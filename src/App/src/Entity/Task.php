<?php
namespace App\Entity;

use App\Service\TaskService;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="tasks", uniqueConstraints={@ORM\UniqueConstraint(name="client_task_id_idx", columns={"client_task_id"})})
 */
class Task extends SampleEntity
{
    const CLASS_NAME_RU = 'Задание';

    /**
     * Client started task
     *
     * @var Client
     * @ORM\ManyToOne(targetEntity="App\Entity\Client", inversedBy="tasks")
     * @ORM\JoinColumn(referencedColumnName="id")
     */
    protected $client;

    /**
     * @var string
     * @ORM\Column(type="string", length=32)
     */
    protected $client_task_id;

    /**
     * Task template
     *
     * @var Template
     * @ORM\ManyToOne(targetEntity="App\Entity\Template", inversedBy="tasks")
     * @ORM\JoinColumn(referencedColumnName="id")
     */
    protected $template;

    /**
     * Task status
     *
     * @var string
     * @ORM\Column(type="string", length=32, nullable=false, options={"default" = "new"})
     */
    protected $status = TaskService::TASK_STATUS_NEW;

    /**
     * Params from client to edit template
     *
     * @var array
     * @ORM\Column(type="json_array", nullable=false)
     */
    protected $params = [];

    /**
     * List of result video links
     *
     * @var array
     * @ORM\Column(type="json_array", nullable=false)
     */
    protected $links = [];

    /**
     * Task start time
     *
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=false)
     */
    protected $time_created;

    /**
     * Task temp folder path
     *
     * @var string|null
     * @ORM\Column(type="string", length=256, nullable=true)
     */
    protected $path;

    /**
     * Callback url
     *
     * @var string|null
     * @ORM\Column(type="string", length=256, nullable=true)
     */
    protected $callback_url;

    /**
     * @var string
     * @ORM\Column(type="string", length=32)
     */
    protected $last_render_id;

    public function __construct(Client $client, string $client_task_id, Template $template) {
        $this->setClient($client);
        $this->setClientTaskId($client_task_id);
        $this->setTemplate($template);
        $this->setTimeCreated(new \DateTime());
    }

    /**
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * @param Client $client
     * @return Task
     * @throws \InvalidArgumentException
     */
    public function setClient(Client $client): Task
    {
        if ($client === $this->client) {
            return $this;
        }
        if ($client === null) {
            if($this->client !== null) {
                $this->client->removeFromTasks($this);
            }
            $this->client = null;
        } else {
            if (!$client instanceof Client) {
                throw new \InvalidArgumentException('$client must be null or instance of App\Entity\Client');
            }
            if ($this->client !== null) {
                $this->client->removeFromTasks($this);
            }
            $this->client = $client;
            $client->addToTasks($this);
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getClientTaskId(): string
    {
        return $this->client_task_id;
    }

    /**
     * @param string $client_task_id
     * @return Task
     */
    public function setClientTaskId(string $client_task_id): Task
    {
        $this->client_task_id = $client_task_id;
        return $this;
    }

    /**
     * @return Template
     */
    public function getTemplate(): Template
    {
        return $this->template;
    }

    /**
     * @param Template $template
     * @return Task
     * @throws \InvalidArgumentException
     */
    public function setTemplate(Template $template): Task
    {
        if ($template === $this->template) {
            return $this;
        }
        if ($template === null) {
            if($this->template !== null) {
                $this->template->removeFromTasks($this);
            }
            $this->template = null;
        } else {
            if (!$template instanceof Template) {
                throw new \InvalidArgumentException('$template must be null or instance of App\Entity\Template');
            }
            if ($this->template !== null) {
                $this->template->removeFromTasks($this);
            }
            $this->template = $template;
            $template->addToTasks($this);
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return Task
     */
    public function setStatus(string $status): Task
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @param array $params
     * @return Task
     */
    public function setParams(array $params): Task
    {
        $this->params = (array)$params;
        return $this;
    }

    /**
     * @return array
     */
    public function getLinks(): array
    {
        return $this->links;
    }

    /**
     * @param array $links
     * @return Task
     */
    public function setLinks(array $links): Task
    {
        $this->links = (array)$links;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getTimeCreated(): \DateTime
    {
        return $this->time_created;
    }

    /**
     * @param \DateTime|string $time_created
     * @return Task
     */
    public function setTimeCreated($time_created = 'now'): Task
    {
        $this->time_created = $time_created instanceof \DateTime ? $time_created : new \DateTime($time_created);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * @param string|null $path
     * @return Task
     */
    public function setPath(?string $path): Task
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCallbackUrl(): ?string
    {
        return $this->callback_url;
    }

    /**
     * @param string|null $callback_url
     * @return Task
     */
    public function setCallbackUrl(?string $callback_url): Task
    {
        $this->callback_url = $callback_url;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastRenderId(): string
    {
        return $this->last_render_id;
    }

    /**
     * @param string $last_render_id
     * @return Task
     */
    public function setLastRenderId(string $last_render_id): Task
    {
        $this->last_render_id = $last_render_id;
        return $this;
    }
}