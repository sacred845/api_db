<?php

namespace App\Api\V1Bundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Currency as Entity;
use App\Api\V1Bundle\Lists\CurrencyList as ListData;
use App\Model\Controller\Api\ApiController;

class CurrencyController extends ApiController
{
	public function list(Request $request)
    {
		$filter = [];
		return $this->getList(ListData::class, Entity::class,
			$request, $filter);	
    }
}
