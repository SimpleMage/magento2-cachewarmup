<?php

namespace SimpleMage\CacheWarmup\Cron;

use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use GuzzleHttp\Psr7\Request;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use SimpleMage\CacheWarmup\Model\Config;
use Psr\Log\LoggerInterface;
use Magento\Framework\Filesystem;
use Laminas\Uri\UriFactory;

class CacheWarmup
{
    protected const string USER_AGENT
        = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36';

    /**
     * @var LoggerInterface $logger
     */
    protected LoggerInterface $logger;

    /**
     * @var array $curlHandles
     */
    protected array $curlHandles = [];

    /**
     * @var Config $config
     */
    protected Config $config;

    /**
     * @var Filesystem $filesystem
     */
    protected Filesystem $filesystem;

    /**
     * @var Client $httpClient
     */
    protected Client $httpClient;

    /**
     * @param LoggerInterface $logger
     * @param Config $config
     * @param Filesystem $filesystem
     */
    public function __construct(
        LoggerInterface $logger,
        Config $config,
        Filesystem $filesystem
    ) {
        $this->logger = $logger;
        $this->config = $config;
        $this->filesystem = $filesystem;
        $this->httpClient = new Client();
    }

    /**
     * Execute cronjob to warmup Varnish cache URLs from file
     *
     * @return void
     * @throws FileSystemException
     */
    public function execute()
    {
        if (!$this->config->isCronjobEnabled()) {
            return;
        }

        $ips = $this->config->getIpList();
        $directory = $this->filesystem->getDirectoryRead(DirectoryList::VAR_DIR);
        $filePath = $this->config->getUrlsFilePath();

        if (!$directory->isFile($filePath)) {
            $this->logger->error('Warmup URLs file not found');
            return;
        }

        $urls = explode(PHP_EOL, $directory->readFile($filePath));
        $promises = [];

        foreach ($urls as $url) {
            $url = trim($url);
            if (empty($url)) {
                continue;
            }

            $uri = UriFactory::factory($url);
            $hostname = $uri->getHost();

            foreach ($ips as $ip) {
                $ip = trim($ip);

                $request = new Request('HEAD', $url, [
                    'User-Agent' => self::USER_AGENT,
                    'Host' => $hostname,
                ]);

                $options = [
                    'headers' => ['Host' => $hostname],
                    'connect_timeout' => 10,
                    'allow_redirects' => true,
                    'force_ip_resolve' => 'v4', // Wymuszenie IPv4
                ];

                if (!empty($ip)) {
                    $options[ 'curl'] =[
                        CURLOPT_RESOLVE => ["$hostname:80:'$ip'", "$hostname:443:$ip"],
                        CURLOPT_NOBODY => true,
                    ];
                }

                if (!$this->config->isSkipVerifyHostMode()) {
                    $options['verify'] = false;
                }

                $promises[] = $this->httpClient->sendAsync($request, $options)
                    ->then(
                        function ($response) use ($url) {
                            $this->logResult($url, $response);
                        },
                        function ($reason) use ($url) {
                            $this->logger->error('Warmup failed for $url: ' . $reason->getMessage());
                        }
                    );

                if (count($promises) >= 16) {
                    Promise\Utils::settle($promises)->wait();
                    $promises = [];
                }
            }
        }

        if (!empty($promises)) {
            Promise\Utils::settle($promises)->wait();
        }
    }

    /**
     * Log result
     *
     * @param string $url
     * @param mixed $response
     */
    protected function logResult($url, $response)
    {
        $info = [
            'url' => $url,
            'http_code' => $response->getStatusCode()
        ];

        $logMessage = sprintf(
            'Warmup: %s %d',
            $info['url'],
            $info['http_code'],
        );

        if ($this->config->isDebugMode()) {
            $this->logger->debug($logMessage);
        }
    }
}
