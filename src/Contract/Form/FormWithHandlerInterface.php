<?php
/**
 * @author Eric BATARSON <eric.batarson@digivia.fr>
 */

namespace Digivia\FormHandler\Contract\Form;

interface FormWithHandlerInterface
{
    public static function getHandlerClassName(): string;
}
