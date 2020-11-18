<?php
/**
 * @author Eric BATARSON <eric.batarson@digivia.fr>
 */

namespace Digivia\FormHandler\Handler;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Form\FormFactoryInterface;
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
    public function handle(Request $request, $data = null, array $options = []): bool;
    public function createView(): FormView;
}
