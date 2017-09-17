<?php

define('LOGFOLDER', 'log');     // without the trailing slash

# twilio
define('TWILIO_SID', '');
define('TWILIO_AUTH_TOKEN', '');
define('TWILIO_FROM_NUMBER', '+1000000000');


# misc
define('RECHECK_INTERVAL', 30);                     // time between checks, in seconds
define('DEFAULT_SMS_INTERVAL', 1);               // if an sms is sent, minimum time (in seconds) until the next is sent. 
                                                    // If set to false, the next will be sent the next a block is missed, no matter when

define('OVERWRITE_SAVED_SETTINGS', true);           // if set to true, settings at config.php will override last settings used

# explorers
$explorers = [    
    'https://testnet-explorer.lisk.io', // without the trailing slashes :P
    'https://explorer.lisk.4miners.net',
    'https://explorer.lisk.io',
    'https://explorer.mylisk.com',
    'https://explorer.lisknode.io'
];

define('DEFAULT_EXPLORER', 0);                      // can be 0, 1, 2, 3 ... the position at $explorers

################
$delegates = [];                                    // DO NOT TOUCH :D
################


# watchlist
$delegates['lisk_delegate_name'] = array(                       // delegate name as it appears in the blockchain
    'extension' => '+1',                            // country extension e.g.: +1
    'number' => '000000000',                        // phone number to receive the sms
    'notify_missed_blocks' => true,                 // send an sms if delegate misses a block
    'notify_standby' => true,                       // send sms when delegate appears in the standby list after being in the active list
    'notify_active' => true,                        // send sms when delegate appears in the active list after being in the standby list
    'sms_interval' => false                          // minimum time between sms in seconds. Can be 0. If set to false DEFAULT_SMS_INTERVAL will be used
);

/*
$delegates['lisk_delegate_name1'] = array(                       // delegate name as it appears in the blockchain
    'extension' => '+1',                            // country extension e.g.: +1
    'number' => '000000000',                        // phone number to receive the sms
    'notify_missed_blocks' => true,                 // send an sms if delegate misses a block
    'notify_standby' => true,                       // send sms when delegate appears in the standby list after being in the active list
    'notify_active' => true,                        // send sms when delegate appears in the active list after being in the standby list
    'sms_interval' => false                          // minimum time between sms in seconds. Can be 0. If set to false DEFAULT_SMS_INTERVAL will be used
);

$delegates['lisk_delegate_name2'] = array(                       // delegate name as it appears in the blockchain
    'extension' => '+1',                            // country extension e.g.: +1
    'number' => '000000000',                        // phone number to receive the sms
    'notify_missed_blocks' => true,                 // send an sms if delegate misses a block
    'notify_standby' => true,                       // send sms when delegate appears in the standby list after being in the active list
    'notify_active' => true,                        // send sms when delegate appears in the active list after being in the standby list
    'sms_interval' => false                          // minimum time between sms in seconds. Can be 0. If set to false DEFAULT_SMS_INTERVAL will be used
);

$delegates['lisk_delegate_name3'] = array(                       // delegate name as it appears in the blockchain
    'extension' => '+1',                            // country extension e.g.: +1
    'number' => '000000000',                        // phone number to receive the sms
    'notify_missed_blocks' => true,                 // send an sms if delegate misses a block
    'notify_standby' => true,                       // send sms when delegate appears in the standby list after being in the active list
    'notify_active' => true,                        // send sms when delegate appears in the active list after being in the standby list
    'sms_interval' => false                          // minimum time between sms in seconds. Can be 0. If set to false DEFAULT_SMS_INTERVAL will be used
);
*/
