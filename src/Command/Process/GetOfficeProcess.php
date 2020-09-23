<?php

namespace App\Command\Process;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

use App\Entity\QueuesProcess;
use App\Model\Core;
use App\Model\Logger;
use App\Model\Scheduler\UploadOffice;

use App\Model\Companieshouse\Office;
use App\Model\Companieshouse\CompanieshouseFactory;
use App\Model\Companieshouse\CompanieshouseInterface;

class GetOfficeProcess extends ContainerAwareCommand
{
	private $comps;
	private $compindex;
	
    protected function configure()
    {
        $this
            ->setName('app:companieshouse:getoffice')
            ->addArgument('id', InputArgument::REQUIRED, 'Process id.')
			->addArgument('part', InputArgument::REQUIRED, 'Part id.');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        ini_set("memory_limit","-1");
		set_time_limit(3600*24*20);
        
		$proc = $em->getRepository(QueuesProcess::class)->find($input->getArgument('id'));
		$proc->setPid(getmypid());
		$em->flush($proc);
        $em->getUnitOfWork()->clear(QueuesProcess::class);

		(new UploadOffice())->setPart($input->getArgument('part'))->execute();
/*
		$name = $this->getContainer()->getParameter('companieshouse');
		$path = (Core::getInstance())->getTmpPath();

		$f = fopen($path.$name, 'r');
		
		$this->log(Logger::PRIORITY_INFO, 'Отрытие файла '.$name);
		
		if (!$f) {
			$this->log(Logger::PRIORITY_ERROR, 'Не удается открыть файл '.$name);
			throw new \Exception('Не удается открыть файл '.$name);
		}
		$titles = fgetcsv($f);
		$titles = array_map('trim', $titles);
		$titles = array_map('strtolower', $titles);
		$companyindex = (array_flip($titles))['companynumber'];

		$this->log(Logger::PRIORITY_INFO, 'Инициализация обэектов');
		$comp = $this->initComps();
		$this->createOfficeFile();
		$titles = array_map(function($item){return str_replace(':', '_', $item);},
										$comp->getFields());
		$this->saveOficies([$titles]);
		
		$n = 0;
		$httperrors = [];
		while ($data = fgetcsv($f)) {
			if ($n == 20) break;
			$companynumber = $data[$companyindex];
			$data = $comp->getCSVData($companynumber);
			if ($data) {
				$this->saveOficies($data);
				$n++;
			} else {
				if ($comp->getHttpCode() == 403) {
					$j = 0;
					while(($comp->getHttpCode() == 403) && ($j < 10)) {
						sleep(10);
						$data = $comp->getOficiesByCompanyNumber($companynumber);
						if ($data) {
							$this->saveOficies($data);
							$n++;
						} else 
							$this->log(Logger::PRIORITY_ERROR, $comp->getHttpCode().' - попытка '.$j);
						$j++;
					}
				}
				
				if (!$data)
					$httperrors[$comp->getHttpCode()] = ($httperrors[$comp->getHttpCode()] ?? 0) + 1;
				
				if ($comp->isOverLimit())
					$comp = $this->toNextComp();
			}
			
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
*/
		echo "Success\n";
    }
	
	protected function initComps(): CompanieshouseInterface
	{
		$factory = new CompanieshouseFactory();
		$keys = $this->getContainer()->getParameter('apikeys');
		foreach ($keys as $key)
			$this->comps[] = $factory->getComp($key, 'office');
		$this->compindex = 0;

		return $this->comps[$this->compindex];
	}
	
	protected function toNextComp(): CompanieshouseInterface
	{
		if (count($this->comps) > 1) {
			$this->compindex = ($this->compindex + 1) % count($this->comps);
			$this->log(Logger::PRIORITY_INFO, 'Переключение на аккаунт '.$this->compindex);
			if ($this->compindex == 0) {
				$pause = 4*60;
				$this->log(Logger::PRIORITY_INFO, 'Пауза '.$pause.' секунд.');
				sleep($pause);
			}
		}
		
		return $this->comps[$this->compindex];
	}
	
	protected function createOfficeFile()
	{
		$name = $this->getContainer()->getParameter('companieshouse_officies');
		$path = (Core::getInstance())->getTmpPath();
		$f = fopen($path.$name, 'w');
		if (!$f)
			throw new \Exception('Не удается открыть файл '.$name);
		fclose($f);	
	}
	
	protected function saveOficies(array $data)
	{
		$name = $this->getContainer()->getParameter('companieshouse_officies');
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
