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
        'raids' => ['Ульдир'], //raid to check
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
        foreach ($characters as $character) {
            $raw = \json_decode($this->fetch($this->getUrl($fields, $character)), true);
            if ($raw['name'] ?? false) {
                $this->log('BattleNet.getCharactersData('.$character.')', 'success');
                $data[] = $raw;
            } else {
                $this->log('BattleNet.getCharactersData('.$character.')', 'fail');
            }
        }

        return $data;
    }

    /**
     * Get current week raid progress.
     * NOTE: Battle.net progression API is very complicated,
     * that's why we need multi-level foreach and other shit.
     *
     * @todo refactor that shit
     *
     * @return array
     */
    public function getRaidProgress(): array
    {
        $raiders = [];
        $bosses = [];
        $progress = [];
        $chars = $this->getLeveledCharacters();
        $data = $this->getCharactersData($chars, 'progression');
        //Get list of bosses and kill timestamps with player names
        foreach ($data as $char) {
            foreach ($char['progression']['raids'] as $raid) {
                if (\in_array($raid['name'], $this->filters['raids'], true)) {
                    foreach ($raid['bosses'] as $boss) {
                        if (!isset($bosses[$raid['name']][$boss['name']])) {
                            $bosses[$raid['name']][$boss['name']] = 0;
                        }
                        foreach (['normal', 'heroic', 'mythic'] as $difficulty) {
                            if ($boss[$difficulty.'Kills'] ?? null) {
                                $progress[$raid['name']][$boss['name']][$difficulty][$boss[$difficulty.'Timestamp']][] = $char['name'];
                            }
                        }
                    }
                }
            }
        }

        //Prepare date filter
        switch (\date('w')) {
        case 0: //sunday
        case 1: //monday
        case 2: //tueseday
            $week = 'previous';
            break;
        default: //wednesday+
            $week = 'this';
            break;
        }
        $timeFilter = \strtotime(\date('y-m-d 00:00:00', \strtotime('Wednesday '.$week.' week'))); //workaround for 12am
        //Calculate guild raiders
        foreach ($progress as $raidName => $rawBosses) {
            foreach ($rawBosses as $bossName => $difficulty) {
                foreach ($difficulty as $diffName => $times) {
                    foreach ($times as $timestamp => $players) {
                        $timestamp = \substr((string) $timestamp, 0, -3); //workaround for "000" in bnet's timestamps
                        if (\count($players) >= 3 && $timestamp >= $timeFilter) { //We check only guild groups with 3+ members for this wow Week (from wed to wed).
                            foreach ($players as $player) {
                                if (!isset($raiders[$raidName][$bossName][$player])) {
                                    $raiders[$player]['kills'][$bossName] = 0;
                                }
                                ++$raiders[$player]['kills'][$bossName];
                                $bosses[$raidName][$bossName] = 1;
                            }
                        }
                    }
                }
            }
        }
        //Convert to more usable by Jekyll format
        $processed = [];
        foreach ($raiders as $name => $data) {
            $data['name'] = $name;
            foreach ($data['kills'] as $boss => $count) {
                $data['kills'][] = ['name' => $boss, 'count' => $count];
                unset($data['kills'][$boss]);
            }
            $processed['raiders'][] = $data;
        }
        foreach ($bosses as $raidName => $bossList) {
            foreach ($bossList as $name => $killed) {
                $processed['bosses'][] = ['name' => $name, 'killed' => (bool) $killed, 'raid' => $raidName];
            }
        }
        // Sort by kills
        \usort($processed['raiders'], function (array $a, array $b) {
            $aKills = 0;
            $bKills = 0;
            foreach (['a', 'b'] as $i) { //count boss kills for $a and $b
                if (!${$i}['kills']) {
                    continue;
                }
                foreach (${$i}['kills'] as $kills) {
                    ${$i.'Kills'} += $kills['count'];
                }
            }
            if ($aKills === $bKills) {
                return 0;
            }

            return ($aKills < $bKills) ? 1 : -1;
        });

        return $processed;
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
