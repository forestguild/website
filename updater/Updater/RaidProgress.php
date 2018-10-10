<?php

declare(strict_types=1);

namespace Rakshazi\WoW\Updater;

class RaidProgress extends Base
{
    /**
     * Configuration.
     *
     * @var array
     */
    protected $config;

    /**
     * BattleNet handler.
     *
     * @var BattleNet
     */
    protected $bnet;

    /**
     * Filters.
     *
     * @var array
     */
    protected $filters = [
        'raids' => ['Ульдир'],
    ];

    /**
     * List of raid bosses.
     *
     * @var array
     */
    protected $bosses = [];

    /**
     * Init.
     *
     * @param array     $config
     * @param BattleNet $bnet
     */
    public function __construct(array $config, BattleNet $bnet)
    {
        $this->config = $config;
        $this->bnet = $bnet;
        $this->filters['time'] = $this->getTimeFilter();
    }

    /**
     * Get current week raid progress.
     *
     * @return array
     */
    public function get(): array
    {
        $kills = $this->getKillTimestamps();
        $raiders = $this->getRaiders($kills);
        $data = $this->preprocess($raiders);
        $this->log('RaidProgress.get', 'success');

        return $data;
    }

    /**
     * Prepare wow week datetime filter (from wednesday to wednesday).
     *
     * @return int timestamp
     */
    protected function getTimeFilter(): int
    {
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
        $filter = (int) \strtotime(\date('y-m-d 00:00:00', \strtotime('Wednesday '.$week.' week'))); //workaround for 12am
        $this->log('RaidProgress.getTimeFilter', 'Wednesday '.$week.' week == '.$filter);

        return $filter;
    }

    /**
     * Get array of bosses and players' kill timestamps.
     *
     * @return array
     */
    protected function getKillTimestamps(): array
    {
        $progress = [];
        $chars = $this->bnet->getLeveledCharacters();
        $data = $this->bnet->getCharactersData($chars, 'progression');
        //Get list of bosses and kill timestamps with player names
        foreach ($data as $char) {
            foreach ($char['progression']['raids'] as $raid) {
                if (\in_array($raid['name'], $this->filters['raids'], true)) {
                    foreach ($raid['bosses'] as $boss) {
                        if (!isset($this->bosses[$raid['name']][$boss['name']])) {
                            $this->bosses[$raid['name']][$boss['name']] = 0;
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
        $this->log('RaidProgress.getKillTimestamps', 'success');

        return $progress;
    }

    /**
     * Get array of raiders with kills.
     *
     * @param array $killTimestamps array from self::getKillTimestamps()
     *
     * @return array
     */
    protected function getRaiders(array $killTimestamps): array
    {
        $raiders = [];

        //Calculate guild raiders
        foreach ($killTimestamps as $raidName => $rawBosses) {
            foreach ($rawBosses as $bossName => $difficulty) {
                foreach ($difficulty as $diffName => $times) {
                    foreach ($times as $timestamp => $players) {
                        $timestamp = \substr((string) $timestamp, 0, -3); //workaround for "000" in bnet's timestamps
                        if (\count($players) >= 3 && $timestamp >= $this->filters['time']) { //We check only guild groups with 3+ members for this wow Week (from wed to wed).
                            foreach ($players as $player) {
                                if (!isset($raiders[$raidName][$bossName][$player])) {
                                    $raiders[$player]['kills'][$bossName] = 0;
                                }
                                ++$raiders[$player]['kills'][$bossName];
                                $this->bosses[$raidName][$bossName] = 1;
                            }
                        }
                    }
                }
            }
        }
        $this->log('RaidProgress.getRaiders', 'success');

        return $raiders;
    }

    /**
     * Preprocess data to jekyll data.
     *
     * @param array $raiders Array from self::getRaiders()
     *
     * @return array
     */
    protected function preprocess(array $raiders): array
    {
        $processed = [];
        foreach ($raiders as $name => $data) {
            $data['name'] = $name;
            foreach ($data['kills'] as $boss => $count) {
                $data['kills'][] = ['name' => $boss, 'count' => $count];
                unset($data['kills'][$boss]);
            }
            $processed['raiders'][] = $data;
        }
        foreach ($this->bosses as $raidName => $bossList) {
            foreach ($bossList as $name => $killed) {
                $processed['bosses'][] = ['name' => $name, 'killed' => (bool) $killed, 'raid' => $raidName];
            }
        }
        // Sort by kills
        \usort($processed['raiders'] ?? [], function (array $a, array $b) {
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
        $this->log('RaidProgress.preprocess', 'success');

        return $processed;
    }
}
