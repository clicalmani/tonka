- [Introduction](#introduction)
- [Configuration](#configuration)
- [Driver Prerequisite](#driver-prerequisite)
- [Mail Transport](#mail-transport)
- [Transport Setup](#transport-setup)
- [Using a 3rd Party Transport](#using-a-3rd-party-transport)
- [Composing](#composing)
- [High Availability Configuration](#high-availability-configuration)
- [Load Balancing Configuration](#load-balancing-configuration)
- [Configuring Emails Globally](#configuring-emails-globally)
- [Sending Emails](#sending-emails)
- [Queueing Mail](#queueing-mail)

# Introduction

Tonka provides a simple and efficient way to send emails. This guide covers the basics of configuring mail settings, composing messages, and sending emails using the popular [Symfony Mailer](https://symfony.com/doc/current/mailer.html) component functionality.

# Configuration

Tonka's email services may also be configured via your application's `config/mail.php` configuration file. Each mailer defined in this file can have its own unique transport settings, allowing you to manage multiple mailers with different drivers or credentials as needed.

Within your mail configuration file, you will find a `mailers` configuration array. This array contains a sample configuration entry for each of the major mail transports supported by Tonka, while the `default` configuration value determines which mailer will be used by default when your application needs to send an email message.

Example `config/mail.php`:

```php
return [
    'default' => env('MAIL_MAILER', 'smtp'),

    'mailers' => [
        'smtp' => [
            'schema' => 'smtp',
            'host' => env('MAIL_HOST', 'smtp.example.com'),
            'port' => env('MAIL_PORT', 465),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
        ],
        'postmark' => [
            'schema' => 'postmark+smtp',
            'host' => 'default',
            'port' => null,
            'username' => 'ID',
            'password' => null
        ],
        'resend' => [
            'schema' => 'resend+smtp',
            'host' => 'default',
            'port' => null,
            'username' => 'resend',
            'password' => 'API_KEY'
        ],
        // Add other mailers as needed...
    ],

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'from@example.com'),
        'name' => env('MAIL_FROM_NAME', 'Example'),
    ],
];
```

You may customize or add additional mailers as required for your application's needs.

## Driver Prerequisite

Before sending emails, ensure the required mailer driver is installed. Tonka uses Symfony Mailer, so you need the `symfony/mailer` package:

```bash
composer require symfony/mailer
```

## Mail Transport

Tonka supports multiple mail transport options, allowing you to choose the best method for your environment. The most common transport is SMTP, but you can also use third-party services like Sendmail or native PHP mail.

To specify the transport, set the `MAIL_MAILER` value in your `.env` file:

- `smtp` – Use an SMTP server.
- `sendmail` – Use the Sendmail program.
- `mail` – Use PHP's native mail function.

Example:

```env
MAIL_MAILER = 'smtp'
```

Refer to the [Symfony Mailer transports documentation](https://symfony.com/doc/current/mailer.html#transport) for advanced configuration options.

## Transport Setup

To set up a mail transport in your environment, add a mail service in your `bootstrap/app.php` configuration file using the `addService` method. This method accepts an anonymous function that receives the application instance. Within this function, register your mail transport and mailer services as follows:

```php
// bootstrap/app.php

use Clicalmani\Foundation\Maker\Application;
use Symfony\Component\DependencyInjection\Loader\Configurator\DefaultsConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ServiceConfigurator;

return Application::setup(rootPath: dirname(__DIR__))
    ->withService(static function(Application $app) {
        $app->addService('smtp.mailer.transport', [\Clicalmani\Foundation\Mail\MailerTransport::class]);
        $app->addService(
            'smtp.mailer', 
            [
                \Clicalmani\Foundation\Mail\Mailer::class,
                static fn(ServiceConfigurator|DefaultsConfigurator $config) => 
                    $config->args([$app->dependency('service', 'smtp.mailer.transport')])
            ]
        );
    })
    ->run();
```

This configuration ensures that your mail transport is available throughout your application and that the mailer service uses it for sending emails.

?> **Note:** The `mailer` suffix (e.g., `smtp.mailer`, `smtp.mailer.transport`) must be appended to the mail transport service name. This ensures that each mailer is correctly associated with its corresponding transport in the service container.

You may add as many transports as you want by defining custom mailers and/or custom transports. A custom mailer is simply a subclass of the `Mailer` class, allowing you to encapsulate specific transport logic or configuration.

Example custom mailer:

```php
<?php
namespace App\Mailer;

use Clicalmani\Foundation\Mail\Mailer;

class LogMailer extends Mailer
{
    // Optionally override methods or add new functionality
}
```

Example Custom Transport:

Below is a minimal example of a custom mail transport class that logs emails to a file instead of sending them. This can be useful for development or testing environments.

```php
<?php
namespace App\Mailer\Transport;

use Clicalmani\Foundation\Support\Facades\Env;
use Clicalmani\Foundation\Mail\TransportInterface;

class LogMailerTransport implements TransportInterface
{
    /**
     * Creates a mailer transport instance.
     *
     * @return \Symfony\Component\Mailer\Transport\TransportInterface
     */
    public function create() : \Symfony\Component\Mailer\Transport\TransportInterface
    {
        return \Symfony\Component\Mailer\Transport::fromDsn(Env::get('MAILER_LOG_DSN'));
    }
}
```

Register this transport in your service container and use it as needed for local development or debugging.

Register your custom mailer in the service container:

```php
// bootstrap/app.php

use Clicalmani\Foundation\Maker\Application;
use Symfony\Component\DependencyInjection\Loader\Configurator\DefaultsConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ServiceConfigurator;

return Application::setup(rootPath: dirname(__DIR__))
    ->withService(static function(Application $app) {
        $app->addService('log.mailer.transport', [\App\Mailer\Transport\LogMailerTransport::class]);
        $app->addService(
            'log.mailer', [
                \App\Mailer\LogMailer::class, 
                static fn(ServiceConfigurator|DefaultsConfigurator $config) => 
                    $config->args([$app->dependency('service', 'log.mailer.transport')])
            ]
        );
    })
    ->run();
```

You can now use your custom mailer throughout your application, enabling flexible integration with multiple transports as needed.

## Using a 3rd Party Transport

Tonka allows integration with third-party mail transports such as `Mailgun`, `SendGrid`, or `Amazon SES`. To use a third-party transport, install the corresponding Symfony Mailer bridge package. For example, to use Mailgun:

```bash
composer require symfony/mailgun-mailer
```

Update your `.env` file with the appropriate DSN for the service:

```env
MAIL_MAILER = 'mailgun'
MAIL_DSN = 'mailgun+api://API_KEY@default?domain=your-domain.com'
```

Modify your transport class to use the DSN:

```php
namespace App\Mailer\Transport;

use Clicalmani\Foundation\Support\Facades\Env;
use Clicalmani\Foundation\Mail\TransportInterface;

class LogMailerTransport implements TransportInterface
{
    /**
     * Creates a mailer transport instance.
     *
     * @return \Symfony\Component\Mailer\Transport\TransportInterface
     */
    public function create() : \Symfony\Component\Mailer\Transport\TransportInterface
    {
        return \Symfony\Component\Mailer\Transport::fromDsn(Env::get('MAIL_DSN'));
    }
}
```

Refer to the [Symfony Mailer bridges documentation](https://symfony.com/doc/current/mailer.html#using-a-3rd-party-transport) for details on supported services and DSN formats.

## Composing

To compose an email, use Symfony's [Mime Component](https://symfony.com/doc/current/components/mime.html) Email class to create a message object. You can set the subject, body (plain text or HTML), recipients, and add attachments if needed.

```php
// app/Http/Controllers/MailController

use Clicalmani\Foundation\Mail\Email;
use Clicalmani\Foundation\Mail\MailerInterface;

public function sendMail(MailerInterface $mailer)
{
    $mail = (new Email('Time for Symfony Mailer!', view('user.notify', ['name' => 'John Doe'])->render()))
        ->from(env('MAIL_FROM_ADDRESS', ''))
        ->to('john.doe@example.com')
        ->text('Sending emails is fun again!');
    $mailer->send($mail);
}
```

You can add multiple recipients using `cc()` and `bcc()` methods:

```php
$mail->cc('cc@example.com');
$mail->bcc('bcc@example.com');
```

For more advanced options such as embedding images, adding attachments, or customizing headers, refer to the [Symfony Email documentation](https://symfony.com/doc/current/mailer.html#creating-sending-messages).

## High Availability Configuration

Tonka's mailer support [High Availability](https://en.wikipedia.org/wiki/High_availability) to ensure reliable email delivery by automatically switching to backup transports if the primary one fails. This is useful for maintaining high availability and minimizing disruptions:

```env
MAIL_DSN = 'failover(smtp://user:pass@smtp1.example.com smtp://user:pass@smtp2.example.com)'
```

In your custom transport class, use the DSN as usual:

```php
public function create() : \Symfony\Component\Mailer\Transport\TransportInterface
{
    return \Symfony\Component\Mailer\Transport::fromDsn(Env::get('MAIL_DSN'));
}
```

Symfony Mailer will attempt to send the email using each transport in order until one succeeds. This provides automatic failover without additional code changes.

For more information, see the [Symfony Mailer High Availability documentation](https://symfony.com/doc/current/mailer.html#high-availability).

## Load Balancing Configuration

Tonka supports load [balancing](https://en.wikipedia.org/wiki/Load_balancing_(computing)), which distributes email sending across multiple transports for load balancing. Configure round robin by providing a DSN string with multiple transports separated by a comma (`,`):

```env
MAIL_DSN = 'roundrobin(smtp://user:pass@smtp1.example.com smtp://user:pass@smtp2.example.com)'
```

Update your transport class to use the DSN:

```php
public function create() : \Symfony\Component\Mailer\Transport\TransportInterface
{
    return \Symfony\Component\Mailer\Transport::fromDsn(Env::get('MAIL_DSN'));
}
```

Symfony Mailer will cycle through the listed transports, sending each email using the next available transport. For more details, see the [Symfony Mailer round robin documentation](https://symfony.com/doc/current/mailer.html#load-balancing).

## Configuring Emails Globally

You can define global email settings to ensure consistency across all outgoing messages. The `from` address and name specified in your `config/mail.php` file will be used as the default sender for all emails:

```php
'from' => [
    'address' => env('MAIL_FROM_ADDRESS', ''),
    'name' => env('MAIL_FROM_NAME', 'Example'),
],

'to' => [
    'address' => env('MAIL_TO_ADDRESS', ''),
    'name' => env('MAIL_TO_NAME', 'Toure Iliass'),
],

'cc' => [
    'address' => env('MAIL_CC_ADDRESS', ''),
    'name' => env('MAIL_CC_NAME', 'Tonka'),
],

'headers' => [
    'dates' => [
        'X-Mailer-Date' => \DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s'), new \DateTimeZone('UTC')),
        'X-Mailer-Received' => \DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s'), new \DateTimeZone('UTC')),
    ],
    'mailbox' => [
        'X-Mailer-From' => 'Tonka Mailer <' . env('MAIL_FROM_ADDRESS', '') . '>',
        'X-Mailer-To' => 'Tonka Mailer <' . env('MAIL_TO_ADDRESS', '') . '>',
    ],
    'tags' => ['password-reset'],
    'metadata' => [
        'X-Mailer' => 'Tonka Mailer',
        'X-Mailer-Version' => '2.3.4',
    ],
    'unstructured' => [
        'X-Mailer-Header' => 'Tonka Mailer',
    ],
    'parametized' => [
        'X-Mailer-Param' => 'Tonka Mailer',
    ]
],
```

When composing emails, you do not need to set the sender explicitly unless you want to override the global configuration for a specific message. The mailer will automatically use these global settings if the `from` address is not provided.

To override the global sender for a particular email, use the `from()` method:

```php
$mail->from('custom-sender@example.com', 'Custom Sender');
```

This approach helps maintain a consistent sender identity and simplifies email management throughout your application.

## Sending Emails

Once your mailer and transport are configured, you can send emails using the injected `MailerInterface` in your controllers or services. Here’s a basic example:

```php
use Clicalmani\Foundation\Mail\Email;
use Clicalmani\Foundation\Mail\MailerInterface;

public function notifyUser(MailerInterface $mailer)
{
    $email = (new Email('Welcome!', 'Thank you for registering.'))
        ->from(env('MAIL_FROM_ADDRESS', ''))
        ->to('user@example.com');
    $mailer->send($email);
}
```

You may customize the subject, body, recipients, and add attachments as needed. For advanced usage, refer to the [Symfony Mailer documentation](https://symfony.com/doc/current/mailer.html#creating-sending-messages).

### Email Address Formats

When specifying email addresses in Tonka, you can use either a simple string (e.g., `'user@example.com'`) or an instance of the `Address` object from Symfony's Mime component. This provides flexibility for advanced use cases, such as specifying a display name along with the email address.

**Using a string:**
```php
$mail->to('user@example.com');
```

**Using an Address object:**
```php
use Symfony\Component\Mime\Address;

$mail->to(new Address('user@example.com', 'User Name'));
$mail->to(Address::create('user@example.com <User Name>'));
```

You can use `Address` objects for `from()`, `to()`, `cc()`, and `bcc()` methods. This allows you to set both the email and the display name:

```php
$mail->from(new Address('no-reply@example.com'));
$mail->cc(new Address('manager@example.com', 'Manager'));
```

You can specify multiple recipients for `to()`, `cc()`, or `bcc()` by passing multiple addresses to each method:

```php
use Symfony\Component\Mime\Address;

// Using strings
$mail->to('user1@example.com', 'user2@example.com');

$mail->cc(new Address('manager@example.com', 'Manager'), new Address('teamlead@example.com', 'Team Lead'));

// Mixing strings and Address objects
$mail->bcc('auditor@example.com', new Address('ceo@example.com', 'CEO'));
```

This allows you to easily send emails to multiple recipients in a single call.

## Message Headers

You can customize email headers to include additional metadata or comply with specific requirements. Symfony's Email class provides methods to add custom headers to your messages.

**Adding a custom header:**
```php
$mail->getHeaders()->addTextHeader('X-Custom-Header', 'HeaderValue');
```

**Adding multiple headers:**
```php
$mail->getHeaders()
    ->addTextHeader('X-Tracking-ID', '123456')
    ->addTextHeader('X-Environment', 'Production');
```

You can also set structured headers, such as tags or metadata, using the appropriate header types:

```php
use Symfony\Component\Mime\Header\UnstructuredHeader;

$mail->getHeaders()->add(new UnstructuredHeader('X-Tag', 'welcome-email'));
```

These headers will be included in the outgoing email and can be used by mail servers or third-party services for tracking, categorization, or custom processing.

For more details, see the [Symfony Mailer headers documentation](https://symfony.com/doc/current/mailer.html#message-headers).

## Handling Attachments

You can attach files to your emails using the `attach()` method. This method accepts the file path and an optional name for the attachment:

```php
$mail->attach('/path/to/document.pdf', 'UserGuide.pdf');
```

To attach files from a string (for example, dynamically generated content), use `attachFromPath()` or `attachFromData()`:

```php
$mail->attachFromData('Report content', 'report.txt', 'text/plain');
```

You may also embed images in HTML emails using the `embed()` method, which returns a Content-ID (CID) that you can reference in your HTML body:

```php
$cid = $mail->embed('/path/to/image.png');
$mail->html('<img src="cid:' . $cid . '" alt="Embedded Image">');
```

For more details on attachments and embedding, see the [Symfony Email attachments documentation](https://symfony.com/doc/current/mailer.html#attachments-and-embeds).


## Queueing Mail

Tonka supports queuing emails for asynchronous delivery, improving performance and reliability. To queue mail, configure your application to use a queue driver (such as Redis, database, or file-based queues).

When sending an email, dispatch it to the queue instead of sending immediately:

```php
use Clicalmani\Foundation\Acme\MailerInterface;
use Clicalmani\Foundation\Queue\Queue;

public function sendQueuedMail(MailerInterface $mailer)
{
    Queue::push(function() use ($mailer) {
        $email = (new \Symfony\Component\Mime\Email())
            ->from(env('MAIL_FROM_ADDRESS', ''))
            ->to('jane.doe@example.com')
            ->subject('Queued Email Example')
            ->text('This email was queued for delivery.');
        $mailer->send($email);
    });
}
```

Ensure your queue worker is running to process queued jobs. Refer to the [Tonka Queue documentation](../queue.md) for setup and management instructions.
