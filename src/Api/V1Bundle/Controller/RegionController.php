<?php

namespace App\Api\V1Bundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Region as Entity;
use App\Api\V1Bundle\Lists\RegionList as ListData;
use App\Model\Controller\Api\ApiController;

class RegionController extends ApiController
{
	public function list(Request $request)
    {
		$filter = [];
		if ($request->get('uri', null))
			$filter['uri'] = $request->get('uri');
		else {
			if ($request->get('enabled_on_site', null)) {
				$enable = $request->get('enabled_on_site');
				if (in_array($enable, ['true', 'false'])) {
					$enable = ($enable == 'true') ? true : false;
					$filter['enabled_on_site'] = $enable;
				}
			}
			if ($request->get('locations_country_id', null)) {
				$filter['locations_country_id'] = $request->get('locations_country_id', null);
			}
			if ($request->get('locations_country_uri', null)) {
				$filter['locations_country_uri'] = $request->get('locations_country_uri', null);
			}
			if ($request->get('locations_state_id', null)) {
				$filter['locations_state_id'] = $request->get('locations_state_id', null);
			}
			if ($request->get('locations_state_uri', null)) {
				$filter['locations_state_uri'] = $request->get('locations_state_uri', null);
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
