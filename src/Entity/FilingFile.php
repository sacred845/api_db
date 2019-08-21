<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FilingFileRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class FilingFile
{
	const STATUS_NEW = 'new';
	const STATUS_PROCESS = 'process';
	const STATUS_DOWNLOAD = 'download';
	const STATUS_ERROR = 'error';
	
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $company_number;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $url;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $status;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCompanyNumber(): ?string
    {
        return $this->company_number;
    }

    public function setCompanyNumber(string $company_number): self
    {
        $this->company_number = $company_number;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }
	
	/** @ORM\PrePersist */
	public function doStuffOnPrePersist()
    {
        $this->setCreatedAt(new \DateTime('now'));
		$this->status = self::STATUS_NEW;
    }	
}
