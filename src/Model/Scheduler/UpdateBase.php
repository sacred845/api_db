<?php

namespace App\Model\Scheduler;

use App\Entity\Base;
use App\Entity\UpdateFile;
use App\Entity\Ftp;
use Doctrine\ORM\EntityManager;
use App\Model\Core;
use App\Model\RemoteFile;
use App\Model\FtpServer;
use App\Entity\Update;

class UpdateBase implements ShedulerInterface
{
	private $em;
	private $entitybase;
	
	public function __construct (Base $entitybase, EntityManager $em)
	{
		$this->entitybase = $entitybase;
		$this->em = $em; 
	}
	
	public function execute()
	{
		$basefiles = $this->entitybase->getBaseFiles();
		$container = (Core::getInstance())->getServiceContainer();
		if ($basefiles->count()) {
			$update = null;
			$tmpupdate = new Update();
			$tmpupdate->setBase($this->entitybase);
			foreach ($basefiles as $basefile) {
				$hashes = $basefile->getUpdatesFileHashes();
				$updatefile = new UpdateFile();
				$updatefile->setBaseFile($basefile)
					->setFormat($basefile->getFormat());
				if ($update)	
					$updatefile->setUpdate($update);
				else {
					$updatefile->setUpdate($tmpupdate);
				}
				
				$file = new RemoteFile($updatefile);
				if (!$file->download()) {
					throw new \Exception('Не удается загрузить файл');
				}
				if (!in_array($file->getHash(), $hashes)) {
					$file->packToZip();

					try {
						$this->em->getConnection()->beginTransaction();
						if (!($update instanceOf Update)) {
							$update = new Update();
							$update->setAsOfDate(new \DateTime('now'))
									->setBase($this->entitybase)
								//	->setUserId($this->entitybase->getUserId())
									->setUpdateNumber($this->entitybase->getNextUpdateNum());
							$this->em->persist($update);
							$this->em->flush($update);
						}
						$updatefile->setUpdate($update);
						$this->em->persist($updatefile);
						$this->em->flush($updatefile);
						$servers = $this->em->getRepository(Ftp::class)->findBy(['is_use' => true]);
						foreach ($servers as $server) {
							$ftp = new FtpServer($server);
							$url = $ftp->upload($file);
							$updatefile->setFileUrl($url);
						}
						
						$updatefile->setName($file->getOriginName())
								->setOriginalFileSize($file->getOriginSize())
								->setFileHash($file->getHash())
								->setArchivedFileSize($file->getArchiveSize());						
						
						$this->em->flush($updatefile);
						$event = new \App\AdminBundle\Dispatch\Events\UpdFileEvent($updatefile, $this->em);
						$container->get('event_dispatcher')->dispatch('file.load.after', $event);
						$this->em->getConnection()->commit();
                    } catch (Exception $e) {
                        $this->em->getConnection()->rollback();
                        throw $e;
                    }
				}
				if ($update) {
					$event = new \App\AdminBundle\Dispatch\Events\BaseUpdateEvent($update, $this->em);
					$container->get('event_dispatcher')->dispatch('update.load.after', $event);
				}
				//var_dump($hashes);
			}
		}
		/*
		$path = (Core::getInstance())->getTmpPath();
		$file = new RemoteFile($this->entityfile);
		if (!$file->download()) {
			throw new \Exception('Не удается загрузить файл');
		}
		
		$file->packToZip();

		$this->entityfile->setName($file->getOriginName())
				->setOriginalFileSize($file->getOriginSize())
				->setFileHash($file->getHash())
				->setArchivedFileSize($file->getArchiveSize());
		
		$servers = $this->em->getRepository(Ftp::class)->findBy([]);
		foreach ($servers as $server) {
			$ftp = new FtpServer($server);
			$url = $ftp->upload($file);
			$this->entityfile->setFileUrl($url);
		}
		
		$this->em->flush($this->entityfile);
		*/
	}
}
