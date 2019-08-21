<?php

namespace App\Model\Companieshouse;

class CompanieshouseFactory
{
	public function getComp($apikey, $type): CompanieshouseInterface
	{
		if ($type == 'filing')
			return new Filing($apikey);
		elseif ($type == 'office')
			return new Office($apikey);
	}
}
