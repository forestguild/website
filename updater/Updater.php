<?php

declare(strict_types=1);

namespace Rakshazi\WoW;

/**
 * Guild info updater.
 */
class Updater extends Updater\Base
{
    /**
     * Updater configuration.
     *
     * @var array
     */
    protected $config = [];

    /**
     * Battle.net handler.
     *
     * @var Updater\BattleNet
     */
    protected $bnet;

    /**
     * WoWProgress handler.
     *
     * @var Updater\WowProgress
     */

    /**
     * Raider.io handler.
     *
     * @var Updater\RaiderIO
     */
    protected $wowprogress;

    /**
     * CloudFlare handler.
     *
     * @var Updater\CloudFlare
     */
    protected $cloudflare;

    /**
     * Init.
     *
     * @param string $region           WoW region, default: eu
     * @param array  $realm            WoW realm (server) with en and ru and id, example: ['ru' => 'Галакронд', 'en' => 'Galakrond', id => 607]
     * @param string $guild            Guild name
     * @param string $battlenet_apikey Battle.net API key
     * @param array  $cloudflare       CloudFlare config ['zone_id' => '', 'email' => '', 'key' => '']
     * @param string $lang             Battle.net locale, default: en_GB
     */
    public function __construct(string $region, array $realm, string $guild, string $battlenet_apikey = '', array $cloudflare = [], string $lang = 'en_GB')
    {
        $this->config = [
            'region' => $region,
            'realm' => $realm,
            'guild' => $guild,
            'apikey' => [
                'battle.net' => $battlenet_apikey,
                'cloudflare' => $cloudflare,
            ],
            'lang' => $lang,
        ];
        $this->bnet = new Updater\BattleNet($this->config);
        $this->wowprogress = new Updater\WowProgress($this->config);
        $this->raiderio = new Updater\RaiderIO($this->config);
        $this->cloudflare = new Updater\CloudFlare($this->config);
    }

    /**
     * Get latest news.
     *
     * @return array
     */
    public function getNews(): array
    {
        $news = [];
        \array_push($news, $this->bnet->getNews(), $this->wowprogress->getNews());

        return $this->sort($news);
    }

    /**
     * Update game progress.
     */
    public function updateProgress(): void
    {
        // Update guild progress
        $this->wowprogress->updateGuild();
        $this->raiderio->updateGuild();

        // Update characters progress
        $chars = $this->bnet->getCharacters();
        $this->wowprogress->updateCharacters($chars);
        $this->raiderio->updateCharacters($chars);
    }

    /**
     * Purge website cache on cloudflare.
     */
    public function purgeCache(): void
    {
        $this->cloudflare->purgeCache();
    }

    /**
     * Get news and save them to csv.
     *
     * @param string $file Path to file
     */
    public function toCsv(string $file): void
    {
        $data = $this->getNews();
        $fp = \fopen($file, 'w');
        \fputcsv($fp, ['timestamp', 'type', 'title', 'description']);
        foreach ($data as $item) {
            \fputcsv($fp, $item);
        }
        \fclose($fp);
    }
}
