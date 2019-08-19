<?php

namespace App\Model;

class Logger
{
    const PRIORITY_FATAL = 'fatal';
    const PRIORITY_ERROR = 'error';
    const PRIORITY_WARN = 'warn';
    const PRIORITY_INFO = 'info';
    const PRIORITY_DEBUG = 'debug';

    private $proc;
	private $logname;

    public function __construct ($logname = 'logger')
    {
		$this->logname = $logname;
        return $this;
    }
    
    public function add($priority, $mes): self
    {
        $str = date('Y-m-d H:i:s').':'."\t".getmypid()."\t".$priority.' - '.$mes."\n";
        $this->writeToFile($str);
        return $this;
    }
    
    public function getContent(): ?string
    {
        $res = '';
        if (file_exists($this->getFilename()))
            $res = file_get_contents($this->getFilename());
        else 
            $res = "Файл не найден.";
        return $res;
    }
    
    private function writeToFile($mes)
    {
        $f = fopen($this->getFilename(), 'a');
        fwrite($f, $mes);
        fclose($f);
    }
    
    private function getFilename(): string
    {
        global $kernel;
        return $this->getPath().$this->logname;
    }
    
    private function getPath(): string
    {
        global $kernel;
        return $kernel->getRootDir().'/../var/log/';
    }
}
