<?php
/**
 * @author Eric BATARSON <eric.batarson@digivia.fr>
 */

declare(strict_types=1);

namespace Digivia\FormHandler\DependencyInjection;

use Digivia\FormHandler\Contract\Handler\HandlerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Class DigiviaFormHandlerExtension
 * @package Digivia\FormHandler\DependencyInjection
 */
class DigiviaFormHandlerExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        // Register tag Digivia handler for all class implements HandlerInterface
        $container
            ->registerForAutoconfiguration(HandlerInterface::class)
            ->addTag(FormHandlerServiceTag::FORM_HANDLER_SERVICE_TAG)
        ;
    }
}
