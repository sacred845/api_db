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

class TestCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('app:test:time');
    }
	


    protected function execute(InputInterface $input, OutputInterface $output)
    {
		set_time_limit(3600*24*20);

		$a  = 1;

		while($a) {
			
			(Core::getInstance())->toLog(Logger::PRIORITY_DEBUG, $a." min\n", 'Test_logger');
			sleep(60);
			$a++;
		}
	}
}
