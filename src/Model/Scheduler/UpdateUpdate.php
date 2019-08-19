<?php

namespace App\Model\Scheduler;

use App\Entity\Update;
use App\Entity\UpdateFile;
use App\Entity\Ftp;
use Doctrine\ORM\EntityManager;
use App\Model\Core;
use App\Model\RemoteFile;
use App\Model\FtpServer;
use App\Model\Logger;

class UpdateUpdate implements ShedulerInterface
{
	private $em;
	private $entityupdate;
	
	public function __construct (Update $entityupdate, EntityManager $em)
	{
		$this->entityupdate = $entityupdate;
		$this->em = $em; 
	}
	
	public function execute()
	{
		$files = $this->entityupdate->getFiles();
		$base = $this->entityupdate->getBase();
		$hashes = $base->getFileHashes();
		$container = (Core::getInstance())->getServiceContainer();
		if ($files->count()) {
			$update = null;
			$tmpupdate = new Update();
			$tmpupdate->setBase($base);
			foreach ($files as $file) {
				//$hashes = $basefile->getUpdatesFileHashes();
				$updatefile = new UpdateFile();
				if ($file->getBaseFile())
					$updatefile->setBaseFile($file->getBaseFile());
				else
					$updatefile->setCustomUrl($file->getCustomUrl());
				$updatefile->setFormat($file->getFormat());
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
									->setBase($base)
									->setUserId($base->getUserId())
									->setUpdateNumber($base->getNextUpdateNum());
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
				} else {
					(Core::getInstance())->toLog(Logger::PRIORITY_INFO, 'File already was download', 'Files_logger');
				}
				
				if ($update) {
					$event = new \App\AdminBundle\Dispatch\Events\BaseUpdateEvent($update, $this->em);
					$container->get('event_dispatcher')->dispatch('update.load.after', $event);
				}
			}
		}
	}
}
