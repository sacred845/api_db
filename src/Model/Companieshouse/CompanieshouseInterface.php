<?php

namespace App\Model\Companieshouse;

interface CompanieshouseInterface
{
	public function getCSVData($number): ?array;
	
	public function getFields(): array;
}
