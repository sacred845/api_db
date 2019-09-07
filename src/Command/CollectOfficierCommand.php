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

class CollectOfficierCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('app:collect:officier');
    }
	


    protected function execute(InputInterface $input, OutputInterface $output)
    {
		$path = (Core::getInstance())->getTmpPath();
		$filedir = $path.'../public/files/';
		chdir($filedir);
		
		$name = $this->getContainer()->getParameter('comp_officies');
		$numstream = 10;
		
		$output = fopen($path.$name, 'w');
		stream_set_write_buffer ($output , 0 );
		
		for ($i = 0; $i < $numstream; $i++) {
			$filename = str_replace('.csv', '_'.$i.'.csv', $name);
			echo $filename."\n";
			$f = fopen($path.$filename, 'r');
			$n = 0;
			while($data = fgets($f)) {
				if ($n == 0) {
					if ($i == 0)
						fwrite($output, $data);
					$n++;
					continue;
				}
				fwrite($output, $data);	
				$n++;
			}
			fclose($f);
		}
		
		fclose($output);
		
		$em = $this->getContainer()->get('doctrine')->getManager();
		$newname = str_replace('.csv','_'.date('Y-m-d_h:i:s').'.csv',$name);
		$file = $em->getRepository(OutputFile::class)->findOneBy(['code' => 'comp_officies']);
		$file->setName($newname.'.zip');
		$em->flush($file);
		echo "Move file\n";	
		rename($path.$name, $filedir.$newname);
		echo "Pack file\n";
		exec ('zip '.$newname.'.zip '.$newname.' > /dev/null', $output, $return_var);
		echo "Clear old file\n";
		unlink($filedir.$newname);
	}
}
