<?php

namespace App\Command\Process;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

use App\Entity\QueuesProcess;
use App\Model\Scheduler\UploadFile;
use App\Model\Core;
use App\Model\Logger;

class TestProcess extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('app:test:proc')
            ->addArgument('id', InputArgument::REQUIRED, 'Process id.');
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
