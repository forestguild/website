<?php

declare(strict_types=1);
/*
 * PSR-4 autoloader
 * @param string $class The fully-qualified class name.
 * @return void
 */
\spl_autoload_register(function ($class): void {
    $prefix = 'Rakshazi\\WoW\\';
    $base_dir = __DIR__.'/updater/';
    $len = \strlen($prefix);
    if (0 !== \strncmp($prefix, $class, $len)) {
        return;
    }
    $relative_class = \substr($class, $len);
    $file = $base_dir.\str_replace('\\', '/', $relative_class).'.php';
    if (\file_exists($file)) {
        require $file;
    }
});

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
    $updater->toData(\getcwd().'/_data');
} elseif (($argv[1] ?? false) === 'cache') {
    $updater->purgeCache();
} elseif (($argv[1] ?? false) === 'test') {
    $updater->raid->get();
}
