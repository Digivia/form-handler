<?php
/**
 * @author Eric BATARSON <eric.batarson@digivia.fr>
 */

namespace Digivia\FormHandler\Tests\HandlerFactory;

use Digivia\FormHandler\Contract\Handler\HandlerInterface;
use Digivia\FormHandler\Exception\FormNotDefinedException;
use Digivia\FormHandler\Exception\FormTypeNotFoundException;
use Digivia\FormHandler\Exception\HandlerNotFoundException;
use Digivia\FormHandler\Handler\AbstractHandler;
use Digivia\FormHandler\HandlerFactory\HandlerFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\Form\FormInterface;

/**
 * Class FormHandlerFactoryTest
 * @package Digivia\Tests
 */
class FormHandlerFactoryTest extends TestCase
{
    public function testRegisterFormHandlerSuccess()
    {
        $handler = $this->createMock(AbstractHandler::class);
        $this->assertInstanceOf(HandlerInterface::class, $handler);

        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')
            ->willReturn($handler);
        $container->method('has')
            ->willReturn(true);
        $this->assertInstanceOf(ContainerInterface::class, $container);

        $factory = new HandlerFactory($container);
        $this->assertInstanceOf(HandlerInterface::class, $factory->createHandler(get_class($handler)));
    }

    public function testRegisterFormHandlerFailure()
    {
        $container = $this->createMock(ContainerInterface::class);
        $this->expectException(HandlerNotFoundException::class);
        $factory = new HandlerFactory($container);
        $factory->createHandler('stuff');
    }

    public function testCreateFormWithNotExistsFormClassName()
    {
        $container = $this->createMock(ContainerInterface::class);
        $factory = new HandlerFactory($container);
        $this->expectException(FormTypeNotFoundException::class);
        $factory->createFormWithHandler('stuff');
    }
    public function testCreateFormWithNotImplementsHandlerFormClassName()
    {
        $container = $this->createMock(ContainerInterface::class);
        $factory = new HandlerFactory($container);
        $this->expectException(FormNotDefinedException::class);
        $factory->createFormWithHandler(FormInterface::class);
    }
}
