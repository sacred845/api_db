<?php

namespace App\Model;

trait LoggerTrait
{
	private $logger;
	
    public function log(string $priority, ?string $mes, string $name = 'logger'): self
    {
        if (!$this->logger)
            $this->logger = new Logger($name);
        $this->logger->add($priority, $mes);
        return $this;
    }	
}
