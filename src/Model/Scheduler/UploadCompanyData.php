<?php

namespace App\Model\Scheduler;

use App\Model\Core;
use App\Model\Logger;
use App\Model\UploadOffice;

use App\Model\Companieshouse\Office;
use App\Model\Companieshouse\CompanieshouseFactory;
use App\Model\Companieshouse\CompanieshouseInterface;

abstract class UploadCompanyData implements ShedulerInterface
{
	protected $part;
	protected $tmpprotectfile;
	
	public function __construct ()
	{
	}
	
	public function __destruct()
	{
		$path = (Core::getInstance())->getTmpPath().'busy/';
		unlink($path.$this->tmpprotectfile);
	}
	
	abstract public function execute();

	public function setPart($part)
	{
		$this->part = $part;
		$path = (Core::getInstance())->getTmpPath().'busy/';
		$this->tmpprotectfile = 'protect'.$this->part;
		$f = fopen($path.$this->tmpprotectfile, 'w');
		fclose($f);
		return $this;
	}

	protected function openCompanyFile()
	{
		$name = (Core::getInstance())->getParameter('companieshouse');
		if (!is_null($this->part))
			$name = str_replace('.csv', '_'.$this->part.'.csv', $name);
		$path = (Core::getInstance())->getTmpPath();

		$f = fopen($path.$name, 'r');
		
		$this->log(Logger::PRIORITY_INFO, 'Отрытие файла '.$name);
		
		if (!$f) {
			$this->log(Logger::PRIORITY_ERROR, 'Не удается открыть файл '.$name);
			throw new \Exception('Не удается открыть файл '.$name);
		}
		
		return $f;
	}
	
	protected function initComps($name): CompanieshouseInterface
	{
		$factory = new CompanieshouseFactory();
		$keys = explode(',', (Core::getInstance())->getParameter('apikeys'));

		$accounts = (Core::getInstance())->getParameter('accounts');

		foreach (array_values($accounts) as $acc)
			$this->comps[] = $factory->getComp($acc, $name);
		if (count($this->comps) > 1)
			$this->compindex = $this->part;
			//$this->compindex = rand(0, count($this->comps) - 1);
		else
			$this->compindex = 0;

		return $this->comps[$this->compindex];
	}
	
	protected function toNextComp(): CompanieshouseInterface
	{
		if (count($this->comps) > 1) {
			$this->compindex = rand(0, count($this->comps) - 1);
			//$this->compindex = ($this->compindex + 1) % count($this->comps);
			$this->log(Logger::PRIORITY_INFO, 'Переключение на аккаунт '.$this->compindex);
			/*
			if ($this->compindex == 0) {
				$pause = 2*60;
				$this->log(Logger::PRIORITY_INFO, 'Пауза '.$pause.' секунд.');
				sleep($pause);
			}
			*/
		}
		
		return $this->comps[$this->compindex];
	}
	
	protected function createDataFile($name)
	{
		if (!is_null($this->part))
			$name = str_replace('.csv', '_'.$this->part.'.csv', $name);
		//$name = (Core::getInstance())->getParameter('companieshouse_officies');
		$path = (Core::getInstance())->getTmpPath();
		$f = fopen($path.$name, 'w');
		if (!$f)
			throw new \Exception('Не удается открыть файл '.$name);
		fclose($f);	
	}
	
	protected function saveData(array $data, $name)
	{
		if (!is_null($this->part))
			$name = str_replace('.csv', '_'.$this->part.'.csv', $name);
		//$name = (Core::getInstance())->getParameter('companieshouse_officies');
		$path = (Core::getInstance())->getTmpPath();
		$f = fopen($path.$name, 'a');
		if (!$f)
			throw new \Exception('Не удается открыть файл '.$name);
			
		foreach ($data as $line) {
			$str = '';
			foreach ($line as $item) {
				if ($str)
					$str .= ',';
				$str .= '"'.$item.'"';
			}
			fwrite($f, $str."\n");
		}
		
		fclose($f);
	}
	
	protected function log($priority, $mes)
	{
		(Core::getInstance())->toLog($priority, $mes, 'Comp_logger');
	}
}
