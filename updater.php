<?php

declare(strict_types=1);
$updater = new \Rakshazi\WoW\Updater(
    'eu',
    ['ru' => 'Галакронд', 'en' => 'Galakrond', 'id' => 607],
    'Ясный Лес',
    \getenv('BATTLENET_API_KEY'),
    [
        'zone_id' => \getenv('CF_ZONE_ID'),
        'email' => \getenv('CF_API_EMAIL'),
        'key' => \getenv('CF_API_KEY'),
    ],
    'ru_RU');

if (($argv[1] ?? false) === 'news') {
    $updater->updateProgress();
    $updater->toCsv(\getcwd().'/_data/news.csv');
} else {
    $updater->purgeCache();
}
