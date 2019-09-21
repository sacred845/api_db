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

class OfficierDownloadCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('app:officier:download');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
		set_time_limit(3600*24*20);
		ini_set("memory_limit","-1");
        $container = $this->getContainer();
        $em = $this->getContainer()->get('doctrine')->getManager();
		$num = (int)$this->getContainer()->getParameter('num_thread');
		
		$task = new QueuesTask ();
		$task->setCode(QueuesTask::CODE_DOWNLOADOFFICIERS)
				->setParams(['num' => $num])
				->setStatus(QueuesTask::STATUS_INPROGRESS);
		$em->persist($task);
		$em->flush($task);		
		
        
        $processes = [];
        for ($i = 0; $i < $num; $i++) {
            $processes[$i] = new Process(array('/usr/bin/php', 
                            $this->getContainer()->get('kernel')->getRootDir().'/../bin/console', 
                            'app:officierpath:update', $i));
			$processes[$i]->setTimeout(3600*24*20);
			$processes[$i]->setIdleTimeout(3600*24*20);
            $processes[$i]->start();

        }
        
        $isbisy = true; 
		$ishaserror = false;
        while($isbisy) {
            $isbisy = false; 
            for ($i = 0; $i < $num; $i++) {
                $isbisy = $isbisy || $processes[$i]->isRunning();
			}
        }
 
        for ($i = 0; $i < $num; $i++) {
			$ishaserror = $ishaserror || $processes[$i]->getErrorOutput();;
		} 
		
		if (!$ishaserror) {
			echo 'Collect data';
			$collect = new Process(array('/usr/bin/php', 
                            $this->getContainer()->get('kernel')->getRootDir().'/../bin/console', 
                            'app:collect:officier'));
			$collect->setTimeout(3600*24);
			$collect->setIdleTimeout(3600*24);
			try {
				$collect->mustRun();
			} catch (ProcessFailedException $exception) {
				$ishaserror = true;
			}		
			echo $collect->getOutput();			
		}

		$task->setStatus($ishaserror ? QueuesTask::STATUS_ERROR : QueuesTask::STATUS_SUCCESS)
			->setFinishedAt(new \DateTime('now'));
		$em->flush($task);		
        echo 'OK';
	}
}
