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

class TestAllCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('app:testall:time');
    }
	


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $num = 30;
        $processes = [];
        for ($i = 0; $i < $num; $i++) {
            $processes[$i] = new Process(array('/usr/bin/php', 
                            $this->getContainer()->get('kernel')->getProjectDir().'/bin/console', 
                            'app:test:time'));
			$processes[$i]->setTimeout(3600*24*20);
			//$processes[$i]->setIdleTimeout(3600*24*20);
			$processes[$i]->disableOutput();
            $processes[$i]->start();

        }

        $isbisy = true; 
		$ishaserror = false;
        while($isbisy) {
            $isbisy = false; 
            for ($i = 0; $i < $num; $i++) {
                $isbisy = $isbisy || $processes[$i]->isRunning();
            sleep(3600);			}
        }
 
        for ($i = 0; $i < $num; $i++) {
			$ishaserror = $ishaserror || $processes[$i]->getErrorOutput();;
		} 


        echo "OK\n";
	}
}
