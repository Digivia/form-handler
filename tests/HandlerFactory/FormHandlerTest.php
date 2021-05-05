<?php
/**
 * @author Eric BATARSON <eric.batarson@digivia.fr>
 */

namespace Digivia\FormHandler\Tests\HandlerFactory;

use Digivia\FormHandler\Exception\CallbackMustReturnHttpResponseException;
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
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validation;

/**
 * Class FormHandlerFactoryTest
 * @package Digivia\Tests
 */
class FormHandlerTest extends TestCase
{
    private $handler;

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
        $request = Request::create('/', Request::METHOD_POST, [
            'form_test' => ["name" => 'value']
        ]);
        $this->createForm($request);

        $this->assertTrue(true, $this->handler->getForm()->isSubmitted());
        $this->assertInstanceOf(
            Response::class,
            $this->handler->handle(
                $request,
                function () {
                    return new Response();
                },
                function () {}
            )
        );
    }

    public function testHandleFormWithNoResponseCallbackOnSuccess()
    {
        $request = Request::create('/', Request::METHOD_POST, [
            'form_test' => ["name" => 'value']
        ]);
        $this->createForm($request);

        $this->expectException(CallbackMustReturnHttpResponseException::class);
        $this->handler->handle(
            $request,
            function () {
                return null;
            },
            function () {
                return new Response();
            }
        );
    }

    public function testHandleFormFail()
    {
        // Send a name with length < 3 chars (validation should fail)
        $request = Request::create('/', Request::METHOD_POST, [
            'form_test' => ["name" => 'ab']
        ]);
        $this->createForm($request);

        $this->assertTrue(true, $this->handler->getForm()->isSubmitted());
        $this->assertInstanceOf(
            Response::class,
            $this->handler->handle(
                $request,
                function () {
                },
                function () {
                    return new Response();
                }
            )
        );
    }

    public function testHandleFormWithNoResponseCallbackRender()
    {
        // Send a name with length < 3 chars (validation should fail)
        $request = Request::create('/', Request::METHOD_POST, [
            'form_test' => ["name" => 'ab']
        ]);
        $this->createForm($request);

        $this->assertTrue(true, $this->handler->getForm()->isSubmitted());
        $this->expectException(CallbackMustReturnHttpResponseException::class);
        $this->handler->handle($request, function () {}, function () {});
    }

    public function testFormView()
    {
        $this->expectException(FormNotDefinedException::class);
        $this->handler->createView();

        $this->createForm();
        $this->assertInstanceOf(FormView::class, $this->handler->createView());
    }

    private function createForm(Request $request = null)
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
        $this->handler->setEventDispatcher(new EventDispatcher());
        $this->handler
            ->method('provideFormTypeClassName')
            ->willReturn(FormTestType::class);
        $request = $request ?? Request::createFromGlobals();
        $this->handler->createForm($request, new TestEntity());
    }
}
