<?php

use Elasticsearch\ClientBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

require_once __DIR__ . '/vendor/autoload.php';

$hosts = [
    '127.0.0.1:9200',         // IP + Port
    '127.0.0.1',              // Just IP
    'localhost:9200',        // Domain + Port
    'localhost',            // Just Domain
//    'https://localhost',        // SSL to localhost
//    'https://127.0.0.1:9200'  // SSL to IP + Port
];

$logger = new Logger('name');
$logger->pushHandler(new StreamHandler(__DIR__ .'/your.log', Logger::WARNING));


$client = Elasticsearch\ClientBuilder::create()
    ->setHosts($hosts)
    ->setRetries(3)
    ->setLogger($logger)
    ->build();

$params = [
    'index' => 'test',
    'type' => 'test',
    'id' => 1,
    'parent' => 'abc',              // white-listed Elasticsearch parameter
    'client' => [
        'custom' => [
            'customToken' => 'abc', // user-defined, not white listed, not checked
            'otherToken' => 123
        ]
    ]
];
$exists = $client->exists($params);
var_dump($exists);
