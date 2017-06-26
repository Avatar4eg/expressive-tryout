<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/** @ORM\MappedSuperclass */
abstract class SampleEntity
{
    const CLASS_NAME_RU = 'Базовый класс';

    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer", length=10, options={"unsigned" = true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return SampleEntity
     */
    public function setId(int $id): SampleEntity
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get(string $name)
    {
        $method = self::methodName($name, 'get');
        if (method_exists($this, $method)) {
            return $this->$method();
        }
        $method = self::methodName($name, 'is');
        if (method_exists($this, $method)) {
            return $this->$method();
        }
        return $this->{$name};
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function __set(string $name, $value)
    {
        $method = self::methodName($name, 'set');
        if (method_exists($this, $method)) {
            return $this->$method($value);
        }
        $this->{$name} = $value;
        return $this;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function __isset(string $name): bool
    {
        $method = self::methodName($name, 'get');
        if (method_exists($this, $method)) {
            $has_method = true;
        } else {
            $method = self::methodName($name, 'is');
            $has_method = method_exists($this, $method);
        }
        return ($has_method && $this->$method() !== null);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        switch (true) {
            case property_exists($this, 'title'):
                return (string)$this->{'title'};
            case $this->getId() !== null:
                return self::CLASS_NAME_RU . ' №' . $this->getId();
            default:
                return 'Новый объект типа "' . self::CLASS_NAME_RU . '"';
        }
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    /**
     * @param $property_name
     * @param string $pre
     * @return string
     */
    protected static function methodName($property_name, $pre = ''): string
    {
        $words = explode('_', $property_name);
        $name = $pre;
        foreach ($words as $word) {
            $name .= ucfirst($word);
        }
        return $name;
    }
}
