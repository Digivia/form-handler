<?php
/**
 * @author Eric BATARSON <eric.batarson@digivia.fr>
 */

declare(strict_types=1);

namespace Digivia\FormHandler\Event;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class FormHandlerEvent
 * @package Digivia\FormHandler\Event
 */
final class FormHandlerEvent extends Event
{
    private ?Request       $request;
    private FormInterface $form;
    /** @var mixed */
    private $data;

    /**
     * FormHandlerEvent constructor.
     * @param Request|null $request
     * @param FormInterface $form
     */
    public function __construct(FormInterface $form, Request $request = null)
    {
        $this->request = $request;
        $this->form    = $form;
        $this->data    = $form->getData();
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }

    public function getForm(): FormInterface
    {
        return $this->form;
    }

    public function setForm(FormInterface $form): void
    {
        $this->form = $form;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData($data): void
    {
        $this->data = $data;
    }
}
