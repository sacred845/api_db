<?php

namespace App\Command;

use App\Entity\QueuesTask;
use App\Entity\QueuesProcess;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use App\Model\Core;
use App\Model\Logger;
use App\Entity\OutputFile;

class DownloadCompanyCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('app:company:download');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
		set_time_limit(3600*24*20);
		ini_set("memory_limit","-1");
        $container = $this->getContainer();
        $em = $this->getContainer()->get('doctrine')->getManager();
		
		$task = new QueuesTask ();
		$task->setCode(QueuesTask::CODE_DOWNLOADCOMPANY)
				->setStatus(QueuesTask::STATUS_INPROGRESS);
		$em->persist($task);
		$em->flush($task);			
		/*
		if ($em->getRepository(QueuesTask::class)->isFullTaskStack()) {
			(Core::getInstance())->toLog(Logger::PRIORITY_ERROR, 'Too many tasks.', 'Files_logger');
			echo "Too many tasks\n";
			return;
		}
		*/
		$ishaserror = false;
		$proc = new QueuesProcess();
		$proc->setStatus(QueuesProcess::STATUS_INPROGRESS)
					->setStartedAt(new \DateTime('now'))
					->setTask($task);		
		$em->persist($proc);
		$em->flush($proc);
		$process = new Process(array('/usr/bin/php', 
						$this->getContainer()->get('kernel')->getRootDir().'/../bin/console', 
						'app:file:load', $proc->getId()));	
		$process->setTimeout(3600*24);
		$process->setIdleTimeout(3600*24);
		try {
			$process->mustRun();
			$proc->setStatus(QueuesProcess::STATUS_SUCCESS);
		} catch (ProcessFailedException $exception) {
			$proc->setStatus(QueuesProcess::STATUS_ERROR)
					->setMessage($exception->getMessage());
			$ishaserror = true;
		}
		echo $process->getOutput();
		$proc->setFinishedAt(new \DateTime('now'));
		$em->flush($proc);
		
		if (!$ishaserror) {
			$path = (Core::getInstance())->getTmpPath();
			$filedir = $path.'../public/files/';
			$name = $this->getContainer()->getParameter('companieshouse'); 
			$newname = str_replace('.csv','_'.date('Y-m-d_h:i:s').'.csv',$name);
			$file = $em->getRepository(OutputFile::class)->findOneBy(['code' => 'companieshouse']);
			$file->setName($newname.'.zip');
			$em->flush($file);
			chdir($filedir);
			copy($path.$name, $filedir.$newname);
			exec ('zip '.$newname.'.zip '.$newname.' > /dev/null', $output, $return_var);
			unlink($filedir.$newname);
		}

		$task->setStatus($ishaserror ? QueuesTask::STATUS_ERROR : QueuesTask::STATUS_SUCCESS)
			->setFinishedAt(new \DateTime('now'));
		$em->flush($task);
    }
}
