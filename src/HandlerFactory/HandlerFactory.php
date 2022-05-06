<?php
/**
 * @author Eric BATARSON <eric.batarson@digivia.fr>
 */

declare(strict_types=1);

namespace Digivia\FormHandler\HandlerFactory;

use Digivia\FormHandler\Contract\Form\FormWithHandlerInterface;
use Digivia\FormHandler\Contract\Handler\HandlerInterface;
use Digivia\FormHandler\Contract\HandlerFactory\HandlerFactoryInterface;
use Digivia\FormHandler\DependencyInjection\FormHandlerServiceTag;
use Digivia\FormHandler\Exception\FormNotDefinedException;
use Digivia\FormHandler\Exception\FormTypeNotFoundException;
use Digivia\FormHandler\Exception\HandlerNotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;

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
     * @param string $formClassName
     * @param mixed|null $data
     * @param array $options
     * @return HandlerInterface
     * @throws FormNotDefinedException
     * @throws FormTypeNotFoundException
     * @throws HandlerNotFoundException
     */
    public function createFormWithHandler(
        string $formClassName,
        mixed $data = null,
        array $options = []
    ): HandlerInterface
    {
        // Form Type class must implement correct interface
        $this->checkFormInterfaceImplementation($formClassName);

        /** @var FormWithHandlerInterface $formClassName */
        // Call static function getHandlerClassName() declared in FormWithHandlerInterface, checked above
        // TypeHint in comment is for help IDE
        $handlerClass = $formClassName::getHandlerClassName();

        if (!$this->container->has($handlerClass)) {
            throw new FormTypeNotFoundException(sprintf("Form Type %s does not exists.", $formClassName));
        }
        /** @var HandlerInterface $handler */
        try {
            $handler = $this->container->get($handlerClass);
            $this->checkHandlerInterfaceImplementation(get_class($handler));
            /** @var string $formClassName */
            // $formClassName is really a string
            $handler->setFormFCQN($formClassName);
            return $handler;
        } catch (NotFoundExceptionInterface | ContainerExceptionInterface) {
            throw new HandlerNotFoundException(
                sprintf(
                    "Form Handler %s was not found in container. Is this class exists ?",
                    $handlerClass
                )
            );
        }
    }

    /**
     * Create form handler from his name
     * @param string $handler
     * @return HandlerInterface
     * @throws ContainerExceptionInterface
     * @throws HandlerNotFoundException
     * @throws NotFoundExceptionInterface
     */
    public function createHandler(string $handler): HandlerInterface
    {
        if (!$this->container->has($handler)) {
            throw new HandlerNotFoundException(
                sprintf(
                    "Handler %s does not exists. Have you create it and tagged with %s in your services.yaml ?",
                    $handler,
                    FormHandlerServiceTag::FORM_HANDLER_SERVICE_TAG
                )
            );
        }
        return $this->container->get($handler);
    }

    /**
     * @param string $formFQCN
     * @return ReflectionClass
     * @throws FormTypeNotFoundException
     */
    private function getFormClassReflection(string $formFQCN): ReflectionClass
    {
        try {
            return new ReflectionClass($formFQCN);
        } catch (\ReflectionException $e) {
            throw new FormTypeNotFoundException(sprintf("Form Type class %s was not found.", $formFQCN));
        }
    }

    /**
     * @param string $handlerFQCN
     * @return ReflectionClass
     * @throws HandlerNotFoundException
     */
    private function getHandlerClassReflection(string $handlerFQCN): ReflectionClass
    {
        try {
            return new ReflectionClass($handlerFQCN);
        } catch (\ReflectionException $e) {
            throw new HandlerNotFoundException(sprintf("Handler class %s was not found.", $handlerFQCN));
        }
    }

    /**
     * @param string $formFCQN
     * @throws FormNotDefinedException
     * @throws FormTypeNotFoundException
     */
    private function checkFormInterfaceImplementation(string $formFCQN)
    {
        $formReflection = $this->getFormClassReflection($formFCQN);
        if (!$formReflection->implementsInterface(FormWithHandlerInterface::class)) {
            throw new FormNotDefinedException(
                sprintf(
                    "Your form class %s must implement %s to use handler.",
                    $formFCQN,
                    FormWithHandlerInterface::class
                )
            );
        }
    }

    /**
     * @param string $handlerFCQN
     * @throws HandlerNotFoundException
     */
    private function checkHandlerInterfaceImplementation(string $handlerFCQN)
    {
        $formReflection = $this->getHandlerClassReflection($handlerFCQN);
        if (!$formReflection->implementsInterface(HandlerInterface::class)) {
            throw new HandlerNotFoundException(
                sprintf(
                    "Your handler class %s must implement %s to use handler.",
                    $handlerFCQN,
                    HandlerInterface::class
                )
            );
        }
    }

}
