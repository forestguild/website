<?php

declare(strict_types=1);

namespace Rakshazi\WoW\Updater;

class Base
{
    /**
     * Configuration.
     *
     * @var array
     */
    protected $config;

    /**
     * Init.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
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
     * @param string $task    Updater and task name
     * @param string $message Message
     */
    protected function log(string $task, string $message): void
    {
        echo '['.\date('Y-m-d H:i:s')."] $task - $message\n";
    }

    /**
     * Sort results.
     *
     * @param array $data Data to sort
     *
     * @return array
     */
    protected function sort(array $data): array
    {
        \uasort($data, function (array $a, array $b) {
            if ($a['timestamp'] === $b['timestamp']) {
                return 0;
            }

            return ($a['timestamp'] < $b['timestamp']) ? 1 : -1;
        });

        return $data;
    }
}
