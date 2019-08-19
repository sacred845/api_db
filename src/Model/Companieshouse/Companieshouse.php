<?php

namespace App\Model\Companieshouse;

use App\Model\Core;

class Companieshouse
{
	const API_URL = 'https://api.companieshouse.gov.uk/';
	const API_GETOFFICEIES = '/company/{company_number}/officers';
	
	const OFICE_FILEDS = [
		'company_number',
		'officer_role',
		'links:officer:appointments',
		'occupation',
		'name',
		'nationality',
		'country_of_residence',
		'address:country',
		'address:region',
		'address:premises',
		'address:address_line_2',
		'address:locality',
		'address:postal_code',
		'address:address_line_1',
		'date_of_birth:month',
		'date_of_birth:year',
		'appointed_on'
	];

	private $apikey;
	private $responsecode;
	
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
	
	public function getOficiesByCompanyNumber($number): ?array
	{
		$url = self::API_URL.self::API_GETOFFICEIES.'?items_per_page=100';
		$url = str_replace('{company_number}', $number, $url);
		$respnose = (Core::getInstance())->sendGetAuthRequest($url, $this->apikey.':');
		$this->responsecode = $respnose['code'];
		$res = null;
		if (($this->responsecode == 200) && $respnose['response']) {
			$data = json_decode($respnose['response'], true);
			$items = $data['items'];
			foreach ($items as $item) {
				$item['company_number'] = $number;
				foreach (self::OFICE_FILEDS as $field) {
					if (strpos($field, ':') !== false) {
						$objfileds = explode(':', $field);
						$value = $item;
						foreach ($objfileds as $fieldname) {
							$value = $value[$fieldname] ?? '';
						}
						$line[str_replace(':', '_', $field)] = $value;
					} else
						$line[$field] = $item[$field] ?? '';
				}	
				$res[] = $line;
			}
		}
		return $res;
	}
}
