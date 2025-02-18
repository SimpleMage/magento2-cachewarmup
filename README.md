# SimpleMage CacheWarmup module for Magento 2 (Adobe Commerce)

## Overview

The **SimpleMage CacheWarmup** module is designed to enhance the performance of your Magento store by automating the process of warming up the cache for your website's pages. This module is compatible with both **Magento Full Page Cache** and **Varnish**, ensuring that your store's pages are preloaded into the cache, reducing load times for your visitors.

The module allows you to upload a file containing most viewed 1000-2000 URLs (e.g. exorted from Google Analytics) and automatically performs a cache warmup for those pages. It is particularly useful for setups with multiple Varnish servers, as it can handle complex infrastructures seamlessly.

## Key Features

- **Compatibility**: Works with both Magento Full Page Cache and Varnish.
- **URL from file**: Supports cache warmup based on a list of URLs imported from a file (e.g., from Google Analytics) - can warmup custom pages from every module provider e.g. blog module or category pages with filters applied.
- **Multi-Server Support**: Handles infrastructures with multiple Varnish servers or single Varnish instance.
- **Cronjob Integration**: Automates cache warmup at scheduled intervals via cronjob.
- **SSL Verification**: Includes an option to disable SSL verification for local Varnish testing.

## Installation

1. Use composer to install

or download the module files and place them in the `app/code/SimpleMage/CacheWarmup` directory within your Magento installation

2. Run the following commands to enable the module:
   ```bash
   php bin/magento setup:upgrade
   php bin/magento setup:di:compile
   php bin/magento cache:flush
   ```
3. Configure the module via the Magento admin panel under `Stores > Configuration > SimpleMage > Cache Warmup`
 - `General Settings`
   - `Varnish Server IPs` - if you have multiple Varnish servers put here public IPs of this servers, if you have Varnish installed on the same server (or not have Varnish installed) leave it blank
   - `URLs File` - upload file with URLs you want to warump
   - `Skip verify host SSL certificate` - use only on localhost for testing purposes
 - `Cronjob Settings`
   - `Enable warmup cronjob` - enable or disable warmup cronjob
   - `Cron schedule` - cron expression configuration
   - `Enable debug mode` - enable debugging of every query for every URL from the file
        
## Troubleshooting

1. Cache not warming up properly

If you notice that certain pages are not being cached, follow these steps:

- Enable Debug Mode: Turn on debug mode in the module settings.
- Check Logs: Review the logs to see the status of the requests made by the module.
  - If the status is not 200, there may be a connection issue between your Magento application server and the Varnish server(s).
  - Ensure that your Magento server can send requests to the public IP address of your Varnish server(s).

2. Local Varnish Testing

If you are testing Varnish locally and encountering issues:
- Problem with SSL verification: Use the option in the module settings to disable SSL verification `Stores > Configuration > SimpleMage > Cache Warmup > General Settings > Skip verify host SSL certificate`. This is useful for local environments where SSL certificates may not be properly configured.

## Support
For any issues or questions regarding the module, please open an issue on our GitHub repository.
