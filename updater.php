<?php

declare(strict_types=1);

/**
 * Guild info updater.
 */
class updater
{
    /**
     * Init.
     *
     * @param string $region   WoW region, default: eu
     * @param string $realm    WoW Realm (server) name (english, lowercase
     * @param int    $realm_id Raider.IO Realm ID
     * @param string $guild    Guild name
     */
    public function __construct(string $region, string $realm, int $realm_id, string $guild)
    {
        $this->wowprogress = 'https://wowprogress.com/update_progress/guild/'.$region.'/'.$realm.'/'.\str_replace(' ', '+', $guild);
        $this->raiderio = [
            'realmId' => $realm_id,
            'realm' => $realm,
            'region' => $region,
            'guild' => $guild,
            'numMembers' => 0, //amout of members to update. 0 = all
        ];
    }

    /**
     * Update guild progress on WowProgress.com.
     */
    public function runWowProgress(): void
    {
        try {
            $charIds = [];
            $html = $this->fetch($this->wowprogress);
            \libxml_use_internal_errors(true);
            $dom = new \DomDocument();
            $dom->loadHTML($html);
            $finder = new \DomXPath($dom);
            $classname = 'char_chbx';
            $nodes = $finder->query("//*[contains(@class, '$classname')]");
            foreach ($nodes as $node) {
                $charIds[] = \substr($node->getAttribute('id'), 6); //because id is "check_1232145", so we must remove that prefix
            }
            $result = \json_decode($this->send($this->wowprogress, ['submit' => 1, 'char_ids' => \json_encode($charIds)]), true);
            $this->log('WowProgress', ($result['success'] ?? false) === true ? 'success' : 'fail');
        } catch (\Throwable $t) {
            $this->log('WowProgress', 'fail. '.$t->getMessage());
        }
    }

    /**
     * Update guild progress on Raider.io.
     */
    public function runRaiderIO(): void
    {
        try {
            $result = \json_decode($this->send('https://raider.io/api/crawler/guilds', $this->raiderio), true);
            $this->log('Raider.io', ($result['success'] ?? false) === true ? 'success' : 'fail.');
        } catch (\Throwable $t) {
            $this->log('Raider.io', 'fail. '.$t->getMessage());
        }
    }

    /**
     * Send POST request to URL with DATA.
     *
     * @param string $url
     * @param mixed  $data
     * @param array  $headers HTTP headers
     *
     * @return string
     */
    protected function send(string $url, $data, array $headers = null): string
    {
        $ch = \curl_init();
        if (\is_array($data)) {
            $data = \http_build_query($data);
        }
        \curl_setopt($ch, CURLOPT_URL, $url);
        \curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST'); //workaround for redirect bug (send post - redirect - send get)
        \curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        \curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        if ($headers) {
            \curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        \curl_setopt($ch, CURLOPT_POSTREDIR, 3); //workarond for redirect bug
        \curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data = \curl_exec($ch);
        \curl_close($ch);

        return $data;
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
     * Write log message.
     *
     * @param null|string $task    Updater and task name
     * @param string      $message Message
     */
    protected function log(string $task = null, string $message): void
    {
        if ($task) {
            echo '['.\date('Y-m-d H:i:s')."] $task - $message\n";
        } else {
            echo "\n$message\n\n";
        }
    }
}

$updater = new Updater('eu', 'galakrond', 607, 'Ясный Лес');
$updater->runWowProgress();
$updater->runRaiderIO();
