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
            - { name: digivia.form_handler }
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
public function create(HandlerFactoryInterface $factory, Request $request) : Response
{
    // Entity used in your form
    $entity = new MyEntity();
    $handler = $factory->createHandler(TestHandler::class);
    if ($handler->handle($request, $entity)) {
        return $this->redirectToRoute('index');
    }

    return $this->render('default/create.html.twig', array(
        'form' => $handler->createView(),
    ));
}
```

As you can see, this Controller just get request and send a response...

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

