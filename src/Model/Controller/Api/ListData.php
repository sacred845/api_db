<?php

namespace App\Model\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\ControllerTrait;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Collections\Collection;
use App\Model\Core;
use App\Model\Translater;

abstract class ListData
{
	use ControllerTrait;
	
	protected $sortfield_default = 'id';
	protected $sorttype_default = 'ASC';
    protected $container;
    protected $entityname;
	protected $collection;
	protected $fields;
	protected $params;
    protected $sortby;
    protected $sorttype;	
	protected $use_translate = false;
	protected $filter;
	protected $request;
	protected $itemsonpage = 50;
	/*
    protected $collection;
    protected $paginator;
    protected $fields;
    protected $actions;
    protected $buttons;
    protected $action_route;
	protected $add_route;
    protected $grid_route;
    protected $title = 'Grid';
    protected $request;
    
    protected $optnumpages = [20,50,100,200];

    protected $search;
    protected $filter;
    protected $linestyles;
	protected $formview;
	protected $breadcrumb;
	protected $use_paginator = true;
	protected $edit_only = false;
	*/
	abstract protected function init(): self;
	
    public function __construct ($container)
    {
        $this->container = $container;
		$this->init();
        return $this;
    }
	
    public function setEntity($entity)
    {
        $this->entityname = $entity;
        return $this;
    }

    public function getData()
    {
        if (!$this->collection)
            $this->fetch();
            
		$res = [];	
	//	if ($this->params['url'] ?? null)
		$res['url'] = $this->request->server->get('REQUEST_URI');	
		$res['data'] = $this->prepareCollection();
		
        return $res;
    }
	
    public function setParam(string $name, $value)
    {
        $this->params[$name] = $value;
        return $this;
    }		
	
	public function setFilter(?array $filter)
	{
        $this->filter = $filter;
        return $this;
	}	
	
	public function setRequest(Request $request)
	{
        $this->request = $request;
        return $this;
	}
	
	protected function prepareCollection()
	{
		$res = null;

		$translater = null;
		if ($this->use_translate && $this->request->get('language_code'. null)) {
			$lang = (Core::getInstance())->getLangByCode($this->request->get('language_code'. null));
			if ($lang) {
				$translater = new Translater($lang);
			}
		}

		if ($this->collection) {
			foreach ($this->collection as $item) {
				$line = [];
				if ($translater ?? null) {
					$item = $translater->translate($item);
				}
				foreach ($this->fields as $key => $field) {
					$method = 'get' . str_replace('_', '', ucwords($field['name'], '_'));
					$value = $item->$method() ?? '';
					if ($value instanceof Collection) {
						$childreen = [];
						if ($value->count()) {
							foreach ($value as $listitem) {
								$childreen[] = $this->getSimpleSettings($listitem, $translater);
							}
						}
						$line[$key] = $childreen;
					} elseif ($value instanceof $this->entityname) {
						$line[$key] = $this->getSimpleSettings($value, $translater);
					} elseif (gettype($value) === 'object'){
						$line[$key] = $this->getSimpleSettings($value, $translater);
					} else
						$line[$key] = $value;
				}

				$res[] = $line;
			}
		}
		return $res;
	}
	
	protected function getSimpleSettings($listitem, ?Translater $translater = null)
	{
		$child = null;
		if ($translater ?? null) {
			$listitem = $translater->translate($listitem);
		}		
		
		foreach ($this->fields as $listitemkey => $listitemfield) {
			$listitemmethod = 'get' . str_replace('_', '', ucwords($listitemfield['name'], '_'));
			if (method_exists($listitem, $listitemmethod)) {
				$listitemvalue = $listitem->$listitemmethod() ?? '';
				if (!($listitemvalue instanceof Collection) && 
						!($listitemvalue instanceof $this->entityname && !is_object($listitemvalue)))
					if (gettype($listitemvalue) != 'object')
						$child[$listitemkey] = $listitemvalue;
			}
		}

		return $child;
	}
	
	protected function getTranslates($item)
	{
		$translations = [];
		$langs = (Core::getInstance())->getLanguagesForTr();
		foreach ($langs as $lang) {
			$translate = [];
			$translate['lang_id'] = $lang->getId();
			$translate['lang_code'] = $lang->getCodeIso6391();
			foreach($item->getTranslateField() as $tr) {
				$name = $tr . $lang->getSlug();
				$translate[$tr] = $item->$name;
			}
						
			$translations[] = $translate;
		}
		return $translations;
	}
	
    protected function getPaginator(Request $request)
    {
        $paginator = new \stdClass;
		
		$itemsonpage = (int)$request->get('count', null);
		$paginator->itemsonpage = ($itemsonpage && ($itemsonpage < 500)) ? $itemsonpage : $this->itemsonpage;
		$paginator->currpage = (int)$request->get('page', 0);
		if (!$paginator->currpage)
			$paginator->currpage++;
		
		$paginator->currpage--;

        return $paginator;        
    }	
	
    protected function fetch()
    {
        $this->sortby = $this->sortby ?? $this->sortfield_default;
        $this->sorttype = $this->sorttype ?? $this->sorttype_default;		
        try {
            $repository = $this->getDoctrine()
                            ->getRepository($this->entityname);
                            
            if (method_exists($repository, 'getForApi')) {
				$this->paginator = $this->getPaginator($this->request);
                $this->collection = $repository->getForApi( $this->paginator,
                        $this->sortby, $this->sorttype, $this->filter);
                
            } else {
                throw new \Exception('Database error.');
            }
        } catch (Exception $e) {
            throw new \Exception('Database error.');
        }
        return $this;
    }
}
