<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="queues_tasks")
 * @ORM\Entity(repositoryClass="App\Repository\QueuesTaskRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class QueuesTask
{
	const STATUS_NOTSTART = 'not_started';
	const STATUS_INPROGRESS = 'in_process';
	const STATUS_SUCCESS = 'success';
	const STATUS_ERROR = 'error';

	const CODE_COMPANIESHOUSE = 'update_companieshouse';
	const CODE_FILING = 'update_filing';
	const CODE_DOWNLOADCOMPANY = 'download_company';

	
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="bigint")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updated_at;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $finished_at;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $params;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $status;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?\DateTimeInterface $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getFinishedAt(): ?\DateTimeInterface
    {
        return $this->finished_at;
    }

    public function setFinishedAt(?\DateTimeInterface $finished_at): self
    {
        $this->finished_at = $finished_at;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getParams(): ?array
    {
        return unserialize($this->params);
    }

    public function setParams(?array $params): self
    {
        $this->params = serialize($params);

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
	
	/** @ORM\PrePersist */
	public function doStuffOnPrePersist()
    {
		$this->setCreatedAt(new \DateTime('now'));
    }	
}
