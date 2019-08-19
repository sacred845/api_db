<?php

namespace App\Api\V1Bundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\UserNews as Entity;
use App\Api\V1Bundle\Lists\UserNewsList as ListData;
use App\Model\Controller\Api\ApiController;

class UserNewsController extends ApiController
{
	public function list(Request $request)
    { 
		$filter = [];

			if ($request->get('enabled_on_site', null)) {
				$enable = $request->get('enabled_on_site');
				if (in_array($enable, ['true', 'false'])) {
					$enable = ($enable == 'true') ? true : false;
					$filter['enabled_on_site'] = $enable;
				}
			}


		return $this->getList(ListData::class, Entity::class,
			$request, $filter);	
    }
}
