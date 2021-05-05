<?php
/**
 * @author Eric BATARSON <eric.batarson@digivia.fr>
 */

declare(strict_types=1);

namespace Digivia\FormHandler\Handler;

use Digivia\FormHandler\Event\FormHandlerEvent;
use Digivia\FormHandler\Event\FormHandlerEvents;
use Digivia\FormHandler\Exception\CallbackMustReturnHttpResponseException;
use Digivia\FormHandler\Exception\FormNotDefinedException;
use Digivia\FormHandler\Exception\FormTypeNotFoundException;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
    private array $formOptions = [];
    private array $extraParams = [];
    private $data;

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
     * Use Symfony Form component to create the form object
     * @param Request|null $request
     * @param null $data
     * @param array $options
     * @return AbstractHandler
     * @throws FormTypeNotFoundException
     */
    public function createForm(Request $request = null, $data = null, array $options = []): self
    {
        $form = $this->formFactory->create(
            $this->getFormClassName(),
            $data,
            $options
        );
        if ($request instanceof Request) {
            $form->handleRequest($request);
        }
        $this->setForm($form);
        return $this;
    }

    public function setForm(FormInterface $form)
    {
        $this->form = $form;
    }

    public function getForm(): ?FormInterface
    {
        return $this->form;
    }

    /**
     * @param Request $request
     * @param callable $onSuccess
     * @param callable $render
     * @return Response
     * @throws CallbackMustReturnHttpResponseException
     * @throws FormTypeNotFoundException
     */
    public function handle(Request $request, callable $onSuccess, callable $render): Response
    {
        // Create form and handle request
        if (null === $this->form) {
            $this->createForm($request, $this->data, $this->formOptions);
        }
        // Create specific form event
        $formEvent = new FormHandlerEvent($this->getForm(), $request);
        // Is form submitted ?
        $submitted = $this->getForm()->isSubmitted();
        if ($submitted && $this->getForm()->isValid()) {
            // Get Form data
            $this->data = $this->getForm()->getData();
            // If this event is listened, it let you update data before process
            if ($this->eventDispatcher->hasListeners(FormHandlerEvents::EVENT_FORM_PROCESS)) {
                $this->eventDispatcher->dispatch($formEvent, FormHandlerEvents::EVENT_FORM_PROCESS);
                $this->data = $formEvent->getData();
            }
            // Process (ie : push in DB, send a mail, etc)
            $this->process($this->data, $this->extraParams);
            // This event is dispatched after process to create a post process job
            $this->eventDispatcher->dispatch($formEvent, FormHandlerEvents::EVENT_FORM_SUCCESS);
            $response = $onSuccess($this->data);
            if (!$response instanceof Response) {
                throw new CallbackMustReturnHttpResponseException(
                    "'onSuccess' callback must return Http Response (instance of " . Response::class . ")"
                );
            }
            // If response is a redirect to another resource, change status code for HTTP 303 "See other"
            if ($response instanceof RedirectResponse) {
                $response->setStatusCode(Response::HTTP_SEE_OTHER);
            }
            return $response;
        }
        $response = $render($this->createView(), $this->data);
        if (!$response instanceof Response) {
            throw new CallbackMustReturnHttpResponseException(
                "'render' callback must return Http Response (instance of " . Response::class . ")"
            );
        }
        // Form was submitted, but not valid. Change Response code to HTTP 422
        if ($submitted && Response::HTTP_OK === $response->getStatusCode()) {
            $this->eventDispatcher->dispatch($formEvent, FormHandlerEvents::EVENT_FORM_FAIL);
            $response->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        return $response;
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

    public function setFormOptions(array $formOptions): self
    {
        $this->formOptions = $formOptions;
        return $this;
    }

    public function setExtraParams(array $extraParams): self
    {
        $this->extraParams = $extraParams;
        return $this;
    }

    public function setData($data): self
    {
        $this->data = $data;
        return $this;
    }
}
