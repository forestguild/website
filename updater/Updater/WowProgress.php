<?php

declare(strict_types=1);

namespace Rakshazi\WoW\Updater;

class WowProgress extends Base
{
    /**
     * Update guild progress.
     */
    public function updateGuild(): void
    {
        try {
            $result = \json_decode($this->send($this->getUrl(), ['submit' => 1, 'char_ids' => \json_encode($this->getCharIds())]), true);
            $this->log('WowProgress.updateGuild', ($result['success'] ?? false) === true ? 'success' : 'fail');
        } catch (\Throwable $t) {
            $this->log('WowProgress.updateGuild', 'fail. '.$t->getMessage());
        }
    }

    /**
     * Update characters' progress.
     *
     * @param array $characters Characters' names to update
     */
    public function updateCharacters(array $characters = []): void
    {
        $urls = [];
        foreach ($characters as $name) {
            $urls[] = $this->getUrl($name);
        }
        foreach ($this->sendMulti($urls, ['update' => 1]) as $result) {
            try {
                $result = \json_decode($result, true);
                $this->log('WowProgress.updateCharacters', ($result['success'] ?? false) === true ? 'success' : 'fail');
            } catch (\Throwable $t) {
                $this->log('WowProgress.updateCharacters', 'fail. '.$t->getMessage());
            }
        }
    }

    /**
     * Get news.
     *
     * @param int $limit Limit of news items
     *
     * @return array
     */
    public function getNews(int $limit = 5): array
    {
        $data = [];
        try {
            $raw = $this->fetch($this->getUrl('_news'));
            $rss = \simplexml_load_string($raw);
            foreach ($rss->channel->item as $item) {
                $type = 'member-'.((false !== \strpos((string) $item->title, 'joined')) ? 'join' : 'leave');
                $data[] = [
                    'timestamp' => \strtotime((string) $item->pubDate),
                    'type' => $type,
                    'title' => \strtr((string) $item->title, ['joined' => 'вступил(-а) в', 'left' => 'покинул(-а)']),
                    'description' => '',
                ];
            }
            $this->log('WowProgress.getNews', 'success');
            $data = \array_slice($this->sort($data), 0, $limit);
        } catch (\Throwable $t) {
            $this->log('WowProgress.getNews', 'fail. '.$t->getMessage());
        }

        return $data;
    }

    /**
     * Get wowprogress url.
     *
     * @param string $character Character name to update. If empty, url for guild update will be returned. If _news, guild news url will be returned
     *
     * @return array
     */
    protected function getUrl(string $character = null): string
    {
        $url = 'https://wowprogress.com/';
        if ($character && '_news' !== $character) {
            $url .= 'character/'.$this->config['region'].'/'.$this->config['realm']['ru'].'/'; // https://wowprogress.com/character/eu/Галакронд/
            $url .= $character; // https://wowprogress.com/character/eu/Галакронд/Этке
        } elseif ($character && '_news' === $character) {
            $url .= 'rss/guild/'.$this->config['region'].'/'; // https://wowprogress.com/rss/guild/eu/
            $url .= \strtolower($this->config['realm']['ru']).'/'; // https://wowprogress.com/rss/guild/eu/галакронд
            $url .= \str_replace(' ', '+', $this->config['guild']); // https://wowprogress.com/rss/guild/eu/галакронд/Ясный+Лес
        } else {
            $url .= 'update_progress/guild/'.$this->config['region'].'/'; // https://wowprogress.com/update_progress/guild/eu/
            $url .= \strtolower($this->config['realm']['ru']).'/'; // https://wowprogress.com/update_progress/guild/eu/галакронд
            $url .= \str_replace(' ', '+', $this->config['guild']); // https://wowprogress.com/update_progress/guild/eu/галакронд/Ясный+Лес
        }

        return $url;
    }

    /**
     * Get character ids from WoWProgress.
     *
     * @return array
     */
    protected function getCharIds(): array
    {
        $charIds = [];
        $html = $this->fetch($this->getUrl());
        \libxml_use_internal_errors(true);
        $dom = new \DomDocument();
        $dom->loadHTML($html);
        $finder = new \DomXPath($dom);
        $classname = 'char_chbx';
        $nodes = $finder->query("//*[contains(@class, '$classname')]");
        foreach ($nodes as $node) {
            $charIds[] = \substr($node->getAttribute('id'), 6); //because id is "check_1232145", so we must remove that prefix
        }

        return $charIds;
    }
}
