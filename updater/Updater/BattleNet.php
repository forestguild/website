<?php

declare(strict_types=1);

namespace Rakshazi\WoW\Updater;

class BattleNet extends Base
{
    /**
     * Character filters.
     *
     * @var array
     */
    protected $filters = [
        'level' => 120, //min level
        'ranks' => [0, 1, 2], //required guild rank
    ];

    /**
     * Get names of privileged characters
     * to update their progress.
     *
     * @return array
     */
    public function getCharacters(): array
    {
        $chars = [];
        $raw = \json_decode($this->fetch($this->getUrl('members')), true);
        if (!($raw['members'] ?? false)) {
            $this->log('BattleNet.getCharacters', 'fail. No members found (Check battle.net api key)');

            return [];
        }
        foreach ($raw['members'] as $member) {
            // filter mermbers by level and ranks
            if (
                \in_array($member['rank'], $this->filters['ranks'], true)
                && $member['character']['level'] >= $this->filters['level']
            ) {
                $chars[] = $member['character']['name'];
            }
        }
        \sort($chars);
        $this->log('BattleNet.getCharacters', 'success');

        return $chars;
    }

    /**
     * Get all max level characters' names.
     *
     * @return array
     */
    public function getLeveledCharacters(): array
    {
        $chars = [];
        $raw = \json_decode($this->fetch($this->getUrl('members')), true);
        if (!($raw['members'] ?? false)) {
            $this->log('BattleNet.getLeveledCharacters', 'fail. No members found (Check battle.net api key)');

            return [];
        }
        foreach ($raw['members'] as $member) {
            // filter mermbers by level and ranks
            if ($member['character']['level'] >= $this->filters['level']) {
                $chars[] = $member['character']['name'];
            }
        }
        \sort($chars);
        $this->log('BattleNet.getLeveledCharacters', 'success');

        return $chars;
    }

    /**
     * Get characters' data.
     *
     * @param array  $characters Character names to get
     * @param string $fields     Battle.net API fields
     *
     * @return array
     */
    public function getCharactersData(array $characters, string $fields): array
    {
        $data = [];
        $urls = [];
        foreach ($characters as $character) {
            $urls[] = $this->getUrl($fields, $character);
        }
        foreach ($this->fetchMulti($urls, 50) as $raw) {
            $raw = \json_decode($raw, true);
            if ($raw['name'] ?? false) {
                $this->log('BattleNet.getCharactersData('.$raw['name'].')', 'success');
                $data[] = $raw;
            } else {
                $this->log('BattleNet.getCharactersData()', 'fail');
            }
        }

        return $data;
    }

    /**
     * Get guild news.
     *
     * @return array
     */
    public function getNews(): array
    {
        $data = [];
        $news = \json_decode($this->fetch($this->getUrl('news')), true);
        if (!($news['news'] ?? false)) {
            $this->log('BattleNet.getNews', 'fail. No news found');

            return [];
        }
        foreach ($news['news'] as $item) {
            if ('guildAchievement' === $item['type']) {
                $data[] = [
                    'timestamp' => \substr((string) $item['timestamp'], 0, -3), //weird battle.net result with "000" ending in any timestamp
                    'type' => 'achievement',
                    'title' => $item['achievement']['title'],
                    'description' => $item['achievement']['description'],
                ];
            }
        }
        $this->log('BattleNet.getNews', 'success');

        return $data;
    }

    /**
     * Get battle.net API url.
     *
     * @param string $fields    API fields to load, eg: news, members
     * @param string $character Character name. If set - char url will be returned
     *
     * @return string https://eu.api.battle.net/wow/guild/Галакронд/Ясный%20Лес?fields=news&locale=ru_RU&apikey=XXXXXXXX
     */
    protected function getUrl(string $fields = '', string $character = null): string
    {
        $url = 'https://'.$this->config['region'].'.api.battle.net/wow/'.($character ? 'character' : 'guild').'/'; //https://eu.api.battle.net/wow/guild/
        $url .= \ucfirst($this->config['realm']['ru']).'/'.($character ? $character : \str_replace(' ', '%20', $this->config['guild'])); //https://eu.api.battle.net/wow/guild/Галакронд/Ясный%20Лес
        $url .= '?fields='.$fields.'&locale='.$this->config['lang'].'&apikey='.$this->config['api']['battle.net'];

        return $url; //https://eu.api.battle.net/wow/guild/Галакронд/Ясный%20Лес?fields=news&locale=ru_RU&apikey=XXXXXXXX
    }
}
