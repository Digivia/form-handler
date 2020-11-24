<?php
/**
 * @author Eric BATARSON <eric.batarson@digivia.fr>
 */

declare(strict_types=1);

namespace Digivia\FormHandler;

use Digivia\FormHandler\DependencyInjection\CompilerPass\FormHandlerCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class DigiviaFormHandlerBundle
 * @package Digivia\FormHandler
 * @codeCoverageIgnore
 */
class DigiviaFormHandlerBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new FormHandlerCompilerPass());
    }
}
