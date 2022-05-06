<?php
/**
 * @author Eric BATARSON <eric.batarson@digivia.fr>
 */

namespace Digivia\FormHandler\Contract\Handler;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Interface HandlerInterface
 * @package Digivia\FormHandler\Handler
 */
interface HandlerInterface
{
    /**
     * Process form after submit and valid
     * @param $data
     * @param array $options
     */
    public function process($data, array $options): void;

    /**
     * Creates the form from Form Factory
     * @param Request|null $request
     * @param null $data
     * @param array $options
     * @return $this
     */
    public function createForm(Request $request = null, $data = null, array $options = []): self;

    /**
     * Handle the form and return response from given callable
     * @param Request $request
     * @param callable $onSuccess
     * @param callable $render
     * @return Response
     */
    public function handle(Request $request, callable $onSuccess, callable $render): Response;

    /**
     * Set initial Form Type data. ie : entity to work with
     * @param $data
     * @return HandlerInterface
     */
    public function setData($data): self;

    /**
     * Helper : create form view
     * @return FormView
     */
    public function createView(): FormView;

    /**
     * Getter for the form
     * @return FormInterface|null
     */
    public function getForm(): ?FormInterface;

    /**
     * Add options to Form Type
     * @param array $formOptions
     * @return HandlerInterface
     */
    public function setFormOptions(array $formOptions): self;

    /**
     * Add extra parameters for process method
     * @param array $extraParams
     * @return HandlerInterface
     */
    public function setExtraParams(array $extraParams): self;


    /**
     * Symfony Form Factory service
     * @param FormFactoryInterface $formFactory
     */
    public function setFormFactory(FormFactoryInterface $formFactory): void;

    /**
     * Symfony Event Dispatcher service
     * @param EventDispatcher $eventDispatcher
     */
    public function setEventDispatcher(EventDispatcher $eventDispatcher): void;
}
