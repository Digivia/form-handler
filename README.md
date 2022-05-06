# Symfony form handler bundle V2

If you want to separate the logic carried out during the submission 
of a form from the processing of the request carried out in the controller, this bundle is for you :)


Thus, the controller only does its job: to receive a request and send back a response.

Requirements :
=============

Need to use php8 and Symfony 5.4 / 6

Installation
============

Applications that use Symfony Flex
----------------------------------

Open a command console, enter your project directory and execute:

```console
$ composer require digivia/form-handler
```

Applications that don't use Symfony Flex
----------------------------------------

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require digivia/form-handler
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            // ...
            new Digivia\FormHandler\DigiviaFormHandlerBundle()
        ];

        // ...
    }

    // ...
}
```
Configuration
-------------

:new:

**Thanks to Symfony autoconfigure, no need to configure tags or nothing else**

Usage
------

Create your form, it must implement **Digivia\FormHandler\Contract\Form\FormWithHandlerInterface**

Then, you can provide your handler class name in static method **getHandlerClassName**

```php
use Digivia\FormHandler\Contract\Form\FormWithHandlerInterface;
use App\FormHandler\TestFormHandler;

/**
 * Class FormTestType
 * @package Digivia\Tests\HandlerFactory\TestSet\Form
 */
class FormTestType extends AbstractType implements FormWithHandlerInterface
{
    public static function getHandlerClassName(): string
    {
        // Here add your form handler
        return TestFormHandler::class;
    }
    
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // here add your form field - see Symfony doc
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        // Form configuration - see Symfony doc
    }
}
```
Create your form handler :

```php
<?php
namespace App\FormHandler\TestFormHandler;

use App\Form\MyFormType;
use Digivia\FormHandler\Handler\AbstractHandler;

class TestFormHandler extends AbstractHandler
{
    protected function process($data, array $options): void
    {
        // your business logic in case of successful form submitting
        // ie : Doctrine persisting, messenger, mail...
    }
}
```


In your controller, call the form handler factory :

```php
public function edit(HandlerFactoryInterface $factory, Request $request, Post $post) : Response
{
    // Instanciate form handler and gives him your form type class name
    $handler = $factory->createFormWithHandler(FormTestType::class);
    // Give data to work with and options to form / handler
    $handler->setData($post); // Optionally, set entity to work with to the form
    // Return Response after treatment    
    return $handler->handle(
        $request,
        // Callable used in case of form submitted with success
        function (Post $post) use ($request) {
            return $this->redirectToRoute('post_show', ['id' => $post->getId()]);
        },
        // Callable used in case of non-submitted form, or submitted but not valid
        function (FormView $formView, $data) {
            return $this->render('conference/edit.html.twig', [
                'form' => $formView,
                'post' => $data
            ]);
        }
    );
}
```

As you can see, this Controller just get request and send a response...

You can give to your form some options and to your handler "process" method extra parameters :
```php

// Instanciate form handler
$handler = $factory->createFormWithHandler(FormTestType::class);
// Give data to work with and options to form / handler
$handler->setFormOptions(['validation_groups' => false]); // Optionally, add form type options if you need
// will be sent to $options in FormType :
FormFactory::create(string $type = 'Symfony\Component\Form\Extension\Core\Type\FormType', $data = null, array $options = [])


// Process extra parameters is fourth parameter
$handler = $factory->createFormWithHandler(FormTestType::class);
// Give data to work with and options to form / handler
$handler->setExtraParams(['form_creation' => true]); // Optionally, add form type options if you need
// will be sent to $options in this Form Handler method : 
protected function process($data, array $options): void

```

Note :
------

The two callables, $onSuccess and $render, must return a Response (Symfony\Component\HttpFoundation\Response).
**The form handler will provide status code HTTP 303 if response**
is an instance of Symfony\Component\HttpFoundation\RedirectResponse.

If form is submitted, but not valid, **the handler will provide an HTTP 422 code to the response.**

So you can use this bundle with Turbo like this - see : [https://github.com/symfony/ux/blob/0a6ebad4bc67f74ba3bbb52f6586085ddcd28ab1/src/Turbo/README.md#forms](https://github.com/symfony/ux/blob/0a6ebad4bc67f74ba3bbb52f6586085ddcd28ab1/src/Turbo/README.md#forms)

```php
public function edit(HandlerFactoryInterface $factory, Request $request, Post $post) : Response
{
    // Instanciate form handler
    $handler = $factory->createFormWithHandler(FormTestType::class);
    // Give data to work with and options to form / handler
    $handler->setData($post); // Optionally, set entity to work with to the form
    // Return Response after treatment    
    return $handler->handle(
        $request,
        // Callable used in case of form submitted with success
        function (Post $post) use ($request) {
            // 🔥 If you uses Turbo 🔥
            if (TurboStreamResponse::STREAM_FORMAT === $request->getPreferredFormat()) {
                // If the request comes from Turbo, only send the HTML to update using a TurboStreamResponse
                return $this->render(
                    'post/success.stream.html.twig',
                    ['post' => $post],
                    new TurboStreamResponse()
                );
            }
            return $this->redirectToRoute('post_show', ['id' => $post->getId()]);
        },
        // Callable used in case of non-submitted form, or submitted but not valid
        function (FormView $formView, $data) {
            return $this->render('conference/edit.html.twig', [
                'form' => $formView,
                'post' => $data
            ]);
        }
    );
}
```

Events
------

when the form is submitted, several events are dispatched:

```
FormHandlerEvents::EVENT_FORM_PROCESS
```
Event dispatched just before the call to the process method. 
It allows you to modify the data received from the form. Thanks to this event, 
you can therefore act on the data sent to the process method of your Handler.

```
FormHandlerEvents::EVENT_FORM_SUCCESS
```
Event dispatched after a successful submission of the form, 
and after the processing carried out by the "process" method.

```
FormHandlerEvents::EVENT_FORM_FAIL
```
Event dispatched after a failed submission of the form.

__________

Do not hesitate to contact me if you have any questions or ideas for development. Enjoy!

Eric BATARSON - [Digivia](http://www.digivia.fr)
