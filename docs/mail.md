# Mail & Notifications

- [Introduction](#introduction)
- [Configuration](#configuration)
  - [Variables d'Environnement](#variables-d-39-environnement)
  - [Fichier de Configuration](#fichier-de-configuration)
- [Création de Mailables](#creation-de-mailables)
  - [Génération d'un Mailable](#generation-d-39-un-mailable)
  - [Structure d'un Mailable](#structure-d-39-un-mailable)
- [Construction d'un Mailable](#construction-d-39-un-mailable)
  - [Définir le Sujet](#definir-le-sujet)
  - [Définir l'Adresse d'Expédition](#definir-l-39-adresse-d-39-expedition)
  - [Définir le Template](#definir-le-template)
  - [Transmettre des Données](#transmettre-des-donnees)
  - [Pièces Jointes](#pieces-jointes)
- [Envoi d'E-mails](#envoi-d-39-e-mails)
  - [Envoi Simple](#envoi-simple)
  - [Envoi en File d'Attente](#envoi-en-file-d-39-attente)
  - [Envoi avec Destinataires](#envoi-avec-destinataires)
- [Templates d'E-mail](#templates-d-39-e-mail)
  - [Templates Twig](#templates-twig)
  - [HTML en Ligne](#html-en-ligne)
- [Notifications](#notifications)
  - [Génération d'une Notification](#generation-d-39-une-notification)
  - [Structure d'une Notification](#structure-d-39-une-notification)
  - [Canaux de Diffusion](#canaux-de-diffusion)
  - [Envoi des Notifications](#envoi-des-notifications)
  - [Notifications en Base de Données](#notifications-en-base-de-donnees)
  - [Notifications Broadcastées](#notifications-broadcastees)
  - [Notifications par E-mail](#notifications-par-e-mail)
  - [Canaux Personnalisés](#canaux-personnalises)
  - [Routage des Notifications](#routage-des-notifications)
  - [Observabilité](#observabilite)
- [Exemple Complet](#exemple-complet)
- [Prochaines Étapes](#prochaines-etapes)

---

## Introduction

Les applications modernes doivent communiquer avec leurs utilisateurs via plusieurs canaux : e-mails transactionnels pour les événements de compte, notifications temps réel dans l'application, centres de notifications persistants... Tonka fournit un système unifié pour **l'envoi d'e-mails** et **les notifications multi-canaux**, permettant de composer une seule classe de notification et de la livrer via n'importe quelle combinaison de canaux.

Le **sous-système mail** fournit une API expressive, fondée sur les *mailables*, pour composer et envoyer des e-mails. Un mailable encapsule tout ce qui concerne un e-mail — sujet, template, destinataire, pièces jointes, métadonnées associées — dans une classe unique et testable.

Le **sous-système notifications** s'appuie sur le mail pour offrir une abstraction de niveau supérieur. Une notification représente un message à livrer à un utilisateur via un ou plusieurs canaux : e-mail, base de données, broadcast (via Mercure SSE), SMS, ou tout canal personnalisé. Vous écrivez la logique de notification une seule fois, et la livrez partout.

```
┌─────────────────┐     ┌──────────────────┐     ┌─────────────────┐
│  Notification    │────▶│  Canal : mail    │────▶│  Boîte e-mail   │
│                  │     └──────────────────┘     └─────────────────┘
│                  │     ┌──────────────────┐     ┌─────────────────┐
│  (classe unique) │────▶│  Canal : db      │────▶│  Centre de      │
│                  │     └──────────────────┘     │  notifications  │
│                  │     ┌──────────────────┐     └─────────────────┘
│                  │────▶│  Canal : SSE     │────▶┌─────────────────┐
└──────────────────┘     └──────────────────┘     │  Toast temps    │
                                                    │  réel           │
                                                    └─────────────────┘
```

> **Pour les développeurs Laravel :** cette architecture (mailables + notifications multi-canaux, méthodes `via()`, `toMail()`, `toDatabase()`, `toBroadcast()`) reprend très fidèlement celle de Laravel. Les principales différences se situent dans les détails d'implémentation signalés tout au long de cette page.

---

## Configuration

### Variables d'Environnement

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=user
MAIL_PASSWORD=pass
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@tonka.app
MAIL_FROM_NAME="Tonka App"
```

Pour le développement local, le mailer `log` est pratique : il écrit les e-mails rendus dans les journaux de l'application plutôt que de tenter un envoi SMTP réel.

```env
MAIL_MAILER=log
```

### Fichier de Configuration

```php
// config/mail.php

return [

    'default' => env('MAIL_MAILER', 'smtp'),

    /*
    |--------------------------------------------------------------------------
    | Mailers Disponibles
    |--------------------------------------------------------------------------
    |
    | Supported: "smtp", "mail", "sendmail", "log"
    |
    */

    'mailers' => [
        'smtp' => [
            'schema' => env('MAIL_MAILER', 'smtp'),
            'host' => env('MAIL_HOST', '127.0.0.1'),
            'port' => env('MAIL_PORT', 2525),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD')
        ],

        'sendmail' => [
            'schema' => 'sendmail',
            'host' => 'default',
            'port' => null,
            'username' => null,
            'password' => null
        ],

        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL'),
        ],

        'failover' => [
            'mailers' => [
                'smtp',
                'postmark',
            ],
        ],

        'roundrobin' => [
            'mailers' => [
                'smtp',
                'postmark',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Expéditeur par Défaut
    |--------------------------------------------------------------------------
    */

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS'),
        'name' => env('MAIL_FROM_NAME'),
    ],

    'queue' => [
        'enabled' => env('MAIL_QUEUE', false),
    ],
];
```

| Mailer | Transport | Idéal pour |
|---|---|---|
| `smtp` | SMTP | Production. Compatible avec tout fournisseur SMTP (Mailgun, Postmark, SES, Mailtrap). |
| `mail` | `mail()` PHP | Configurations simples avec un MTA local. Déconseillé en production. |
| `sendmail` | Binaire Sendmail | Serveurs Linux avec Sendmail ou Postfix installé localement. |
| `log` | Journal applicatif | Développement et tests. Les e-mails sont écrits en log plutôt qu'envoyés. |

---

## Création de Mailables

### Génération d'un Mailable

```bash
php tonka make:mail WelcomeMail
```

Génère une nouvelle classe dans `app/Mail`.

### Structure d'un Mailable

Un mailable est une classe qui étend `Clicalmani\Core\Mail\Mailable` et implémente une méthode `build()`, où se configurent le sujet, le template, les données et les pièces jointes :

```php
namespace App\Mail;

use Clicalmani\Core\Mail\Mailable;
use Clicalmani\Core\Mail\MailableInterface;

class WelcomeMail extends Mailable implements MailableInterface
{
    public function __construct(
        private string $name,
        private string $email
    ) {}

    public function build(): self
    {
        return $this
            ->from('noreply@tonka.app', 'Tonka App')
            ->subject('Welcome to Tonka!')
            ->view('emails/welcome')
            ->with([
                'name' => $this->name,
                'email' => $this->email,
                'app_name' => config('app.name')
            ]);
    }
}
```

---

## Construction d'un Mailable

### Définir le Sujet

```php
public function build(): self
{
    return $this->subject('Welcome to Tonka!');
}

// Sujet dynamique
public function build(): self
{
    return $this->subject('Welcome, ' . $this->name . '!');
}
```

### Définir l'Adresse d'Expédition

```php
public function build(): self
{
    return $this->from('noreply@tonka.app', 'Tonka App');
}
```

Si `from()` n'est pas appelé, l'expéditeur par défaut de `config/mail.php` est utilisé.

### Définir le Template

```php
public function build(): self
{
    return $this->view('emails.welcome');
}
```

Vous pouvez aussi préciser un layout que le template étend :

```php
public function build(): self
{
    return $this->view('emails.welcome')
        ->layout('emails.layout.default');
}
```

### Transmettre des Données

```php
public function build(): self
{
    return $this
        ->view('emails/welcome')
        ->with([
            'name' => $this->name,
            'email' => $this->email,
            'app_name' => config('app.name')
        ]);
}
```

Chaque clé du tableau devient une variable disponible dans le template Twig.

### Pièces Jointes

Utilisez `attach()` pour joindre des fichiers depuis le système de fichiers, ou `attachData()` pour joindre un contenu en mémoire :

```php
use Symfony\Component\Mime\Part\File;

public function build(): self
{
    return $this
        ->view('emails/report')
        ->attach(storage_path('reports/monthly.pdf'))
        // Nom de fichier personnalisé présenté au destinataire
        ->attach(storage_path('reports/monthly.pdf'), [
            'as' => 'monthly_report.pdf',
        ])
        // Type MIME explicite (sinon déduit automatiquement)
        ->attach(storage_path('reports/monthly.pdf'), [
            'mime' => 'application/pdf',
        ])
        // Joindre le contenu d'un flux
        ->attachStream(fopen(storage_path('reports/monthly.pdf'), 'r'), [
            'mime' => 'application/pdf',
        ])
        // Joindre un contenu en mémoire
        ->attachData('File content', 'note.txt', [
            'mime' => 'text/plain',
        ])
        // Incorporer une image depuis une ressource
        ->embed(fopen(storage_path('/path/to/images/logo.png'), 'r'), [
            'as'   => 'logo',
            'mime' => 'image/png',
        ])
        // Incorporer une image depuis un fichier existant
        ->embed(storage_path('/path/to/images/logo.png'), [
            'as'   => 'logo',
            'mime' => 'image/png',
        ]);
}
```

L'option `as` définit le nom de fichier présenté au destinataire ; `mime` fixe explicitement le type MIME (déduit de l'extension du fichier si omis).

---

## Envoi d'E-mails

### Envoi Simple

```php
use App\Mail\WelcomeMail;
use Clicalmani\Core\Support\Facades\Mail;

// Via la façade
Mail::to('user@example.com')->send(new WelcomeMail($user->name, $user->email));

// Via le helper
mailer()->to('user@example.com')->send(new WelcomeMail($user->name, $user->email));
```

### Envoi en File d'Attente

```php
// Mise en file pour un traitement immédiat en arrière-plan
Mail::to('user@example.com')
    ->queue(new WelcomeMail($user->name, $user->email));

// Mise en file avec délai (300 secondes = 5 minutes)
Mail::to('user@example.com')
    ->later(300)
    ->send(new WelcomeMail($user->name, $user->email));
```

L'envoi différé s'appuie sur le système de messagerie de Tonka (voir [Messenger](messenger.md)) — assurez-vous qu'un worker est actif pour traiter ces jobs d'envoi.

### Envoi avec Destinataires

```php
// Destinataire unique
Mail::to('user@example.com')->send($mail);

// Plusieurs destinataires
Mail::to(['user1@example.com', 'user2@example.com'])->send($mail);

// Avec CC et BCC
Mail::to('user@example.com')
    ->cc('admin@example.com')
    ->bcc('logs@example.com')
    ->send($mail);

// CC et BCC multiples
Mail::to('user@example.com')
    ->cc(['admin1@example.com', 'admin2@example.com'])
    ->bcc(['log1@example.com', 'log2@example.com'])
    ->send($mail);

// Reply-To
Mail::to('user@example.com')
    ->replyTo('support@example.com', 'Support Team')
    ->send($mail);
```

---

## Templates d'E-mail

### Templates Twig

Les templates sont écrits en Twig et stockés dans `resources/views/emails`. Un template typique étend un layout partagé :

```twig
{# resources/views/emails/welcome.twig.php #}
{% extends layout %}

{% block content %}
<h1>Hello {{ name }}!</h1>
<p>Welcome to {{ app_name }}.</p>
<p>Your email address: {{ email }}</p>
<p>We're thrilled to have you on board.</p>
{% endblock %}
```

La variable `layout` utilisée dans `{% extends layout %}` est injectée automatiquement lorsque vous appelez `->layout('emails.layout.default')` sur le mailable (voir [Définir le Template](#définir-le-template)).

### HTML en Ligne

Pour les e-mails simples ne justifiant pas un fichier de template séparé :

```php
public function build(): self
{
    return $this->html('
        <h1>Hello!</h1>
        <p>Welcome to Tonka.</p>
    ');
}
```

Pratique pour de courtes notifications, mais au-delà de quelques lignes, un template Twig dédié reste plus maintenable.

---

## Notifications

Là où les mailables conviennent à la composition d'e-mails individuels, les **notifications** fournissent une abstraction de plus haut niveau pour livrer des messages aux utilisateurs via plusieurs canaux — e-mail, base de données, broadcast, SMS, ou tout canal personnalisé — depuis une classe unique.

Une notification représente un *événement sémantique* (« votre commande a été expédiée », « quelqu'un a commenté votre publication ») plutôt qu'un mécanisme de livraison précis. Tonka se charge de traduire la notification en charge utile propre à chaque canal.

```
┌─────────────────────┐
│   Notification       │
│   (classe unique)    │
│                      │
│  via() → [mail,      │
│           database,  │
│           broadcast] │
└──────────┬───────────┘
           │
           ▼
┌───────────────────────────────────────────┐
│  NotificationDispatcher                    │
│  ├─ ChannelRegistry::sorted()              │
│  ├─ Filtrage shouldSend()                  │
│  ├─ Pipeline de middlewares                │
│  └─ Événements de cycle de vie             │
│     (Sending/Sent/Failed/Skipped)          │
└──────┬─────────┬─────────┬─────────────────┘
       │         │         │
       ▼         ▼         ▼
   ┌───────┐ ┌───────┐ ┌────────┐
   │ mail  │ │ db    │ │ SSE    │
   └───────┘ └───────┘ └────────┘
```

> **Lien avec la page Broadcasting :** la page [Broadcasting](broadcasting.md#broadcasting-des-notifications) traite déjà en détail du canal `broadcast` (configuration Mercure, autorisation des topics, écoute côté client). Cette section-ci couvre les notifications dans leur ensemble, tous canaux confondus — reportez-vous à Broadcasting pour tout ce qui concerne spécifiquement la diffusion temps réel.

### Génération d'une Notification

```bash
php tonka make:notification OrderShipped
```

### Structure d'une Notification

Une notification est une classe PHP simple qui implémente `NotificationInterface` et déclare ses canaux de livraison via `via()` :

```php
namespace App\Notifications;

use App\Models\Order;
use Clicalmani\Notification\NotificationInterface;

class OrderShipped implements NotificationInterface
{
    public function __construct(
        protected Order $order
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via(): array
    {
        return ['mail', 'database', 'broadcast'];
    }
}
```

L'entité « notifiable » qui reçoit la notification — typiquement un modèle `User`, mais tout modèle utilisant le trait `Notifiable` peut recevoir des notifications :

```php
namespace App\Models;

use Clicalmani\Database\Traits\Notifiable;

class User extends Elegant
{
    use Notifiable;
}
```

### Canaux de Diffusion

#### Canaux Intégrés

| Canal | Description | Méthode requise |
|---|---|---|
| `mail` | Livre la notification par e-mail. | `toMail()` |
| `database` | Stocke la notification dans la table `notifications`. | `toDatabase()` ou `toArray()` |
| `broadcast` | Livre la notification en temps réel via Mercure SSE. | `toBroadcast()` ou `toArray()` |

Chaque canal dispose d'une méthode correspondante qui transforme la notification en charge utile propre au canal. Si cette méthode n'est pas définie, Tonka se rabat sur `toArray()`.

Le dispatcher traite les canaux dans un ordre de priorité : base de données, puis mail, puis broadcast. Cet ordre garantit, par exemple, qu'une notification est persistée en base **avant** d'être diffusée — un client recevant l'événement SSE peut ainsi charger l'enregistrement correspondant en toute sécurité.

### Envoi des Notifications

```php
use App\Models\User;
use App\Notifications\OrderShipped;

$user = User::find(1);
$order = Order::find(42);

$user->notify(new OrderShipped($order));
```

Pour plusieurs destinataires :

```php
use Clicalmani\Core\Support\Facades\Notification;

Notification::send($users, new OrderShipped($order));
```

#### Destinataires Ad Hoc

Pour notifier un destinataire non porté par un modèle — une adresse e-mail brute, un webhook Slack, un numéro SMS — utilisez `Notification::route()` :

```php
use Clicalmani\Core\Support\Facades\Notification;

// Canal unique
Notification::route('mail', 'client@example.com')
    ->notify(new OrderShipped($order));

// Plusieurs canaux pour le même destinataire
Notification::route('mail', 'ops@example.com')
    ->route('slack', 'https://hooks.slack.com/services/XXX')
    ->route('sms', '+22990000000')
    ->notify(new ServerDown($server));
```

Avec `route()`, seuls les canaux explicitement listés sont exécutés — même si `via()` en retourne davantage. Le dispatcher filtre la liste des canaux contre les routes de l'`AnonymousNotifiable`.

### Événements de Cycle de Vie

Tonka émet quatre événements lors du dispatch de chaque canal de notification — des points d'accroche idéaux pour la journalisation, les métriques ou l'audit.

| Événement | Moment |
|---|---|
| `NotificationSending` | Avant l'appel à `send()` du canal. |
| `NotificationSent` | Après une livraison réussie. |
| `NotificationSkipped` | Lorsque `shouldSend()` a retourné `false` pour ce canal. |
| `NotificationFailed` | Lorsque `send()` a levé une exception (après les tentatives du middleware). |

```php
use Clicalmani\Notification\Events\{
    NotificationSending,
    NotificationSent,
    NotificationFailed,
    NotificationSkipped,
};

Event::listen(NotificationSent::class, function (NotificationSent $e) {
    metrics()->increment("notifications.sent.{$e->channel}", tags: [
        'event' => basename(str_replace('\\', '/', $e->context->event::class)),
    ]);
});

Event::listen(NotificationFailed::class, function (NotificationFailed $e) {
    logger()->error("Notification failed on {$e->channel}", [
        'event' => $e->context->event::class,
        'error' => $e->exception->getMessage(),
    ]);
});
```

### Middlewares de Notification

Un middleware enveloppe l'appel à `send()` d'un canal — utile pour la journalisation, la retentative sur échec transitoire, la limitation de débit, le mode simulation en test, ou l'ajout de tags propres à un tenant.

```php
// bootstrap/app.php
use Clicalmani\Core\Support\Facades\Notification;
use Clicalmani\Notification\Middleware\{
    LogNotificationMiddleware,
    RetryMiddleware,
};

return \Clicalmani\Core\Maker\Application::setup(rootPath: dirname(__DIR__))
        ->withMailer()
        ->withNotification(static function() {
            ChannelRegistry::globalMiddleware(LogNotificationMiddleware::class);
            ChannelRegistry::middleware(EmailChannel::name(), new RetryMiddleware());
        });
```

```php
interface ChannelMiddlewareInterface
{
    /**
     * @param callable(NotificationContext): void $next
     */
    public function handle(NotificationContext $context, callable $next): void;
}
```

### Notifications en Base de Données

```bash
php tonka make:entity Notification notifications id
```

```php
// database/entities/NotificationEntity.php
namespace Database\Entities;

use Clicalmani\Database\DataTypes\DateTime;
use Clicalmani\Database\DataTypes\Integer;
use Clicalmani\Database\DataTypes\Json;
use Clicalmani\Database\DataTypes\Timestamp;
use Clicalmani\Database\DataTypes\VarChar;
use Clicalmani\Database\Factory\Entity;
use Clicalmani\Database\Factory\PrimaryKey;
use Clicalmani\Database\Factory\Property;

class NotificationEntity extends Entity
{
    #[Property(
        length: 10,
        unsigned: true,
        autoIncrement: true
    ), PrimaryKey]
    public Integer $id;

    #[Property(length: 40)]
    public VarChar $type;

    #[Property]
    public Json $data;

    #[Property]
    public ?DateTime $read_at;

    #[Property(length: 10, unsigned: true)]
    public Integer $notifiable_id;

    #[Property(length: 255)]
    public VarChar $notifiable_type;

    public Timestamp $created_at;

    #[Property]
    public ?DateTime $updated_at;
}
```

**Définir la charge persistée :**

```php
// app/Notifications
class OrderShipped implements NotificationInterface
{
    public function __construct(
        protected Order $order
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via(): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'order_reference' => $this->order->reference,
            'message' => "Your order #{$this->order->reference} has been shipped.",
            'action_url' => route('orders.show', $this->order->id),
        ];
    }
}
```

**Récupérer les notifications :**

```php
// app/Models/User.php
class User extends Elegant
{
    // ...

    public function notifications(): MorphToMany
    {
        $this->morphToMany(Notification::class, 'notifiable');
    }
}
```

```php
class Notification extends Elegant
{
    // ...

    public function markAsRead(): void
    {
        // Logic
    }
}
```

```php
// Toutes les notifications
foreach ($user->notifications as $notification) {
    echo $notification->data['message'];
}

// Uniquement les non lues
foreach ($user->unreadNotifications as $notification) {
    echo $notification->data['message'];
}

// Marquer comme lues
$user->unreadNotifications->markAsRead();

// Marquer une notification précise comme lue
$notification->markAsRead();
```

### Notifications Broadcastées

Le canal `broadcast` livre les notifications en temps réel via Mercure SSE. Combiné au canal `database`, ce schéma permet un centre de notifications en direct : la base persiste la notification, le broadcast la pousse immédiatement aux clients connectés.

```php
namespace App\Notifications;

use Clicalmani\Notification\NotificationInterface;
use Broadcaster\ShouldBroadcastInterface;

class OrderShipped implements NotificationInterface, ShouldBroadcastInterface
{
    public function __construct(
        private App\Models\Order $order
    )
    {}

    public function broadcastOn(): array
    {
        return [
            // Topics list
            'users.' . $this->order->user_id
        ];
    }

    public function via(): array
    {
        return ['database', 'broadcast'];
    }

    public function toBroadcast(): array
    {
        return [
            'notification_id' => $this->id,
            'type' => 'order.shipped',
            'title' => 'Order Shipped',
            'message' => "Your order #{$this->order->reference} has been shipped.",
            'action_url' => route('orders.show', $this->order->id),
            'created_at' => now()->toIso8601String(),
        ];
    }
}
```

Côté client, abonnez-vous au topic privé de l'utilisateur et écoutez la notification :

```typescript
const channel = broadcaster.private(`users.${currentUserId}`);

channel.listen('users.123', (notification) => {
    showToast(notification.title, notification.message);
    incrementNotificationBadge();
});
```

Voir la page [Broadcasting](broadcasting.md#broadcasting-des-notifications) pour la configuration complète du temps réel.

### Notifications par E-mail

Définissez une méthode `toMail()` retournant un mailable :

```php
use Clicalmani\Core\Mail\Mailable;
use Clicalmani\Notification\NotificationInterface;

class OrderShipped extends Mailable implements NotificationInterface
{
    public function __construct(
        private App\Models\Order $order
    )
    {}

    public function build(): Mailable
    {
        return $this
            ->subject('Your Order Has Shipped')
            ->view('emails.orders.shipped')
            ->with([
                'order' => $this->order,
                'user' => $this->order->user,
            ]);
    }

    public function toMail(): Mailable
    {
        return $this->build();
    }
}
```

### Canaux Personnalisés

Pour ajouter un canal personnalisé — par exemple, SMS via Twilio — implémentez `NotificationChannelInterface` :

```php
namespace App\Notifications\Channels;

use App\Services\SmsService;
use Clicalmani\Notification\Concerns\DefaultChannelBehavior;
use Clicalmani\Notification\Contracts\NotificationChannelInterface;
use Clicalmani\Notification\NotificationContext;

class SmsChannel implements NotificationChannelInterface
{
    use DefaultChannelBehavior;

    public function __construct(
        protected SmsService $sms
    ) {}

    public static function name(): string
    {
        return 'sms';
    }

    public static function priority(): int
    {
        return 5;
    }

    public function shouldSend(NotificationContext $context): bool
    {
        return method_exists($context->event, 'toSms')
            && $context->routeFor('sms') !== null;
    }

    public function send(NotificationContext $context): void
    {
        $message = $context->event->toSms($context->notifiable);

        $this->sms->send($context->routeFor('sms'), $message);
    }
}
```

Enregistrez le canal dans `bootstrap/kernel.php` — cohérent avec les tableaux `commands`/`providers` déjà présentés sur la page [Structure du Projet](structure.md#bootstrapkernelphp) :

```php
// bootstrap/kernel.php

return [
    // ...

    'notifications' => [
        Clicalmani\Notification\Channels\BroadcastChannel::class,
        Clicalmani\Notification\Channels\DatabaseChannel::class,
        Clicalmani\Notification\Channels\EmailChannel::class,
        App\Notifications\Channels\SmsChannel::class,
    ],
]
```

Pour un canal avec dépendances ou configuration, utilisez l'enregistrement différé :

```php
// bootstrap/app.php
return \Clicalmani\Core\Maker\Application::setup(rootPath: dirname(__DIR__))
            // ...
            ->withService(static function(Application $app) {
                $app->addService(
                    \App\Services\SmsService::class,
                    \App\Services\SmsService::class,
                    static function($config) {
                        $config->args([
                            // Arguments du service
                        ]);
                    },
                    ['sms.service', \App\Services\SmsService::class],
                );
            });
```

```php
// app/Providers/AppServiceProvider.php

use Clicalmani\Core\Support\Facades\Notification;

public function boot() : void
{
    Notification::extend('sms', fn() => new SmsChannel(
        container()->get('sms.service')
    ));
}
```

Ce mécanisme ne résout le canal qu'à son premier usage effectif, minimisant le coût d'amorçage de l'application.

Référencez ensuite le canal dans `via()` :

```php
// app/Notifications

class OrderShipped implements NotificationInterface
{
    // ...

    public function via(): array
    {
        return ['mail', 'database', 'sms'];
    }

    public function toSms(): string
    {
        return "Order #{$this->order->reference} has shipped!";
    }
}
```

### Routage des Notifications

Un notifiable résout son adresse propre à chaque canal via `routeNotificationFor()` :

```php
// app/Models/User.php

use Clicalmani\Notification\Concerns\Notifiable;

class User extends Elegant
{
    use Notifiable;

    public function routeNotificationFor(string $channel): mixed
    {
        return match ($channel) {
            'mail'  => $this->email,
            'sms'   => $this->phone,
            'slack' => $this->slack_webhook,
            default => null,
        };
    }
}
```

### Observabilité

Les événements de cycle de vie émis pour chaque canal rendent le câblage de métriques trivial ([voir Métriques pour plus de détails](metrics.md)) :

```php
use Clicalmani\Core\Support\Facades\Event;

Event::listen(NotificationSent::class, function (NotificationSent $e) {
    metrics()->increment("notifications.sent.{$e->channel}");
});

Event::listen(NotificationFailed::class, function (NotificationFailed $e) {
    metrics()->increment("notifications.failed.{$e->channel}", tags: [
        'error' => $e->exception::class,
    ]);
});
```

Combiné aux mesures de durée du dispatcher, vous obtenez les quatre métriques RED par canal : **Rate** (débit), **Errors** (erreurs), **Duration** (durée), et, via `notifications.sending`, une approximation de **Saturation** si vous suivez la profondeur de la file.

---

## Exemple Complet

Illustrons l'ensemble avec un flux d'inscription utilisateur : un e-mail de bienvenue, et une notification de bienvenue persistée + diffusée en temps réel.

```php
namespace App\Mail;

use App\Models\User;
use Clicalmani\Core\Mail\Mailable;
use Clicalmani\Notification\NotificationInterface;
use Broadcaster\ShouldbroadcastInterface;

class WelcomeMail extends Mailable implements NotificationInterface, ShouldbroadcastInterface
{
    public function __construct(
        protected User $user
    ) {}

    public function build(): self
    {
        $this
            ->from('noreply@tonka.app', 'Tonka App')
            ->subject('Welcome to Tonka!')
            ->view('emails.welcome')
            ->with([
                'name' => $this->user->name,
                'email' => $this->user->email,
                'app_name' => config('app.name'),
            ]);

        // Joindre le guide utilisateur
        $this->attach(storage_path('guides/user-guide.pdf'), [
            'as' => 'user-guide.pdf',
            'mime' => 'application/pdf',
        ]);

        return $this;
    }

    public function via(): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase(): array
    {
        return [
            'type' => 'welcome',
            'title' => 'Welcome to Tonka!',
            'message' => "We're thrilled to have you on board, {$this->user->name}.",
            'action_url' => route('dashboard'),
        ];
    }

    public function toBroadcast(): array
    {
        return [
            'type' => 'welcome',
            'title' => 'Welcome to Tonka!',
            'message' => "We're thrilled to have you on board, {$this->user->name}.",
            'action_url' => route('dashboard'),
        ];
    }
}
```

### Contrôleur

```php
namespace App\Http\Controllers;

use App\Mail\WelcomeMail;
use App\Notifications\WelcomeNotification;
use App\Models\User;
use Clicalmani\Core\Support\Facades\Mail;
use Clicalmani\Core\Http\RequestInterface;

class UserController extends Controller
{
    public function store(RequestInterface $request)
    {
        $user = User::create($request->validated());

        // Envoyer l'e-mail de bienvenue
        Mail::to($user->email)->send(
            new WelcomeMail($user)
        );

        // Envoyer la notification de bienvenue (base de données + broadcast)
        $user->notify(new WelcomeNotification($user));

        return redirect('/users')
            ->with('success', 'User created and notified!');
    }

    public function sendReport(RequestInterface $request)
    {
        // Mettre un e-mail en file pour un envoi différé
        Mail::to('admin@example.com')
            ->queue(new ReportMail());

        return response()->json([
            'success' => true,
            'message' => 'Report queued'
        ]);
    }
}
```

### Observer

```php
namespace App\Observers;

use App\Mail\WelcomeMail;
use App\Notifications\WelcomeNotification;
use App\Models\User;
use Clicalmani\Core\Support\Facades\Mail;

class UserObserver
{
    public function created(User $user): void
    {
        // Envoyer l'e-mail de bienvenue
        Mail::to($user->email)->send(
            new WelcomeMail($user)
        );

        // Créer la notification in-app
        $user->notify(new WelcomeNotification($user));
    }
}
```

```php
// app/Models/User.php
class User extends Elegant
{
    use Notifiable;

    // ...

    protected array $observers = [
        \App\Observers\UserObserver::class
    ];
}
```

---

## Prochaines Étapes

Maintenant que vous maîtrisez le mail et les notifications :

1. **[Validation](validation.md)** → Valider les données entrantes
2. **[Base de Données](database.md)** → Travailler avec les modèles et l'ORM
3. **[Messenger](messenger.md)** → Mettre en file les e-mails et notifications pour un traitement en arrière-plan
4. **[Broadcasting](broadcasting.md)** → Configurer la livraison de notifications en temps réel via Mercure
5. **[Sessions](sessions.md)** → Gérer les sessions utilisateur

---

**Mail & Notifications maîtrisés !** 🚀 → [Validation](validation.md)