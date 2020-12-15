<?php
/**
 * @author Eric BATARSON <eric.batarson@digivia.fr>
 */

namespace Digivia\FormHandler\Tests\HandlerFactory;

use Digivia\FormHandler\Event\FormHandlerEvent;
use Digivia\FormHandler\Event\FormHandlerEvents;
use Digivia\FormHandler\Exception\FormNotDefinedException;
use Digivia\FormHandler\Exception\FormTypeNotFoundException;
use Digivia\FormHandler\Handler\AbstractHandler;
use Digivia\FormHandler\Handler\HandlerInterface;
use Digivia\FormHandler\Tests\HandlerFactory\TestSet\Form\FormTestType;
use Digivia\FormHandler\Tests\HandlerFactory\TestSet\Model\TestEntity;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validation;

/**
 * Class FormHandlerFactoryTest
 * @package Digivia\Tests
 */
class FormHandlerTest extends TestCase
{
    private $handler;
    /**
     * @var EventDispatcher
     */
    private EventDispatcher $eventDispatcher;

    public function setUp(): void
    {
        parent::setUp();
        $this->handler = $this->getMockForAbstractClass(AbstractHandler::class);
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $this->handler = null;
    }

    public function testRegisterFormHandler()
    {
        $handler = $this->createMock(AbstractHandler::class);
        $this->assertInstanceOf(HandlerInterface::class, $handler);
    }

    public function testFormClassNameFailClassNotExists()
    {
        $this->handler
            ->method('provideFormTypeClassName')
            ->willReturn('BadFormType');
        $this->expectException(FormTypeNotFoundException::class);
        $this->handler->getFormClassName();
    }

    public function testFormClassNameFailNonFormType()
    {
        $mockedFormClass = get_class($this->createMock(stdClass::class));
        $this->handler
            ->method('provideFormTypeClassName')
            ->willReturn($mockedFormClass);
        $this->assertEquals($mockedFormClass, $this->handler->getFormClassName());
    }

    public function testFormClassNameCheckSuccess()
    {
        $mockedFormClass = get_class($this->createMock(AbstractType::class));
        $this->handler
            ->method('provideFormTypeClassName')
            ->willReturn($mockedFormClass);
        $this->assertEquals($mockedFormClass, $this->handler->getFormClassName());
    }

    public function testCreateForm()
    {
        $this->createForm();
        $this->assertInstanceOf(FormInterface::class, $this->handler->getForm());
    }

    public function testHandleForm()
    {
        $this->createForm();

        $request = Request::create('/', Request::METHOD_POST, [
            'form_test' => ["name" => 'value']
        ]);

        $this->eventDispatcher->addListener(FormHandlerEvents::EVENT_FORM_PROCESS, function (FormHandlerEvent $event) {
            $this->assertInstanceOf(Request::class, $event->getRequest());
            $this->assertInstanceOf(Form::class, $event->getForm());
        });
        $this->assertTrue(true, $this->handler->getForm()->isSubmitted());
        $this->assertEquals(true, $this->handler->handle($request));
    }

    public function testHandleFormFail()
    {
        $this->createForm();

        // Send a name with length < 3 chars (validation should fail)
        $request = Request::create('/', Request::METHOD_POST, [
            'form_test' => ["name" => 'ab']
        ]);

        $this->assertTrue(true, $this->handler->getForm()->isSubmitted());
        $this->assertEquals(false, $this->handler->handle($request));
    }

    public function testFormView()
    {
        $this->expectException(FormNotDefinedException::class);
        $this->handler->createView();

        $this->createForm();
        $this->assertInstanceOf(FormView::class, $this->handler->createView());
    }

    private function createForm()
    {
        // Create a form factory that supports HttFoundation Request
        $factory = Forms::createFormFactoryBuilder()
                        ->addExtensions(
                            [
                                new HttpFoundationExtension,
                                new ValidatorExtension(Validation::createValidator())
                            ]
                        )
                        ->getFormFactory();

        $this->handler->setFormFactory($factory);

        $this->eventDispatcher = new EventDispatcher();
        $this->handler->setEventDispatcher($this->eventDispatcher);
        $this->handler
            ->method('provideFormTypeClassName')
            ->willReturn(FormTestType::class);
        $this->handler->createForm(new TestEntity());
    }
}
