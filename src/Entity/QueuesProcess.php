<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="queues_processes")
 * @ORM\Entity(repositoryClass="App\Repository\QueuesProcessRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class QueuesProcess
{
	const STATUS_NOTSTART = 'not_started';
	const STATUS_INPROGRESS = 'in_process';
	const STATUS_SUCCESS = 'success';
	const STATUS_ERROR = 'error';	
	
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
     * @ORM\ManyToOne(targetEntity="App\Entity\QueuesTask")
     * @ORM\JoinColumn(name="queue_task_id", referencedColumnName="id", nullable=false)
     */
    private $task;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $started_at;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $finished_at;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $message;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $status;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $pid;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $params;

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

    public function getTask(): ?QueuesTask
    {
        return $this->task;
    }

    public function setTask(?QueuesTask $task): self
    {
        $this->task = $task;

        return $this;
    }

    public function getStartedAt(): ?\DateTimeInterface
    {
        return $this->started_at;
    }

    public function setStartedAt(?\DateTimeInterface $started_at): self
    {
        $this->started_at = $started_at;

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

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): self
    {
        $this->message = $message;

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

    public function getPid(): ?int
    {
        return $this->pid;
    }

    public function setPid(?int $pid): self
    {
        $this->pid = $pid;

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
}
