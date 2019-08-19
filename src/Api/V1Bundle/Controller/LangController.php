<?php

namespace App\Api\V1Bundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Language as Entity;
use App\Api\V1Bundle\Lists\LangList as ListData;
use App\Model\Controller\Api\ApiController;

class LangController extends ApiController
{
	public function list(Request $request)
    {
		$filter = [];
		return $this->getList(ListData::class, Entity::class,
			$request, $filter);	
    }
}
