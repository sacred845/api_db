<?php

namespace App\Model\Companieshouse;

use App\Model\Core;

abstract class CompanieshouseAbstract implements CompanieshouseInterface
{
	const API_URL = 'https://api.companieshouse.gov.uk/';
	
	private $apikey;
	private $responsecode;
	
	abstract public function getCSVData($number): ?array;
	abstract public function getFields(): array;
	abstract protected function getApiUrl(): string;
	
	public function __construct($apikey)
	{
		$this->apikey = $apikey;
	}	
	
	public function isOverLimit(): bool
	{
		return $this->responsecode == 429;
	}
	
	public function getHttpCode(): int
	{
		return $this->responsecode;
	}
	
	protected function getRequestUrl($number): string
	{
		$url = self::API_URL.$this->getApiUrl().'?items_per_page=100';
		$url = str_replace('{company_number}', $number, $url);	
		
		return $url;
	}
	
	protected function getDataByNumber($number)
	{
		$url = $this->getRequestUrl($number);
		$respnose = (Core::getInstance())->sendGetAuthRequest($url, $this->apikey.':');
		$this->responsecode = $respnose['code'];
		$res = null;
		if (($this->responsecode == 200) && $respnose['response']) {
			$data = json_decode($respnose['response'], true);
			if (!($data['items'] ?? null))
				;//var_dump($data);
			else
				$res = $this->processData($data['items'], $number);
		}
		return $res;
	}
	
	protected function processData($items, $number): ?array
	{
		$res = [];
	//	var_dump($items);
	//	exit;
		foreach ($items as $item) {
			$item['company_number'] = $number;
			foreach ($this->getFields() as $field) {
				$paramname = str_replace(':', '_', $field);
				$line[$paramname] = $this->getValueByField($field, $item);
			}	
			$res[] = $line;
		}
		
		return $res;
	}
	
	protected function getValueByField($field, $item)
	{
		$value = '';
		if (strpos($field, ':') !== false) {
			$objfileds = explode(':', $field);
			$value = $item;
			foreach ($objfileds as $fieldname) {
				$value = $value[$fieldname] ?? '';
			}
		} else
			$value = $item[$field] ?? '';
		
		if (is_array($value))
			$value = implode(',', $value);
		return $value;
	}
}
