<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Entity\OutputFile;

class DownloadController extends AbstractController
{
    public function downloadfile($name, Request $request)
	{
		$em = $this->getDoctrine()->getManager();
		$file = $em->getRepository(OutputFile::class)->findOneBy(['code' => $name]);
		if (!$file) {
			echo 'Файл не существует';
			exit;
		} else
			 return $this->redirect('/files/'.$file->getName());
	}
}
