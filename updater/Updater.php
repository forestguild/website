<?php

declare(strict_types=1);

namespace Rakshazi\WoW;

/**
 * Guild info updater.
 */
class Updater extends Updater\Base
{
    /**
     * Battle.net handler.
     *
     * @var Updater\BattleNet
     */
    public $bnet;

    /**
     * RaidProgress handler.
     *
     * @var Updater\RaidProgress
     */
    public $raid;

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
    public $wowprogress;

    /**
     * CloudFlare handler.
     *
     * @var Updater\CloudFlare
     */
    public $cloudflare;
    /**
     * Updater configuration.
     *
     * @var array
     */
    protected $config = [];

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
            'api' => [
                'battle.net' => $battlenet_apikey,
                'cloudflare' => $cloudflare,
            ],
            'lang' => $lang,
        ];
        $this->bnet = new Updater\BattleNet($this->config);
        $this->raid = new Updater\RaidProgress($this->config, $this->bnet);
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
        $news = \array_merge($this->bnet->getNews(), $this->wowprogress->getNews());

        return $this->sort($news);
    }

    /**
     * Update game progress.
     */
    public function updateProgress(): void
    {
        // Update guild progress
        $this->log('Updater', '===================== Guild Progress =====================');
        $this->wowprogress->updateGuild();
        $this->raiderio->updateGuild();
        $this->log('Updater', '=================== End Guild Progress =====================');

        // Update characters progress
        $this->log('Updater', '===================== Characters Progress =====================');
        $chars = $this->bnet->getCharacters();
        $this->wowprogress->updateCharacters($chars);
        $this->raiderio->updateCharacters($chars);
        $this->log('Updater', '=================== End Characters Progress =====================');
    }

    /**
     * Purge website cache on cloudflare.
     */
    public function purgeCache(): void
    {
        $this->cloudflare->purgeCache();
    }

    /**
     * Get updates and save them to data dir.
     *
     * @param string $dir Path to dir with csv
     */
    public function toData(string $dir): void
    {
        //News
        $this->log(null, '===================== News =====================');
        $data = $this->getNews();
        \file_put_contents($dir.'/news.json', \json_encode($data));
        $this->log(null, '=================== End News =====================');

        //Raid progress
        $this->log(null, '===================== Raid Progress =====================');
        $data = $this->raid->get();
        \file_put_contents($dir.'/raid.json', \json_encode($data, JSON_NUMERIC_CHECK));
        $this->log(null, '=================== End Raid Progress =====================');
    }
}
