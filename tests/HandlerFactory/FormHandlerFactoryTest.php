<?php
/**
 * @author Eric BATARSON <eric.batarson@digivia.fr>
 */

namespace Digivia\Tests\HandlerFactory;

use Digivia\FormHandler\Handler\AbstractHandler;
use Digivia\FormHandler\Handler\HandlerInterface;
use Digivia\FormHandler\HandlerFactory\HandlerFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * Class FormHandlerFactoryTest
 * @package Digivia\Tests
 */
class FormHandlerFactoryTest extends TestCase
{
    public function testRegisterFormHandler()
    {
        $handler = $this->createMock(AbstractHandler::class);
        $this->assertInstanceOf(HandlerInterface::class, $handler);

        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')
            ->willReturn($handler);
        $container->method('has')
            ->willReturn($handler);
        $this->assertInstanceOf(ContainerInterface::class, $container);

        $factory = new HandlerFactory($container);
        $this->assertInstanceOf(HandlerInterface::class, $factory->createHandler(get_class($handler)));
    }
}
