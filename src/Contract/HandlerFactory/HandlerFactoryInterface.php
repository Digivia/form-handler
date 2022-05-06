<?php
/**
 * @author Eric BATARSON <eric.batarson@digivia.fr>
 */

declare(strict_types=1);

namespace Digivia\FormHandler\Contract\HandlerFactory;

use Digivia\FormHandler\Contract\Handler\HandlerInterface;

/**
 * Interface HandlerFactoryInterface
 * @package Digivia\FormHandler\HandlerFactory
 */
interface HandlerFactoryInterface
{
    public function createFormWithHandler(string $formClassName, mixed $data = null, array $options = []): HandlerInterface;
    public function createHandler(string $handler): HandlerInterface;
}
