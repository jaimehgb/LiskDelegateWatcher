<?php

require_once 'config.php';
require_once 'Checker.php';
require_once 'Logger.php';
require_once 'vendor/autoload.php';

use Twilio\Rest\Client;

set_time_limit(0);

$client = new Client(TWILIO_SID, TWILIO_AUTH_TOKEN);

$settings = ['default_sms_interval' => DEFAULT_SMS_INTERVAL, 'explorers' => $explorers, 'mainExplorer' => DEFAULT_EXPLORER ];

if(OVERWRITE_SAVED_SETTINGS)
{
    $file = fopen('data/status.dat', 'w');
    fwrite($file, '');
    fclose($file);
}

$checker = new Checker($settings);
$logger = new Logger(LOGFOLDER);
$checker->setLogger($logger);
$checker->setTwilioClient($client, TWILIO_FROM_NUMBER);

foreach($delegates as $name => $delegate)
{
    $delegate['name'] = $name;
    $checker->addDelegate($delegate);
}


while(true)
{
    $checker->check();
    sleep(RECHECK_INTERVAL);
}
