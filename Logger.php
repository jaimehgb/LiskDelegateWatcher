<?php

class Logger
{
    private $logs = [];
    private $file;
    
    public function __construct($logFilePath)
    {
        $this->file = fopen($logFilePath, 'a') or die('Unable to write to file: ' . $logFilePath);
    }
    
    public function log($msg)
    {
        $msg = '[LOG] ' . $msg . "\n";
        //$this->logs[] = ['type' => 'log', 'msg' => $msg];
        fwrite($this->file, $msg);
    }
    
    public function warn($msg)
    {
        $msg = '[WARNING] ' . $msg . "\n";
        //$this->logs[] = ['type' => 'warn', 'msg' => $msg];
        fwrite($this->file, $msg);
    }
    
    public function error($msg)
    {
        $msg = '[ERROR] ' . $msg . "\n";
        //$this->logs[] = ['type' => 'error', 'msg' => $msg];
        fwrite($this->file, $msg);
    }
    
    public function getAll()
    {
        foreach($this->logs as $msg)
        {
            $msgs[] = $msg;
        }
        return $msgs;
    }
    
    public function get($type)
    {
        $msgs = [];
        switch(strtolower($type))
        {
            case 'log':
            case 'logs':
                return $this->getAll();
                break;
            case 'warn':
            case 'warns':
            case 'warning':
            case 'warnings':
                foreach($this->logs as $msg)
                    if($msg['type'] == 'warn' || $msg['type'] == 'error')
                        $msgs[] = $msg['msg'];
                break;
            default:
                foreach($this->logs as $msg)
                    if($msg['type'] == 'error')
                        $msgs[] = $msg['msg'];
                break;
        }
        return $msgs;   
    }
}