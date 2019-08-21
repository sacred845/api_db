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
	public function __construct ()
	{
	}
	
	abstract public function execute();

	protected function openCompanyFile()
	{
		$name = (Core::getInstance())->getParameter('companieshouse');
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

		foreach ($keys as $key)
			$this->comps[] = $factory->getComp($key, $name);
		$this->compindex = 0;

		return $this->comps[$this->compindex];
	}
	
	protected function toNextComp(): CompanieshouseInterface
	{
		if (count($this->comps) > 1) {
			$this->compindex = ($this->compindex + 1) % count($this->comps);
			$this->log(Logger::PRIORITY_INFO, 'Переключение на аккаунт '.$this->compindex);
			if ($this->compindex == 0) {
				$pause = 2*60;
				$this->log(Logger::PRIORITY_INFO, 'Пауза '.$pause.' секунд.');
				sleep($pause);
			}
		}
		
		return $this->comps[$this->compindex];
	}
	
	protected function createDataFile($name)
	{
		//$name = (Core::getInstance())->getParameter('companieshouse_officies');
		$path = (Core::getInstance())->getTmpPath();
		$f = fopen($path.$name, 'w');
		if (!$f)
			throw new \Exception('Не удается открыть файл '.$name);
		fclose($f);	
	}
	
	protected function saveData(array $data, $name)
	{
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
