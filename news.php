<?php

/**
 * WoW Guild news downloader
 * @author Nikita Chernyi
 */
class News
{
    /**
     * News data
     * @var array
     */
    protected $data = [];
    /**
     * WoWProgress RSS url
     * @var string
     */
    protected $wowprogressUrl;

    /**
     * Battle.net API url
     * @var string
     */
    protected $battlenetUrl;
    /**
     * Init
     * @param string $region WoW region, default: eu
     * @param string $realm WoW realm (server)
     * @param string $guild Guild name
     * @param string $apikey Battle.net API key
     * @param string $lang Battle.net locale, default: en_GB
     */
    public function __construct(string $region = 'eu', string $realm, string $guild, string $apikey, string $lang = 'en_GB')
    {
        $this->wowprogressUrl = 'https://www.wowprogress.com/rss/guild/'.$region.'/'.strtolower($realm).'/'.str_replace(' ','+',$guild);
        $this->battlenetUrl = 'https://'.$region.'.api.battle.net/wow/guild/'.ucfirst($realm).'/'.str_replace(' ','%20', $guild).'?fields=news&locale='.$lang.'&apikey='.$apikey;
    }

    public function get(): array
    {
        return $this->getWowprogress()
             ->getBattlenet()
             ->sort()
             ->data;
    }

    /**
     * Fetch data from URL
     * @param string $url URL to GET
     * @return string
     */
    protected function fetch(string $url): string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($ch);
        curl_close($ch);

        return $data;
    }

    /**
     * Load news from WoWProgress
     * @param int $limit Items limit
     * @return News
     */
    protected function getWowprogress(int $limit = 5): News
    {
        $raw = $this->fetch($this->wowprogressUrl);
        $rss = simplexml_load_string($raw);
        foreach($rss->channel->item as $item) {
            $type = 'member-'.((strpos($item->title, 'joined') !== false) ? 'join' : 'leave');
            $this->data[] = [
                'timestamp' => strtotime((string)$item->pubDate),
                'type' => $type,
                'title' => strtr((string)$item->title, ['joined' => 'вступил(-а) в', 'left' => 'покинул(-а)']),
                'description' => '',
            ];
        }
        $this->sort();
        $this->data = array_slice($this->data, 0, $limit);

        return $this;
    }

    /**
     * Load news from Battle.net API
     * @return News
     */
    protected function getBattlenet(): News
    {
        $news = json_decode($this->fetch($this->battlenetUrl), true);
        foreach($news['news'] as $item) {
            if($item['type'] == 'guildAchievement') {
                $this->data[] = [
                    'timestamp' => substr($item['timestamp'], 0, -3), //weird battle.net result with "000" ending in any timestamp
                    'type' => 'achievement',
                    'title' => $item['achievement']['title'],
                    'description' => $item['achievement']['description'],
                ];
            }
        }

        return $this;
    }

    /**
     * Sort results
     * @return News
     */
    protected function sort(): News
    {
        uasort($this->data, function(array $a, array $b){
            if ($a['timestamp'] == $b['timestamp']) {
                return 0;
            }
            return ($a['timestamp'] < $b['timestamp']) ? 1 : -1;
        });

        return $this;
    }
}

/**
 * Run it!
 */
// 1. Download and parse news
$news = new News('eu', 'Галакронд', 'Ясный Лес', getenv('BATTLENET_API_KEY'), 'ru_RU');
// 2. Create CSV file with headers
$fp = fopen('./_data/news.csv', 'w');
fputcsv($fp, ['timestamp', 'type', 'title', 'description']);
// 3. And append results.
foreach($news->get() as $item) {
    fputcsv($fp, $item);
}
fclose($fp);
// 4. Done
