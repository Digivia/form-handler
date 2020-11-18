<?php
/**
 * @author Eric BATARSON <eric.batarson@digivia.fr>
 */

declare(strict_types=1);

namespace Digivia\FormHandler\Handler;

use Digivia\FormHandler\Event\FormHandlerEvent;
use Digivia\FormHandler\Event\FormHandlerEvents;
use Digivia\FormHandler\Exception\FormTypeNotFoundException;
use ReflectionException;
use ReflectionProperty;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AbstractHandler
 * @package Digivia\FormHandler\Handler
 */
abstract class AbstractHandler implements HandlerInterface
{
    protected static string $formClassName;

    private FormFactoryInterface $formFactory;
    private FormInterface $form;
    private EventDispatcher $eventDispatcher;

    /**
    * @param mixed|null $data
    * @param array $options
    */
    abstract protected function process($data, array $options): void;

    public function setFormFactory(FormFactoryInterface $formFactory): void
    {
        $this->formFactory = $formFactory;
    }

    public function setEventDispatcher(EventDispatcher $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param Request $request
     * @param mixed $data The initial data
     * @param array $options Symfony Form options
     * @return bool
     * @throws FormTypeNotFoundException
     */
    public function handle(Request $request, $data = null, array $options = []): bool
    {
        // Create form and handle request
        $this->form = $this->formFactory->create(
            static::getFormClassName(),
            $data,
            $options
        )->handleRequest($request);

        // Create specific form event
        $formEvent = new FormHandlerEvent($request, $this->form);
        if ($this->form->isSubmitted() && $this->form->isValid()) {
            // Get Form data
            $data = $this->form->getData();
            // If this event is listened, it let you update data before process
            if ($this->eventDispatcher->hasListeners(FormHandlerEvents::EVENT_FORM_PROCESS)) {
                $this->eventDispatcher->dispatch($formEvent, FormHandlerEvents::EVENT_FORM_PROCESS);
                $data = $formEvent->getData();
            }
            // Process (ie : push in DB, send a mail, etc
            $this->process($data, $options);
            // This event is dispatched after process to create a post process job
            $this->eventDispatcher->dispatch($formEvent, FormHandlerEvents::EVENT_FORM_SUCCESS);
            return true;
        }
        // This event is dispatched if form was not valid...
        $this->eventDispatcher->dispatch($formEvent, FormHandlerEvents::EVENT_FORM_FAIL);
        return false;
    }

    public function createView(): FormView
    {
        return $this->form->createView();
    }

    /**
     * @return string
     * @throws FormTypeNotFoundException
     */
    private static function getFormClassName(): string
    {
        try {
            $class = static::class;
            $r = new ReflectionProperty($class, 'formClassName');
        } catch (ReflectionException $e) {
            throw new FormTypeNotFoundException(sprintf("No form type defined. Have you set value on your handler %s::%s ?", $class, '$formClassName'));
        }

        $formName = $class === $r->class ? static::$formClassName : null;

        if (null === $formName) {
            throw new FormTypeNotFoundException("No form type defined. Have you provide it in the setForm method of your handler ?");
        }
        if (!class_exists($formName)) {
            throw new FormTypeNotFoundException(sprintf("Symfony form type '%s' does not exists", $formName));
        }
        return $formName;
    }
}
