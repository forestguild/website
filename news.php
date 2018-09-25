<?php

declare(strict_types=1);
/**
 * WoW Guild news downloader.
 *
 * @author Nikita Chernyi
 */
class news
{
    /**
     * News data.
     *
     * @var array
     */
    protected $data = [];

    /**
     * WoW Region, eg: eu.
     *
     * @var string
     */
    protected $region;

    /**
     * WoW Realm, eg ['ru' => 'Галакронд', 'en' => 'Galakrond', 'id' => 607].
     *
     * @var array
     */
    protected $realm;

    /**
     * WoWProgress guild uri.
     *
     * @var string
     */
    protected $wowprogressUrl;

    /**
     * Battle.net API url.
     *
     * @var string
     */
    protected $battlenetUrl;

    /**
     * Battle.net API guild profile url.
     *
     * @var string
     */
    protected $battlenetGuildUrl;

    /**
     * Raider.IO payload to update guild.
     *
     * @var string
     */
    protected $raiderioPayload;

    /**
     * Characters' filters (for progress update).
     *
     * @var array
     */
    protected $charFilters = [
        'level' => 120, //Min level to scan
        'ranks' => [
            0, //GM
            1, //GM-officer
            2, //Discord
        ],
    ];
    /**
     * Characters' names to update.
     *
     * @see self::getChars()
     *
     * @var array
     */
    protected $chars;

    /**
     * Init.
     *
     * @param string $region WoW region, default: eu
     * @param array  $realm  WoW realm (server) with en and ru and id, example: ['ru' => 'Галакронд', 'en' => 'Galakrond', id => 607]
     * @param string $guild  Guild name
     * @param string $apikey Battle.net API key
     * @param string $lang   Battle.net locale, default: en_GB
     */
    public function __construct(string $region, array $realm, string $guild, string $apikey = '', string $lang = 'en_GB')
    {
        if (!$apikey) {
            $this->log('Battle.net', 'warning. No API key');
        }
        $this->region = $region;
        $this->realm = $realm;
        $this->wowprogressUrl = 'guild/'.$region.'/'.\strtolower($realm['ru']).'/'.\str_replace(' ', '+', $guild);
        $this->battlenetUrl = 'https://'.$region.'.api.battle.net/wow/guild/'.\ucfirst($realm['ru']).'/'.\str_replace(' ', '%20', $guild).'?fields=news&locale='.$lang.'&apikey='.$apikey;
        $this->battlenetGuildUrl = 'https://'.$region.'.api.battle.net/wow/guild/'.\ucfirst($realm['ru']).'/'.\str_replace(' ', '%20', $guild).'?fields=members&locale='.$lang.'&apikey='.$apikey;
        $this->raiderioPayload = [
            'realmId' => $realm['id'],
            'realm' => $realm['en'],
            'region' => $region,
            'guild' => $guild,
            'numMembers' => 0, //amount of members to update. 0 = all
        ];
    }

    /**
     * Get news.
     *
     * @return array
     */
    public function get(): array
    {
        return $this->getWowprogress()
                    ->getBattlenet()
                    ->sort()
                    ->data;
    }

    /**
     * Update guild profiles.
     *
     * @return array
     */
    public function update(): self
    {
        return $this->getChars()
                    ->updateWowprogress()
                    ->updateRaiderio()
                    ->updateCharsWowprogress()
                    ->updateCharsRaiderio();
    }

    protected function log(string $task, string $message): void
    {
        echo '['.\date('Y-m-d H:i:s')."] $task - $message\n";
    }

    /**
     * Fetch data from URL.
     *
     * @param string $url URL to GET
     *
     * @return string
     */
    protected function fetch(string $url): string
    {
        $ch = \curl_init();
        \curl_setopt($ch, CURLOPT_URL, $url);
        \curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        \curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data = \curl_exec($ch);
        \curl_close($ch);

        return $data;
    }

    /**
     * Send POST request to URL with DATA.
     *
     * @param string $url
     * @param array  $data
     *
     * @return string
     */
    protected function send(string $url, array $data): string
    {
        $ch = \curl_init();
        \curl_setopt($ch, CURLOPT_URL, $url);
        \curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST'); //workaround for redirect bug (send post - redirect - send get)
        \curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        \curl_setopt($ch, CURLOPT_POSTFIELDS, \http_build_query($data));
        \curl_setopt($ch, CURLOPT_POSTREDIR, 3); //workarond for redirect bug
        \curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data = \curl_exec($ch);
        \curl_close($ch);

        return $data;
    }

    /**
     * Load news from WoWProgress.
     *
     * @param int $limit Items limit
     *
     * @return News
     */
    protected function getWowprogress(int $limit = 5): self
    {
        try {
            $raw = $this->fetch('https://wowprogress.com/rss/'.$this->wowprogressUrl);
            $rss = \simplexml_load_string($raw);
            foreach ($rss->channel->item as $item) {
                $type = 'member-'.((false !== \strpos((string) $item->title, 'joined')) ? 'join' : 'leave');
                $this->data[] = [
                    'timestamp' => \strtotime((string) $item->pubDate),
                    'type' => $type,
                    'title' => \strtr((string) $item->title, ['joined' => 'вступил(-а) в', 'left' => 'покинул(-а)']),
                    'description' => '',
                ];
            }
            $this->log('FETCH WoWProgress', 'success');
            $this->sort();
            $this->data = \array_slice($this->data, 0, $limit);
        } catch (\Throwable $t) {
            $this->log('FETCH WoWProgress', 'fail. '.$t->getMessage());
        }

        return $this;
    }

    /**
     * Update WoWProgress results.
     *
     * @return News
     */
    protected function updateWowprogress(): self
    {
        /*
         * Algo:
         * 1. Get char ids from wowprogress page (find by css class)
         * 2. Send POST request with those char ids
         */
        try {
            $charIds = [];
            $html = $this->fetch('https://wowprogress.com/update_progress/'.$this->wowprogressUrl);
            \libxml_use_internal_errors(true);
            $dom = new \DomDocument();
            $dom->loadHTML($html);
            $finder = new DomXPath($dom);
            $classname = 'char_chbx';
            $nodes = $finder->query("//*[contains(@class, '$classname')]");
            foreach ($nodes as $node) {
                $charIds[] = \substr($node->getAttribute('id'), 6); //because id is "check_1232145", so we must remove that prefix
            }

            $result = \json_decode($this->send('https://wowprogress.com/update_progress/'.$this->wowprogressUrl, ['submit' => 1, 'char_ids' => \json_encode($charIds)]), true);
            $this->log('UPDATE WoWProgress', ($result['success'] ?? false) === true ? 'success' : 'fail');
        } catch (\Throwable $t) {
            $this->log('UPDATE WoWProgress', 'fail. '.$t->getMessage());
        }

        return $this;
    }

    /**
     * Get characters for update.
     *
     * @return self
     */
    protected function getChars(): self
    {
        $raw = \json_decode($this->fetch($this->battlenetGuildUrl), true);
        if (!($raw['members'] ?? false)) {
            $this->log('FETCH CHARS', 'fail. No members found (Check battle.net api key)');

            return $this;
        }
        $this->chars = [];
        foreach ($raw['members'] as $member) {
            // filter mermbers by level and ranks
            if (
                \in_array($member['rank'], $this->charFilters['ranks'], true)
                && $member['character']['level'] >= $this->charFilters['level']
            ) {
                $this->chars[] = $member['character']['name'];
            }
        }
        $this->log('FETCH CHARS', 'success.');

        return $this;
    }

    /**
     * Update characters at WoWProgress.
     *
     * @return self
     */
    protected function updateCharsWowprogress(): self
    {
        foreach ($this->chars as $char) {
            try {
                $result = \json_decode($this->send('https://wowprogress.com/character/'.$this->region.'/'.$this->realm['ru'].'/'.$char, ['update' => 1]), true);
                $this->log('UPDATE CHARS WoWProgress', $char.' - '.(($result['success'] ?? false) === true ? 'success' : 'fail.'));
            } catch (\Throwable $t) {
                $this->log('UPDATE CHARS WoWProgress', $char.' - fail. '.$t->getMessage());
            }
        }

        return $this;
    }

    /**
     * Update Raider.io guild profile.
     *
     * @return News
     */
    protected function updateRaiderio(): self
    {
        try {
            $result = \json_decode($this->send('https://raider.io/api/crawler/guilds', $this->raiderioPayload), true);
            $this->log('UPDATE Raider.io', ($result['success'] ?? false) === true ? 'success' : 'fail.');
        } catch (\Throwable $t) {
            $this->log('UPDATE Raider.io', 'fail. '.$t->getMessage());
        }

        return $this;
    }

    /**
     * Update Raider.io chars.
     *
     * @return self
     */
    protected function updateCharsRaiderio(): self
    {
        $payload = $this->raiderioPayload;
        unset($payload['guild']);
        unset($payload['numMembers']);

        foreach ($this->chars as $char) {
            try {
                $result = \json_decode($this->send('https://raider.io/api/crawler/characters', \array_merge($payload, ['character' => $char])), true);
                $this->log('UPDATE CHARS Raider.io', $char.' - '.(($result['success'] ?? false) === true ? 'success' : 'fail'));
            } catch (\Throwable $t) {
                $this->log('UPDATE Raider.io', $char.' - fail. '.$t->getMessage());
            }
        }

        return $this;
    }

    /**
     * Load news from Battle.net API.
     *
     * @return News
     */
    protected function getBattlenet(): self
    {
        $news = \json_decode($this->fetch($this->battlenetUrl), true);
        if (!($news['news'] ?? false)) {
            $this->log('FETCH Battle.net', 'fail. No news found');

            return $this;
        }
        foreach ($news['news'] as $item) {
            if ('guildAchievement' === $item['type']) {
                $this->data[] = [
                    'timestamp' => \substr((string) $item['timestamp'], 0, -3), //weird battle.net result with "000" ending in any timestamp
                    'type' => 'achievement',
                    'title' => $item['achievement']['title'],
                    'description' => $item['achievement']['description'],
                ];
            }
        }
        $this->log('FETCH Battle.net', 'success');

        return $this;
    }

    /**
     * Sort results.
     *
     * @return News
     */
    protected function sort(): self
    {
        \uasort($this->data, function (array $a, array $b) {
            if ($a['timestamp'] === $b['timestamp']) {
                return 0;
            }

            return ($a['timestamp'] < $b['timestamp']) ? 1 : -1;
        });

        $this->log('SORT', 'success');

        return $this;
    }
}
/**
 * Run it!
 */
// 1. Download and parse news
$news = new news('eu', ['ru' => 'Галакронд', 'en' => 'Galakrond', 'id' => 607], 'Ясный Лес', \getenv('BATTLENET_API_KEY'), 'ru_RU');
$data = $news->update()->get();
// 2. Create CSV file with headers
$fp = \fopen('./_data/news.csv', 'w');
\fputcsv($fp, ['timestamp', 'type', 'title', 'description']);
// 3. And append results.
foreach ($data as $item) {
    \fputcsv($fp, $item);
}
\fclose($fp);
// 4. Done
