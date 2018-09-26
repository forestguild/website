<?php

declare(strict_types=1);

namespace Rakshazi\WoW\Updater;

class CloudFlare extends Base
{
    /**
     * Purge website cache.
     */
    public function purgeCache(): void
    {
        $url = 'https://api.cloudflare.com/client/v4/zones/'.$this->config['api']['cloudflare']['zone_id'].'/purge_cache';
        $headers = [
            'Content-Type: application/json',
            'X-Auth-Email: '.$this->config['api']['cloudflare']['email'],
            'X-Auth-Key: '.$this->config['api']['cloudflare']['key'],
        ];

        $data = \json_decode($this->send($url, \json_encode(['purge_everything' => true]), $headers), true);
        $this->log('CloudFlare.purgeCache', ($data['success'] ?? false) ? 'success' : 'fail');
    }
}
