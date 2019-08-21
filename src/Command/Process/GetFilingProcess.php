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
use App\Model\Scheduler\UploadFiling;
use App\Model\Companieshouse\Filing;
use App\Model\Companieshouse\CompanieshouseInterface;

class GetFilingProcess extends ContainerAwareCommand
{
	private $comps;
	private $compindex;
	
    protected function configure()
    {
        $this
            ->setName('app:companieshouse:getfiling')
            ->addArgument('id', InputArgument::REQUIRED, 'Process id.');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {/*
        $tasks = $this->getContainer()->getParameter('cron_tasks');
        $currtask = $tasks[$input->getArgument('task')];
        $classname = $currtask['class'];
        $method = $currtask['method'];
        (new $classname)->$method();*/
       // echo $input->getArgument('id')."\n";
        $em = $this->getContainer()->get('doctrine')->getManager();
     //   $process = $em->getRepository('App:Process')->find($input->getArgument('id'));
       // $parser = $em->getRepository('App:Admin\Parsers')->find($process->getParserId());
      //  echo $parser->getId()."\n";
        ini_set("memory_limit","-1");
		set_time_limit(3600*24*20);
 //       throw new Exception('Деление на ноль.');
        
		$proc = $em->getRepository(QueuesProcess::class)->find($input->getArgument('id'));
		$proc->setPid(getmypid());
		$em->flush($proc);

		(new UploadFiling())->execute();

		echo "Success\n";
    }

}
