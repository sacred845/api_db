<?php

namespace App\Model\Scheduler;

use App\Entity\UpdateFile;
use App\Entity\Ftp;
use Doctrine\ORM\EntityManager;
use App\Model\Core;
use App\Model\RemoteFile;
use App\Model\FtpServer;

class UploadFile implements ShedulerInterface
{
	private $url;
	
	public function __construct ($url)
	{
		$this->url = $url;
	}
	
	public function execute()
	{
		$name = 'companieshouse.csv';
		$path = (Core::getInstance())->getTmpPath();
		$file = new RemoteFile($this->url);
		if (!$file->download()) {
			throw new \Exception('Не удается загрузить файл');
		}

		$file->unpuckFile(); 
		if (file_exists($path.$name))
			unlink($path.$name);
		rename($file->getUnpuckfilepath(), $path.$name);

		/*
		$hashes = $this->entityfile->getUpdate()->getBase()->getFileHashes();

		if (in_array($file->getHash(), $hashes)) {
			$update = $this->entityfile->getUpdate();
			$this->em->remove($this->entityfile);
			$this->em->remove($update);
			$this->em->flush();
			$this->log(Logger::PRIORITY_INFO, 'File already was download');
			throw new \Exception('Файл уже был ранее загружен (hash:'.$file->getHash().')');
		}
		
		$file->packToZip();

		$this->entityfile->setName($file->getOriginName())
				->setOriginalFileSize($file->getOriginSize())
				->setFileHash($file->getHash())
				->setArchivedFileSize($file->getArchiveSize());
		
		$servers = $this->em->getRepository(Ftp::class)->findBy(['is_use' => true]);
		foreach ($servers as $server) {
			$this->log(Logger::PRIORITY_INFO, 'Copy to FTP \''.$server->getServer().'\'');
			$ftp = new FtpServer($server);
			$url = $ftp->upload($file);
			$this->entityfile->setFileUrl($url);
		}
		
		$this->log(Logger::PRIORITY_INFO, 'Save file data');
		$this->em->flush($this->entityfile);
		$event = new \App\AdminBundle\Dispatch\Events\UpdFileEvent($this->entityfile, $this->em);
		global $kernel;
		$kernel->getContainer()->get('event_dispatcher')->dispatch('file.load.after', $event);
		$this->log(Logger::PRIORITY_INFO, 'End work with file');
		*/
	}
	
    public function log(string $priority, ?string $mes): self
    {
        if (!$this->logger)
            $this->logger = new Logger('Files_logger');
        $this->logger->add($priority, $mes);
        return $this;
    }
}
