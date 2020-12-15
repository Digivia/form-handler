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

/**
 * Interface HandlerInterface
 * @package Digivia\FormHandler\Handler
 */
interface HandlerInterface
{
    public function setFormFactory(FormFactoryInterface $formFactory): void;
    public function setEventDispatcher(EventDispatcher $eventDispatcher): void;
    public function createForm(Request $request, $data = null, array $options = []): self;
    public function handle(Request $request, $data = null, array $formOptions = [], array $extraParams = []): bool;
    public function createView(): FormView;
    public function getFormClassName(): string;
    public function getForm(): ?FormInterface;
}
