<?php

namespace SimpleMage\CacheWarmup\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\DirectoryList;

class Config
{
    protected const string CONFIG_PATH_GENERAL = 'simplemage_cachewarmup/general/';
    protected const string CONFIG_PATH_CRONJOB = 'simplemage_cachewarmup/cronjob/';

    /**
     * @var ScopeConfigInterface $scopeConfig
     */
    protected ScopeConfigInterface $scopeConfig;

    /**
     * @var DirectoryList $directoryList
     */
    protected DirectoryList $directoryList;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param DirectoryList $directoryList
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        DirectoryList $directoryList
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->directoryList = $directoryList;
    }

    /**
     * Get is cronjob enabled config
     *
     * @return bool
     */
    public function isCronjobEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::CONFIG_PATH_CRONJOB . 'enabled');
    }

    /**
     * Get is debug mode enabled
     *
     * @return bool
     */
    public function isDebugMode()
    {
        return $this->scopeConfig->isSetFlag(self::CONFIG_PATH_CRONJOB . 'debug');
    }

    /**
     * Get Varnish servers IP list
     *
     * @return string[]
     */
    public function getIpList()
    {
        $ips = $this->scopeConfig->getValue(self::CONFIG_PATH_GENERAL . 'ips');
        if (empty($ips)) {
            return [''];
        }
        return explode(',', $ips);
    }

    /**
     * Get URLs file path
     *
     * @return string
     * @throws FileSystemException
     */
    public function getUrlsFilePath()
    {
        if ($this->scopeConfig->getValue(self::CONFIG_PATH_GENERAL . 'source_mode') === 'file_upload') {
            return 'import/warmup/' . $this->scopeConfig->getValue(self::CONFIG_PATH_GENERAL . 'urls_file');
        }

        return 'import/warmup_urls.txt';
    }

    /**
     * Get is skip verify SSL host mode
     *
     * @return bool
     */
    public function isSkipVerifyHostMode()
    {
        return $this->scopeConfig->isSetFlag(self::CONFIG_PATH_GENERAL . 'skip_verify_host');
    }
}
