<?php

namespace App\Model\Companieshouse;

class CompanieshouseFactory
{
	public function getComp($acc, $type): CompanieshouseInterface
	{
		if ($type == 'filing')
			return new Filing($acc);
		elseif ($type == 'office')
			return new Office($acc);
	}
}
