<?php

namespace App\Api\V1Bundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\News as Entity;
use App\Api\V1Bundle\Lists\NewsList as ListData;
use App\Api\V1Bundle\Lists\NewsItemList as ItemData;
use App\Model\Controller\Api\ApiController;

class NewsController extends ApiController
{
	public function list(Request $request)
    { 
		$filter = [];
		if ($request->get('uri', null)) {
			$filter['uri'] = $request->get('uri');
			return $this->item(0, $request);
		}
		else {
			if ($request->get('enabled_on_site', null)) {
				$enable = $request->get('enabled_on_site');
				if (in_array($enable, ['true', 'false'])) {
					$enable = ($enable == 'true') ? true : false;
					$filter['enabled_on_site'] = $enable;
				}
			}
			if ((int)$request->get('category_id', null))
				$filter['category_id'] = (int)$request->get('category_id');
			if ($request->get('category_uri', null))
				$filter['category_uri'] = $request->get('category_uri');
			if ($request->get('tags', null))
				$filter['tags'] = $request->get('tags');
		}
		return $this->getList(ListData::class, Entity::class,
			$request, $filter);	
    }
	
	public function item($id, Request $request)
    {
		if ($id && !$request->get('uri', null))
			$filter = ['id' => $id];
		if ($request->get('uri', null))
			$filter['uri'] = $request->get('uri');

		return $this->getList(ItemData::class, Entity::class,
				$request, $filter);	
    }
}
