<?php
/**
 * @author Eric BATARSON <eric.batarson@digivia.fr>
 */

declare(strict_types=1);

namespace Digivia\FormHandler\DependencyInjection\CompilerPass;

use Digivia\FormHandler\DependencyInjection\FormHandlerServiceTag;
use Digivia\FormHandler\HandlerFactory\HandlerFactory;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Class FormHandlerCompilerPass
 * @package Digivia\FormHandler\DependencyInjection\CompilerPass
 */
final class FormHandlerCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        // Get Handler Factory definition from container
        $formHandlerDefinition = $container->getDefinition(HandlerFactory::class);

        $serviceMap = [];

        // Get all handler tagged with "app.handler" = created handler by developer
        $taggedServices = $container->findTaggedServiceIds(FormHandlerServiceTag::FORM_HANDLER_SERVICE_TAG, true);

        $formFactoryDefinition = $container->getDefinition('form.factory');
        $eventDispatcherDefinition = $container->getDefinition('event_dispatcher');

        // Create a map for each tagged handler
        foreach (array_keys($taggedServices) as $serviceId) {
            $container
                ->getDefinition($serviceId)
                ->addMethodCall('setFormFactory', [$formFactoryDefinition])
                ->addMethodCall('setEventDispatcher', [$eventDispatcherDefinition]);
            $serviceMap[$container->getDefinition($serviceId)->getClass()] = new Reference($serviceId);
        }

        // Add to Handler Factory constructor all handler defined - @see Digivia\FormHandler\HandlerFactory\HandlerFactory::__construct
        $formHandlerDefinition->setArgument(0, ServiceLocatorTagPass::register($container, $serviceMap));
    }
}
