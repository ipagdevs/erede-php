<?php

use Rede\eRede;
use Rede\Store;
use Monolog\Logger;
use Psr\Log\LogLevel;
use Rede\Environment;
use Monolog\Handler\StreamHandler;

require_once __DIR__ . '/../../vendor/autoload.php';

$client_id = '';

$client_secret = '';

$logger = new Logger('eRede');
$logger->pushHandler(new StreamHandler('php://stdout', LogLevel::DEBUG));

$eRede = new eRede(
    new Store(
        filiation: $client_id,
        token: $client_secret,
        environment: Environment::sandbox()
    ),
    $logger
);

// echo $eRede->generateOAuthToken()->toString();
echo json_encode($eRede->generateOAuthToken()->getCredentials(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
