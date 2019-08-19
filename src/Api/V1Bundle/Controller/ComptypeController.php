<?php

namespace App\Api\V1Bundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\CompanyType as Entity;
use App\Api\V1Bundle\Lists\CompanyTypeList as ListData;
use App\Model\Controller\Api\ApiController;

class ComptypeController extends ApiController
{
	public function list(Request $request)
    {
		$filter = [];
		if ($request->get('uri', null))
			$filter['uri'] = $request->get('uri');
		elseif ($request->get('enabled_on_site', null)) {
			$enable = $request->get('enabled_on_site');
			if (in_array($enable, ['true', 'false'])) {
				$enable = ($enable == 'true') ? true : false;
				$filter['enabled_on_site'] = $enable;
			}
		}
		return $this->getList(ListData::class, Entity::class,
			$request, $filter);	
    }
	
	public function item($id, Request $request)
    {
		$filter = ['id' => $id];
		if ($request->get('uri', null))
			$filter['uri'] = $request->get('uri');
		return $this->getList(ListData::class, Entity::class,
				$request, $filter);	
    }
}
