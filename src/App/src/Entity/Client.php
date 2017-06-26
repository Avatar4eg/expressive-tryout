<?php
namespace App\Entity;

use App\Service\RoleService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Zend\Math\Rand;

/**
 * @ORM\Entity
 * @ORM\Table(name="clients", uniqueConstraints={@ORM\UniqueConstraint(name="token_idx", columns={"token"})})
 */
class Client extends SampleEntity
{
    const CLASS_NAME_RU = 'Клиент';
    const TOKEN_LENGTH = 32;

    /**
     * @var string
     * @ORM\Column(type="string", length=128)
     */
    protected $title;

    /**
     * @var string
     * @ORM\Column(type="string", length=32, unique=true)
     */
    protected $token;

    /**
     * @var Collection|Task[]
     * @ORM\OneToMany(targetEntity="App\Entity\Task", mappedBy="client")
     * @ORM\OrderBy({"time_created" = "DESC"})
     */
    protected $tasks;

    /**
     * @var string
     * @ORM\Column(type="string", length=32, options={"default" = "client"})
     */
    protected $role = RoleService::ROLE_CLIENT;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=256, nullable=true)
     */
    protected $api_url;

    /**
     * Params from client to edit template
     *
     * @var array
     * @ORM\Column(type="json_array", nullable=false)
     */
    protected $api_params = [];

    public function __construct() {
        $this->setTitle('Новый ' . self::CLASS_NAME_RU);
        $this->setToken(Rand::getString(self::TOKEN_LENGTH, '0123456789abcdefghijklmnopqrstuvwxyz'));
        $this->tasks = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return Client
     */
    public function setTitle(string $title): Client
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @param string $token
     * @return Client
     */
    public function setToken(string $token): Client
    {
        $this->token = $token;
        return $this;
    }

    /**
     * @return Task[]|Collection
     */
    public function getTasks()
    {
        return $this->tasks;
    }

    /**
     * @param Task[]|Collection $tasks
     * @return Client
     * @throws \InvalidArgumentException
     */
    public function setTasks($tasks): Client
    {
        $tasks = $tasks instanceof Collection || is_array($tasks) ? $tasks : [$tasks];
        $this->removeTasks($this->getTasks());
        $this->addTasks($tasks);
        return $this;
    }

    /**
     * @param Task[]|Collection $tasks
     * @return Client
     * @throws \InvalidArgumentException
     */
    public function addTasks($tasks): Client
    {
        foreach ($tasks as $task) {
            $this->addToTasks($task);
        }
        return $this;
    }

    /**
     * @param Task[]|Collection $tasks
     * @return Client
     * @throws \InvalidArgumentException
     */
    public function removeTasks($tasks): Client
    {
        foreach ($tasks as $task) {
            $this->removeFromTasks($task);
        }
        return $this;
    }

    /**
     * @param Task $task
     * @throws \InvalidArgumentException
     */
    public function addToTasks(Task $task): void
    {
        if ($this->getTasks()->contains($task)) {
            return;
        }
        $this->getTasks()->add($task);
        $task->setClient($this);
    }

    /**
     * @param Task $task
     * @throws \InvalidArgumentException
     */
    public function removeFromTasks(Task $task): void
    {
        if (!$this->getTasks()->contains($task)) {
            return;
        }
        $this->getTasks()->removeElement($task);
        $task->setClient(null);
    }

    /**
     * @return string
     */
    public function getRole(): string
    {
        return $this->role;
    }

    /**
     * @param string $role
     * @return Client
     */
    public function setRole(string $role): Client
    {
        $this->role = $role;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getApiUrl(): ?string
    {
        return $this->api_url;
    }

    /**
     * @param string|null $api_url
     * @return Client
     */
    public function setApiUrl(?string $api_url): Client
    {
        $this->api_url = $api_url;
        return $this;
    }

    /**
     * @return array
     */
    public function getApiParams(): array
    {
        return $this->api_params;
    }

    /**
     * @param array $api_params
     * @return Client
     */
    public function setApiParams(array $api_params): Client
    {
        $this->api_params = (array)$api_params;
        return $this;
    }
}