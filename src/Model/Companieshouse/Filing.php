<?php

namespace App\Model\Companieshouse;

use App\Model\Core;

class Filing extends CompanieshouseAbstract
{
	public function getFields(): array
	{
		return [
			'company_number',
			'barcode',
			'category',
			'date',
			'description',
			'description_values:made_up_date',
			'links:document_metadata',
			'links:self',
			'pages',
			'paper_filed',
			'subcategory',
			'transaction_id',
			'type',
			'associated_filings:0:category',
			'associated_filings:0:date',
			'associated_filings:0:description',
			'associated_filings:0:description_values:capital:0:figure',
			'associated_filings:0:description_values:capital:0:currency',
			'associated_filings:0:type',
			'associated_filings:1:category',
			'associated_filings:1:date',
			'associated_filings:1:description',
			'associated_filings:1:description_values:capital:0:figure',
			'associated_filings:1:description_values:capital:0:currency',
			'associated_filings:1:type',
			'annotations:0:annotation',
			'annotations:0:category',
			'annotations:0:date',
			'annotations:0:description',
			'annotations:0:type',
			'annotations:1:annotation',
			'annotations:1:category',
			'annotations:1:date',
			'annotations:1:description',
			'annotations:1:type',
			'resolutions:0:category',
			'resolutions:0:description',
			'resolutions:0:document_id',
			'resolutions:0:receive_date',
			'resolutions:0:subcategory',
			'resolutions:0:type',
			'resolutions:1:category',
			'resolutions:1:description',
			'resolutions:1:document_id',
			'resolutions:1:receive_date',
			'resolutions:1:subcategory',
			'resolutions:1:type',					
		];
	}

	public function getCSVData($number): ?array
	{
		return $this->getDataByNumber($number);
	}

	protected function getApiUrl(): string
	{
		return '/company/{company_number}/filing-history';
	}
	
	protected function processData($items, $number): ?array
	{
		$res = [];

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
}
