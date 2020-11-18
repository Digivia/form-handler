<?php
/**
 * This file is part of the MyLocative Project - 2020
 * @copyright MyLocative.fr
 * @author Eric BATARSON <eric.batarson@digivia.fr>
 */

namespace Digivia\FormHandler\HandlerFactory;

use Digivia\FormHandler\DependencyInjection\FormHandlerServiceTag;
use Digivia\FormHandler\Exception\HandlerNotFoundException;
use Digivia\FormHandler\Handler\HandlerInterface;
use Psr\Container\ContainerInterface;

/**
 * Class HandlerFactory
 * @package Digivia\FormHandler\HandlerFactory
 */
final class HandlerFactory implements HandlerFactoryInterface
{
    /**
     * @var ContainerInterface
     */
    private ContainerInterface $container;

    /**
     * HandlerFactory constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Create form handler from his name
     * @param string $handler
     * @return HandlerInterface
     * @throws HandlerNotFoundException
     */
    public function createHandler(string $handler): HandlerInterface
    {
        if (!$this->container->has($handler)) {
            throw new HandlerNotFoundException(sprintf(
                    "Handler %s does not exists. Have you create it and tagged with %s in your services.yaml ?",
                    $handler,
                    FormHandlerServiceTag::FORM_HANDLER_SERVICE_TAG
                ));
        }
        return $this->container->get($handler);
    }
}
