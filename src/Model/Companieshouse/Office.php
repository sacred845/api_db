<?php

namespace App\Model\Companieshouse;

use App\Model\Core;

class Office extends CompanieshouseAbstract
{
	const OFICE_FILEDS = [
		'company_number',
		'appointed_on',
		'address:address_line_1',
		'address:address_line_2',
		'address:care_of',
		'address:country',
		'address:locality',
		'address:po_box',
		'address:postal_code',
		'address:premises',
		'address:region',
		'country_of_residence',
		'date_of_birth:day',
		'date_of_birth:month',
		'date_of_birth:year',
		'former_names:forenames',
		'former_names:surname',
		'identification:identification_type',
		'identification:legal_authority',
		'identification:legal_form',
		'identification:place_registered',
		'identification:registration_number',
		'links:officer:appointments',
		'name',
		'nationality',
		'occupation',
		'officer_role',
		'resigned_on'
	];

	public function getFields(): array
	{
		return [
			'company_number',
			'appointed_on',
			'address:address_line_1',
			'address:address_line_2',
			'address:care_of',
			'address:country',
			'address:locality',
			'address:po_box',
			'address:postal_code',
			'address:premises',
			'address:region',
			'country_of_residence',
			'date_of_birth:day',
			'date_of_birth:month',
			'date_of_birth:year',
			'former_names:forenames',
			'former_names:surname',
			'identification:identification_type',
			'identification:legal_authority',
			'identification:legal_form',
			'identification:place_registered',
			'identification:registration_number',
			'links:officer:appointments',
			'name',
			'nationality',
			'occupation',
			'officer_role',
			'resigned_on'
		];
	}

	public function getCSVData($number): ?array
	{
		return $this->getDataByNumber($number);
	}

	protected function getApiUrl(): string
	{
		return '/company/{company_number}/officers';
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
