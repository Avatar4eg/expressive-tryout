<?php
namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Zend\Math\Rand;

/**
 * @ORM\Entity
 * @ORM\Table(name="templates")
 */
class Template extends SampleEntity
{
    const CLASS_NAME_RU = 'Шаблон';

    /**
     * Template title
     *
     * @var string
     * @ORM\Column(type="string", length=128)
     */
    protected $title;

    /**
     * Template description
     *
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    protected $text;

    /**
     * List of tasks using this template
     *
     * @var Collection|Task[]
     * @ORM\OneToMany(targetEntity="App\Entity\Task", mappedBy="template")
     * @ORM\OrderBy({"time_created" = "DESC"})
     */
    protected $tasks;

    /**
     * List of template parts path
     *
     * @var array
     * @ORM\Column(type="json_array", nullable=false)
     */
    protected $paths = [];

    public function __construct() {
        $this->setTitle('Новый ' . self::CLASS_NAME_RU);
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
     * @return Template
     */
    public function setTitle(string $title): Template
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getText(): ?string
    {
        return $this->text;
    }

    /**
     * @param string|null $text
     * @return Template
     */
    public function setText(?string $text): Template
    {
        $this->text = $text;
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
     * @return Template
     * @throws \InvalidArgumentException
     */
    public function setTasks($tasks): Template
    {
        $tasks = $tasks instanceof Collection || is_array($tasks) ? $tasks : [$tasks];
        $this->removeTasks($this->getTasks());
        $this->addTasks($tasks);
        return $this;
    }

    /**
     * @param Task[]|Collection $tasks
     * @return Template
     * @throws \InvalidArgumentException
     */
    public function addTasks($tasks): Template
    {
        foreach ($tasks as $task) {
            $this->addToTasks($task);
        }
        return $this;
    }

    /**
     * @param Task[]|Collection $tasks
     * @return Template
     * @throws \InvalidArgumentException
     */
    public function removeTasks($tasks): Template
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
        $task->setTemplate($this);
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
        $task->setTemplate(null);
    }

    /**
     * @return array
     */
    public function getPaths(): array
    {
        return $this->paths;
    }

    /**
     * @param array $paths
     * @return Template
     */
    public function setPaths(array $paths): Template
    {
        $this->paths = (array)$paths;
        return $this;
    }
}