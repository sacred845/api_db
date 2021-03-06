<?php

namespace App\Model\Scheduler;

use App\Model\Core;
use App\Model\Logger;

class UploadOffice extends UploadCompanyData
{
	public function execute()
	{
		$name = (Core::getInstance())->getParameter('companieshouse');
		$path = (Core::getInstance())->getTmpPath();
		$datafilename = (Core::getInstance())->getParameter('comp_officies');

		$f = $this->openCompanyFile();
		$titles = fgetcsv($f);
		$titles = array_map('trim', $titles);
		$titles = array_map('strtolower', $titles);
		$companyindex = (array_flip($titles))['companynumber'];
		
		$this->log(Logger::PRIORITY_INFO, 'Инициализация обэектов');
		$comp = $this->initComps('office'); 
	//	if (is_null($this->part) || ($this->part == 0))
		$this->createDataFile((Core::getInstance())->getParameter('comp_officies'));
		$titles = array_map(function($item){return str_replace(':', '_', $item);},
										$comp->getFields());
		$this->saveData([$titles], $datafilename);
		
		$n = 0; 
		$j = 0;
		$httperrors = [];
		while ($data = fgetcsv($f)) {
			$j++;
			//if ($n == 20) break;
			$companynumber = $data[$companyindex];
			$data = $comp->getCSVData($companynumber);
			if ($data) {
				$this->saveData($data, $datafilename);
				$n++;
			} else {
			//	$this->log(Logger::PRIORITY_INFO, 'Опрошено '.$n.' компаний.');
			//	$this->log(Logger::PRIORITY_ERROR, ' Получен код ошибки '.$comp->getHttpCode());
				if ($comp->getHttpCode() == 403) {					
					$j = 0;
					while(($comp->getHttpCode() == 403) && ($j < 20)) {
						sleep(10);
						$data = $comp->getCSVData($companynumber);
						if ($data) {
							$this->saveData($data, $datafilename);
							$n++;
						} else 
							$this->log(Logger::PRIORITY_ERROR, $comp->getHttpCode().' - попытка '.$j);
						$j++;
					}
				}
				
				if (!$data)
					$httperrors[$comp->getHttpCode()] = ($httperrors[$comp->getHttpCode()] ?? 0) + 1;
				
				if ($comp->isOverLimit()) {
					
					$j = 0;
					while($comp->isOverLimit() && ($j < 20)) {
						sleep(60);
						$data = $comp->getCSVData($companynumber);
						
						if ($data) {
							$this->saveData($data, $datafilename);
							$n++;
						} else 
							$this->log(Logger::PRIORITY_ERROR, $comp->getHttpCode().' - попытка '.$j);
						$j++;
					}	
					//$comp = $this->toNextComp();
				}
			}
			/*
			if (($j > 20) && ($n == 0))
				$comp = $this->toNextComp();
			*/
			if (($n % 1000) == 0)
				$this->log(Logger::PRIORITY_INFO, 'Получено данных с  '.$n.' компаний.');
		}
		
		$this->log(Logger::PRIORITY_INFO, 'Всего получено данных с  '.$n.' компаний.');
		if (!empty($httperrors)) {
			$errormes = 'Данные не были получена со след. ошибками:';
			foreach ($httperrors as $key => $numerror) {
				$errormes .= $key . ' - '.$numerror. ', ';
			}
			$this->log(Logger::PRIORITY_ERROR, $errormes);
		}
	}
}
