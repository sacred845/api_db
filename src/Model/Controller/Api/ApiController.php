<?php

namespace App\Model\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;


class ApiController extends Controller
{
    const ANSWER_NOT_FOUND = 'not_found';
    const ANSWER_OK = 'ok';
	
	protected function getList($listclass, $entity, Request $request, $filter = [])
	{
		$list = (new $listclass ($this->container))
				->setEntity($entity)
				->setFilter($filter)
				->setRequest($request);

		return $this->getResponse(self::ANSWER_OK, $list->getData());
	}
	
    protected function getResponse(string $type, $data = null): JsonResponse
    {
        $res = [];
        switch ($type) {
			case self::ANSWER_NOT_FOUND :
				$res = ['status' => 'error', 'msg' => 'Not found'];
                break;

        case self::ANSWER_OK :
				$res = $data;
                break;
        }
		
        return new JsonResponse($res);
    }

}
