<?php
/**
 * @author Eric BATARSON <eric.batarson@digivia.fr>
 */

declare(strict_types=1);

namespace Digivia\FormHandler\Event;

/**
 * Class FormHandlerEvents
 * @package Digivia\FormHandler\Event
 */
class FormHandlerEvents
{
    public const EVENT_FORM_SUCCESS = 'digivia.form_handler.success';
    public const EVENT_FORM_FAIL    = 'digivia.form_handler.fail';
    public const EVENT_FORM_PROCESS = 'digivia.form_handler.process';
}
