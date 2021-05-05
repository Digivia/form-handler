# Symfony form handler bundle

In the official Symfony documentation, you can see that a form can be managed in Controller, 
but I think that's not the best practice.
The management form should be delegated to a dedicated manager, and the controller should be as small 
as possible and play only his role : get a request and send a response.

That's why this bundle is interesting.

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

First, you need to define your handler in `services.yaml` with this tag `digivia.handler` :
```yaml
services:
    # ...
    App\Handler\MyHandler:
        tags:
            - { name: digivia.handler }
```

Thanks to Symfony autowiring, in case you have multiple handlers, you can define in one time all your handlers :
```yaml
services:
    # ...
    App\Handler\:
        resource: '../src/Handler'
        tags:
            - { name: digivia.handler }
```

Usage
------

Create your form, and add handler for this form :

```php
<?php
namespace App\Handler\Test;

use App\Form\MyFormType;
use Digivia\FormHandler\Handler\AbstractHandler;

class TestHandler extends AbstractHandler
{
    protected function process($data, array $options): void
    {
        // your business logic in case of successful form submitting
        // ie : Doctrine persisting, messenger, mail...
    }

    protected function provideFormTypeClassName(): string
    {
        // Your form class
        return MyFormType::class;
    }
}
```

In your controller, call the form handler factory :

```php
public function edit(HandlerFactoryInterface $factory, Request $request, Post $post) : Response
{
    // Instanciate form handler
    $handler = $factory->createHandler(TestHandler::class);
    // Give data to work with and options to form / handler
    $handler
        ->setData($post) // Optionally, set entity to work with to the form
        ->setFormOptions(['validation_groups' => false]) // Optionally, add form type options if you need
        ->setExtraParams(['form_creation' => true]); // Optionally, give extra parameter to form process method
    // Return Response after treatment    
    return $handler->handle(
        $request,
        // Callable used in case of form submitted with success
        function ($data) {
            return $this->redirectToRoute('post_show', ['id' => $data->getId()]);
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
$handler = $factory->createHandler(TestHandler::class);
// Give data to work with and options to form / handler
$handler->setFormOptions(['validation_groups' => false]); // Optionally, add form type options if you need
// will be sent to $options in FormType :
FormFactory::create(string $type = 'Symfony\Component\Form\Extension\Core\Type\FormType', $data = null, array $options = [])


// Process extra parameters is fourth parameter
$handler = $factory->createHandler(TestHandler::class);
// Give data to work with and options to form / handler
$handler->setExtraParams(['form_creation' => true]); // Optionally, add form type options if you need
// will be sent to $options in this method : 
protected function process($data, array $options): void

```

Note :
------

The two callables, $onSuccess and $render, must return a Response (Symfony\Component\HttpFoundation\Response).
**The form handler will provide status code HTTP 303 if response**
is an instance of Symfony\Component\HttpFoundation\RedirectResponse.
If form is submitted, but not valid, **the handler will provide an HTTP 422 code to the response.**




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

N'hésitez pas à me contacter si vous avez des questions ou des idées d'évolution. Enjoy !

Eric BATARSON - [Digivia](http://www.digivia.fr)
