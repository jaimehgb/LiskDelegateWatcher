<?php

require_once 'config.php';

class Checker
{
    private $delegates;            // watchlist basically
    private $default_sms_interval; // time between sms on delegates without it set
    private $logger = false;       // a logger object with at least log, warn and error methods 
    private $explorers = [];       // explorers being checked
    private $mainExplorer = false; // main explorer
    private $extracted_data = [];  // an array because may come from more than one explorers
    private $twilio = false;       // twilio client
    private $fromNumber = false;
    
    public function __construct($settings = [])
    {
        if(!$this->loadStatus() && count($settings) < 3)
            throw new Exception('Inssufficient settings to start.');
        
        if(count($settings) > 0)
        {
            // override settings loaded
            $this->default_sms_interval = $settings['default_sms_interval'] ? $settings['default_sms_interval'] : $this->default_sms_interval;
            $this->explorers = $settings['explorers'] ? $settings['explorers'] : $this->explorers;
            $this->mainExplorer = $settings['mainExplorer'] ? $settings['mainExplorer'] : 0;
        }
    }
    
    public function addDelegate($delegate)
    {
        if(!is_array($delegate))
        {
            if($this->logger)
                $this->logger->warn('Failed trying to add delegate. Invalid format.');
            return false;
        }
        
        if($this->delegates[$delegate['name']])
        {
            if($this->logger)
                $this->logger->warn('Failed trying to add delegate. Already in watchlist.');
            return false;
        }
        
        if(!$delegate['name'] || !$delegate['extension'] || !$delegate['number'])
        {
            if($this->logger)
            {
                $this->logger->warn('Trying to add incomplete delegate. Name, number and extension are required.');
            }
            return false;
        }
        
        $delegate['last_checked'] = 0;
        $delegate['active'] = 0;
        $delegate['checked'] = 0;
        $delegate['last_message_sent'] = 0;
        $delegate['sms_interval'] = $delegate['sms_interval'] ? $delegate['sms_interval'] : $this->default_sms_interval;
        $delegate['missed_since_last_sms'] = 0;
        $delegate['missed_blocks'] = 9999999999999; // when updating to the actual number of missed blocks it will be smaller, so no sms will be sent the first check
        
        
        $this->delegates[$delegate['name']] = $delegate;
        if($this->logger)
            $this->logger->log('Delegate added to watchlist: ' . $delegate['name']);
    }
    
    public function updateDelegate($name, $newData)
    {
        if(!$this->delegates[$name])
        {
            if($this->logger)
                $this->logger->warn('Trying to update a missing delegate...');
            return false;
        }
        
        foreach($newData as $key=>$value)
        {
            $this->delegates[$key] = $value;
        }
        return $this;
    }
        
    public function saveStatus()
    {
        $watchlist['delegates'] = $this->delegates;
        $file = fopen('data/status.dat', 'w+');
        if(!$file)
            if($this->logger)
            {
                $this->logger->error('Unable to save status to file: ' . json_encode($watchlist, 2));
                return false;
            }
            else
                throw new Exception('Unable to save status to file: ' . json_encode($watchlist, 2));
        
        #$watchlist['interval'] = $this->interval;
        $watchlist['default_sms_interval'] = $this->default_sms_interval;
        
        $json = json_encode($watchlist);
        fwrite($file, $json);
        fclose($file);
        return $this;
    }
    
    private function loadStatus()
    {
        $data = file_get_contents('data/status.dat');
        
        if(!$data)
            return false;
        
        $data = json_decode($data, true);
        
        if(!$data)
            return false;
        $this->delegates = $data['delegates'];
        $this->default_sms_interval = $data['default_sms_interval'];
        #$this->interval = $data['interval'];

        foreach($this->delegates as $name=>$data)
            if(!$this->delegates[$name]['missed_blocks']) $this->delegates[$name]['missed_blocks'] = 999999999999999;
        
        return $this;
    }
    
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }
    
    public function setTwilioClient($client, $fromNumber)
    {
        $this->twilio = $client;
        $this->fromNumber = $fromNumber;
    }
    
    public function check()
    {
        $this->extracted_data = [];
        foreach($this->explorers as $explorer)
        {
            $data = json_decode(file_get_contents($explorer . '/api/delegates/getActive'), true)['delegates'];
            
            if(!$data)
            {
                if($this->logger)
                    $this->logger->warn('Invalid data extracted from ' . $explorer);
                continue;
            }
            
            $data2 = [];
            foreach($data as $delegate)
            {
                $data2[$delegate['username']] = $delegate;
            }
            
            $this->extracted_data[] = $data2;
        }
        
        $data2 = $this->extracted_data[$this->mainExplorer];
        foreach($this->delegates as $name=>$delegate)
        {
            if(!$data2[$name])
            {
                // not active, send sms if want to be notified when standby
                if($this->delegates[$name]['notify_standby'] && $this->delegates[$name]['checked'] && $this->delegates[$name]['active'])
                {
                    $number = $this->delegates[$name]['extension'] . $this->delegates[$name]['number'];
                    $body = "Hey, $name. Your delegate stopped appearing at the 'Active' list.";
                    $this->twilioSend($number, $body);
                }
                $this->delegates[$name]['active'] = 0;
            }
            else
            {
                // active, update missed blocks
                if(intval($data2[$name]['missedblocks']) > $delegate['missed_blocks'] && $delegate['notify_missed_blocks'] === true)
                {
                    // missed one or more blocks
                    // send sms
		    $this->delegates[$name]['missed_since_last_sms'] += intval($data2[$name]['missedblocks']) - $delegate['missed_blocks'];
                    $this->delegates[$name]['missed_blocks'] = intval($data2[$name]['missedblocks']);
                    $this->send($name);
                }
                
                if($this->delegates[$name]['notify_active'] && !$this->delegates[$name]['active'] && $this->delegates[$name]['checked'])
                {
                    $number = $this->delegates[$name]['extension'] . $this->delegates[$name]['number'];
                    $body = "Hey, $name. Your delegate appeared at the 'Active' list after being at the 'StandBy' list.";
                    $this->twilioSend($number, $body);
                }
                
                $this->delegates[$name]['missed_blocks'] = $data2[$name]['missedblocks'];
                $this->delegates[$name]['active'] = 1;
            }
            $this->delegates[$name]['checked'] = 1;
        }
        $this->saveStatus();
    }
    
    private function send($delegate)
    {
        $name = $delegate;
        $delegate = $this->delegates[$delegate];
        
        if($delegate['last_message_sent'])
        {
            if(time() - $delegate['last_message_sent'] < $delegate['sms_interval'])
            {
                // do nothing
                $this->logger->log("Skipping SMS. Last sms was sent recently.");
            }
            else
            {
                // send sms
                $missed = $delegate['missed_since_last_sms'];
                $number = $delegate['extension'] . $delegate['number'];
                $body = "Hey, $name. It seems your delegate missed $missed block/s recently. Your node may be down.";
                $this->twilioSend($number, $body);
                
                // reset data
                $this->delegates[$name]['missed_since_last_sms'] = 0;
                $this->delegates[$name]['last_message_sent'] = time();
            }
        }
        else 
        {
            // send sms too
            // might be the 1st sms for this user 
            $missed = $delegate['missed_since_last_sms'];
            $number = $delegate['extension'] . $delegate['number'];
            $body = "Hey, $name. It seems your delegate missed $missed block/s recently. Your node may be down.";
            $this->twilioSend($number, $body);
            
            // reset data
            $this->delegates[$name]['missed_since_last_sms'] = 0;
            $this->delegates[$name]['last_message_sent'] = time();
        }
    }
    
    private function twilioSend($number, $body)
    {
        if(!$this->twilio)
        {
            if($this->logger)
                $this->logger->error('Trying to send SMS without Twilio set up.');
            return false;
        }
        
        $this->twilio->messages->create(
            $number,
            array(
                'from' => $this->fromNumber,
                'body' => $body
            )
        );
        $this->logger->log("Message sent: $number: $body");
    }
}
