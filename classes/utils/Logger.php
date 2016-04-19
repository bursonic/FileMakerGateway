<?php

class Logger
{
    private $logFilePath;

    public function __construct($path)
    {
        $this->logFilePath = $path;
    }

    public function log($msg)
    {
        $message = date('Y-m-d h:i:s ') . ' ' . $msg . PHP_EOL;

        file_put_contents($this->logFilePath, $message, FILE_APPEND);
    }
}