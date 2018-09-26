<?php

declare(strict_types=1);

namespace Rakshazi\WoW\Updater;

class RaiderIO extends Base
{
    /**
     * Update guild progress.
     */
    public function updateGuild(): void
    {
        try {
            $result = \json_decode($this->send('https://raider.io/api/crawler/guilds', $this->getPayload()), true);
            $this->log('RaiderIO.updateGuild', ($result['success'] ?? false) === true ? 'success' : 'fail.');
        } catch (\Throwable $t) {
            $this->log('RaiderIO.updateGuild', 'fail. '.$t->getMessage());
        }
    }

    /**
     * Update characters progress.
     */
    public function updateCharacters(array $characters = []): void
    {
        foreach ($characters as $name) {
            try {
                $result = \json_decode($this->send('https://raider.io/api/crawler/characters', $this->getPayload($name)), true);
                $this->log('RaiderIO.updateCharacters('.$name.')', ($result['success'] ?? false) === true ? 'success' : 'fail');
            } catch (\Throwable $t) {
                $this->log('RaiderIO.updateCharacters('.$name.')', 'fail. '.$t->getMessage());
            }
        }
    }

    /**
     * Get payload for raider.io api.
     *
     * @param string $character Character name to update. If empty, payload for guild update will be returned
     *
     * @return array
     */
    protected function getPayload(string $character = null): array
    {
        $payload = [
            'realmId' => $this->config['realm']['id'],
            'realm' => $this->config['realm']['en'],
            'region' => $this->config['region'],
        ];
        if ($character) {
            $payload['character'] = $character;
        } else {
            $payload['guild'] = $this->config['guild'];
            $payload['numMembers'] = 0; //amout of members to update. 0 = all
        }

        return $payload;
    }
}
