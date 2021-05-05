<?php
/**
 * @author Eric BATARSON <eric.batarson@digivia.fr>
 */

namespace Digivia\FormHandler\Handler;

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
     * Symfony Form Factory service
     * @param FormFactoryInterface $formFactory
     */
    public function setFormFactory(FormFactoryInterface $formFactory): void;

    /**
     * Symfony Event Dispatcher service
     * @param EventDispatcher $eventDispatcher
     */
    public function setEventDispatcher(EventDispatcher $eventDispatcher): void;

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
     * Helper : create form view
     * @return FormView
     */
    public function createView(): FormView;

    /**
     * Get concrete Form Type class
     * @return string
     */
    public function getFormClassName(): string;

    /**
     * Getter for the form
     * @return FormInterface|null
     */
    public function getForm(): ?FormInterface;

    /**
     * Add options to Form Type
     * @param array $formOptions
     * @return $this
     */
    public function setFormOptions(array $formOptions): self;

    /**
     * Add extra parameters for process method
     * @param array $extraParams
     * @return $this
     */
    public function setExtraParams(array $extraParams): self;

    /**
     * Set initial Form Type data. ie : entity to work with
     * @param $data
     * @return $this
     */
    public function setData($data): self;
}
