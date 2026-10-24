# Messenger

- [Messenger](#messenger)
- [Introduction](#introduction)
- [Messages](#messages)
  - [Création d'un Message](#creation-d-39-un-message)
  - [Structure d'un Message](#structure-d-39-un-message)
- [Handlers](#handlers)
  - [Création d'un Handler](#creation-d-39-un-handler)
  - [Structure d'un Handler](#structure-d-39-un-handler)
  - [Autodécouverte](#autodecouverte)
- [Transports](#transports)
  - [Elegant Transport](#elegant-transport)
  - [Sync Transport](#sync-transport)
  - [Failed Transport](#failed-transport)
- [Workers](#workers)
  - [Démarrage d'un Worker](#demarrage-d-39-un-worker)
  - [Arrêt d'un Worker](#arret-d-39-un-worker)
- [Retry Strategy](#retry-strategy)
  - [Configuration](#configuration)
  - [Fonctionnement](#fonctionnement)
- [Commandes Console](#commandes-console)
  - [messenger:consume](#messenger-consume)
  - [messenger:kill](#messenger-kill)
  - [messenger:failed:show](#messenger-failed-show)
  - [messenger:failed:retry](#messenger-failed-retry)
- [Exemple Complet](#exemple-complet)
- [Prochaines Étapes](#prochaines-etapes)

---

## Introduction

Le système **Messenger** de Tonka permet le traitement asynchrone des tâches via des files d'attente (queues). Il est basé sur le composant Messenger de Symfony.

**Fonctionnalités :**
- ✅ Messages asynchrones
- ✅ Handlers autodécouverts
- ✅ Transport Elegant (Base de données)
- ✅ Transport Sync (Exécution synchrone)
- ✅ Gestion des échecs (Failed Transport)
- ✅ Stratégie de retry
- ✅ Workers avec verrouillage

---

## Messages

### Création d'un Message

```bash
php tonka make:message SendWelcomeEmailMessage
```

### Structure d'un Message

```php
namespace App\Message;

class SendWelcomeEmailMessage
{
    public function __construct(
        private int $userId,
        private string $email,
        private string $name
    ) {}

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
```

---

## Handlers

### Création d'un Handler

```bash
php tonka make:message-handler SendWelcomeEmailHandler SendWelcomeEmailMessage
```

### Structure d'un Handler

```php
namespace App\Handlers;

use App\Message\SendWelcomeEmailMessage;
use App\Mail\WelcomeEmail;
use Clicalmani\Core\Mail\MailerInterface;

class SendWelcomeEmailHandler
{
    public function __construct(
        private MailerInterface $mailer
    ) {}

    public function __invoke(SendWelcomeEmailMessage $message): void
    {
        $this->mailer->to($message->getEmail())
            ->send(new WelcomeEmail($message->getName()));

        logger()->info('Welcome email sent', [
            'user_id' => $message->getUserId(),
            'email' => $message->getEmail()
        ]);
    }
}
```

### Autodécouverte

Les handlers sont automatiquement découverts dans le dossier `app/Handlers/`.

**Conditions :**
- La classe doit avoir une méthode `__invoke`
- Le premier paramètre de `__invoke` doit être typé (le message)

---

## Transports

### Elegant Transport

Le transport **Elegant** stocke les messages dans une base de données.

**Configuration :**
```env
MESSENGER_TRANSPORT=elegant
MESSENGER_TABLE=messenger_messages
```

**Migration :**
```bash
php tonka messenger:migrate
```

**Utilisation :**
```php
// config/messenger.php
return [
    'default' => 'elegant://default',
    'transports' => [
        'elegant' => [
            'dsn' => 'elegant://default',
            'options' => [
                'table_name' => 'messenger_messages',
                'queue_name' => 'default',
                'keep' => false,
            ]
        ]
    ]
];
```

### Sync Transport

Le transport **Sync** exécute les messages immédiatement (synchrone).

```php
// config/messenger.php
return [
    'default' => 'sync://default',
];
```

### Failed Transport

Le transport **Failed** stocke les messages qui ont échoué après plusieurs tentatives.

```php
// config/messenger.php
return [
    'failure_transports' => [
        'elegant' => 'messenger.transport.failed',
    ]
];
```

---

## Workers

### Démarrage d'un Worker

```bash
php tonka messenger:consume --transport=elegant
```

### Arrêt d'un Worker

```bash
php tonka messenger:kill
```

---

## Retry Strategy

### Configuration

```php
// config/messenger.php
return [
    'retry_strategy' => [
        'default' => [
            'max_retries' => 3,
            'delay' => 1000, // ms
            'multiplier' => 2,
            'max_delay' => 0,
            'jitter' => 0.1
        ],
        'elegant' => [
            'max_retries' => 5,
            'delay' => 5000,
            'multiplier' => 3
        ]
    ]
];
```

### Fonctionnement

La stratégie de retry utilise un **backoff exponentiel** :

- **Tentative 1** → 1000ms de délai
- **Tentative 2** → 2000ms de délai
- **Tentative 3** → 4000ms de délai

---

## Commandes Console

### messenger:consume

Démarre un worker pour consommer les messages.

```bash
# Transport par défaut
php tonka messenger:consume

# Transport spécifique
php tonka messenger:consume --transport=elegant

# Multiples transports
php tonka messenger:consume --transport=elegant,sync
```

### messenger:kill

Arrête tous les workers en cours.

```bash
php tonka messenger:kill
```

### messenger:failed:show

Affiche les messages en échec.

```bash
# Liste de tous les messages
php tonka messenger:failed:show

# Détails d'un message spécifique
php tonka messenger:failed:show 123 --verbose

# Supprimer un message
php tonka messenger:failed:show 123 --remove
```

### messenger:failed:retry

Relance un ou plusieurs messages en échec.

```bash
# Relancer un message spécifique
php tonka messenger:failed:retry 123

# Relancer tous les messages
php tonka messenger:failed:retry
```

---

## Exemple Complet

### 1. Message

```php
namespace App\Message;

class ProcessOrderMessage
{
    public function __construct(
        private int $orderId,
        private array $items,
        private float $total,
        private int $userId
    ) {}

    public function getOrderId(): int { return $this->orderId; }
    public function getItems(): array { return $this->items; }
    public function getTotal(): float { return $this->total; }
    public function getUserId(): int { return $this->userId; }
}
```

### 2. Handler

```php
namespace App\Handlers;

use App\Message\ProcessOrderMessage;
use App\Models\Order;
use App\Models\Invoice;

class ProcessOrderHandler
{
    public function __invoke(ProcessOrderMessage $message): void
    {
        $order = Order::find($message->getOrderId());
        $order->status = 'processing';
        $order->save();

        Invoice::create([
            'order_id' => $order->id,
            'total' => $message->getTotal(),
            'items' => json_encode($message->getItems())
        ]);

        logger()->info('Order processed', [
            'order_id' => $message->getOrderId(),
            'total' => $message->getTotal()
        ]);
    }
}
```

### 3. Contrôleur

```php
namespace App\Http\Controllers;

use App\Message\ProcessOrderMessage;
use App\Models\Order;
use Symfony\Component\Messenger\MessageBusInterface;

class OrderController extends Controller
{
    public function __construct(
        private MessageBusInterface $bus
    ) {}

    public function store(Request $request)
    {
        $order = Order::create($request->validated());

        // Dispatch du message
        $this->bus->dispatch(
            new ProcessOrderMessage(
                $order->id,
                $order->items,
                $order->total,
                auth()->id()
            )
        );

        return response()->json([
            'success' => true,
            'message' => 'Order created and queued!'
        ]);
    }
}
```

### 4. Configuration (Routing)

```php
// config/messenger.php
return [
    'default' => 'elegant://default',
    
    'routing' => [
        'App\Message\ProcessOrderMessage' => 'elegant',
        'App\Message\SendWelcomeEmailMessage' => 'elegant',
    ],
    
    'retry_strategy' => [
        'default' => [
            'max_retries' => 3,
            'delay' => 1000,
            'multiplier' => 2,
        ]
    ],
    
    'failure_transports' => [
        'elegant' => 'messenger.transport.failed',
    ]
];
```

### 5. Worker

```bash
# Démarrer le worker
php tonka messenger:consume --transport=elegant

# En arrière-plan
nohup php tonka messenger:consume --transport=elegant 
```

---

## Prochaines Étapes

Maintenant que vous maîtrisez Messenger :

1. **[Broadcasting](broadcasting.md)** → Communication temps réel
2. **[Task Scheduling](tasks.md)** → Tâches planifiées
3. **[Tests](testing.md)** → Tests automatiques

---

**Messenger compris !** 🚀 → [Broadcasting](broadcasting.md)