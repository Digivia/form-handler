<?php
/**
 * @author Eric BATARSON <eric.batarson@digivia.fr>
 */

declare(strict_types=1);

namespace Digivia\FormHandler\Handler;

use Digivia\FormHandler\Event\FormHandlerEvent;
use Digivia\FormHandler\Event\FormHandlerEvents;
use Digivia\FormHandler\Exception\FormNotDefinedException;
use Digivia\FormHandler\Exception\FormTypeNotFoundException;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Form\AbstractType;
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
//    protected static string $formClassName;

    private FormFactoryInterface $formFactory;
    private ?FormInterface $form = null;
    private EventDispatcher $eventDispatcher;

    /**
     * Process a treatment if form is valid
    * @param mixed|null $data
    * @param array $options
    */
    abstract protected function process($data, array $options): void;

    /**
     * Provide a correct Symfony FormType in Handler
     * @return string
     */
    abstract protected function provideFormTypeClassName(): string;

    public function setFormFactory(FormFactoryInterface $formFactory): void
    {
        $this->formFactory = $formFactory;
    }

    public function setEventDispatcher(EventDispatcher $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @return string
     * @throws FormTypeNotFoundException
     */
    public function getFormClassName(): string
    {
        $formType = $this->provideFormTypeClassName();
        try {
            $r = new ReflectionClass($formType);
        } catch (ReflectionException $e) {
            throw new FormTypeNotFoundException(
                sprintf(
                    "Non existing Form Type defined : « %s ». 
                    Have you provide correct value in « provideFormTypeClassName » method of your handler ?",
                    $formType
                )
            );
        }
        if (!$r->getParentClass()->name === AbstractType::class) {
            throw new FormTypeNotFoundException(
                sprintf(
                    "Symfony form type does not exists. Have your extends %s from AbstractType ?",
                    $formType
                )
            );
        }

        return $formType;
    }

    /**
     * @param $data
     * @param array $options
     * @throws FormTypeNotFoundException
     */
    public function createForm($data, array $options = [])
    {
        // Create form and handle request
        $this->setForm(
            $this->formFactory->create(
                $this->getFormClassName(),
                $data,
                $options
            )
        );
    }

    /**
     * @param FormInterface $form
     */
    public function setForm(FormInterface $form)
    {
        $this->form = $form;
    }

    /**
     * @return FormInterface
     */
    public function getForm(): ?FormInterface
    {
        return $this->form;
    }

    /**
     * @param Request|null $request
     * @param mixed $data The initial data
     * @param array $options Symfony Form options
     * @return bool
     * @throws FormTypeNotFoundException
     */
    public function handle(Request $request = null, $data = null, array $options = []): bool
    {
        // Create form and handle request
        $this->createForm($data, $options);
        $this->setForm(
            $this->getForm()->handleRequest($request)
        );

        // Create specific form event
        $formEvent = new FormHandlerEvent($this->getForm(), $request);
        if ($this->getForm()->isSubmitted() && $this->getForm()->isValid()) {
            // Get Form data
            $data = $this->getForm()->getData();
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

    /**
     * @return FormView
     * @throws FormNotDefinedException
     */
    public function createView(): FormView
    {
        if (null === $this->form) {
            throw new FormNotDefinedException("You have to init form with « createForm » method");
        }
        return $this->form->createView();
    }
}
