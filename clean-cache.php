<?php

declare(strict_types=1);
/**
 * That script will purge cloudflare cache for website
 * It's not in .travis.yml because of issues with secret env variables.
 *
 * @see https://api.cloudflare.com
 */
$ch = \curl_init();
\curl_setopt($ch, CURLOPT_URL, 'https://api.cloudflare.com/client/v4/zones/'.\getenv('CF_ZONE_ID').'/purge_cache');
\curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'X-Auth-Email: '.\getenv('CF_API_EMAIL'),
    'X-Auth-Key: '.\getenv('CF_API_KEY'),
]);
\curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST'); //workaround for redirect bug (send post - redirect - send get)
\curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
\curl_setopt($ch, CURLOPT_POSTFIELDS, \json_encode(['purge_everything' => true]));
\curl_setopt($ch, CURLOPT_POSTREDIR, 3); //workarond for redirect bug
\curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$data = \json_decode(\curl_exec($ch), true);
\curl_close($ch);
echo 'PURGE CACHE - '.(($data['success'] ?? false) ? 'success' : 'fail')."\n";
