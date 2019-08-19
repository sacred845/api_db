<?php

namespace App\Api\V1Bundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Language as Entity;
use App\Api\V1Bundle\Lists\LangList as ListData;
use App\Model\Controller\Api\ApiController;

class IndexController extends ApiController
{
	public function getinfo(Request $request)
    {
		return $this->getResponse(self::ANSWER_OK, ['test' => 'ok']);
    }
}


/*
    public function list(Request $request)
    {
		return $this->getGrid(Grid::class, Entity::class, $request);				
    }
	*/