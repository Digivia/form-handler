<?php
/**
 * @author Eric BATARSON <eric.batarson@digivia.fr>
 */

namespace Digivia\FormHandler\Tests\HandlerFactory\TestSet\Model;

/**
 * Class TestEntity
 * @package Digivia\Tests\HandlerFactory\TestSet\Model
 */
class TestEntity
{
    /**
     * @var string
     */
    private $name = "";

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }
}
