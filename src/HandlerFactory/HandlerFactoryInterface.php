<?php
/**
 * @author Eric BATARSON <eric.batarson@digivia.fr>
 */

declare(strict_types=1);

namespace Digivia\FormHandler\HandlerFactory;

use Digivia\FormHandler\Handler\HandlerInterface;

/**
 * Interface HandlerFactoryInterface
 * @package Digivia\FormHandler\HandlerFactory
 */
interface HandlerFactoryInterface
{
    public function createHandler(string $handler): HandlerInterface;
}
