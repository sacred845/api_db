<?php

namespace App\Command\Process;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

use App\Entity\QueuesProcess;
use App\Model\Scheduler\UploadFile;

class UploadFileProcess extends ContainerAwareCommand
{
	const URL = 'http://download.companieshouse.gov.uk/BasicCompanyDataAsOneFile-2019-08-01.zip';		
	
    protected function configure()
    {
        $this
            ->setName('app:file:load')
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
		set_time_limit(3600*24);
 //       throw new Exception('Деление на ноль.');
        
		$proc = $em->getRepository(QueuesProcess::class)->find($input->getArgument('id'));
		$proc->setPid(getmypid());
		$em->flush($proc);

		(new UploadFile(self::URL))->execute();
		echo "Step1\n";
    }
}
