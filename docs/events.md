# Événements

- [Événements](#evenements)
- [Introduction](#introduction)
- [Définition d'un Événement](#definition-d-39-un-evenement)
  - [Création d'un Événement](#creation-d-39-un-evenement)
  - [Structure d'un Événement](#structure-d-39-un-evenement)
- [Listeners](#listeners)
  - [Création d'un Listener](#creation-d-39-un-listener)
  - [Enregistrement avec Attributs](#enregistrement-avec-attributs)
  - [Priorité](#priorite)
- [Dispatch d'un Événement](#dispatch-d-39-un-evenement)
  - [Via le Dispatcher](#via-le-dispatcher)
  - [Via le Helper](#via-le-helper)
- [Événements Broadcastés](#evenements-broadcastes)
  - [Définition](#definition)
  - [Canaux de Broadcast](#canaux-de-broadcast)
  - [Données de Broadcast](#donnees-de-broadcast)
- [Listeners avec Méthode Personnalisée](#listeners-avec-methode-personnalisee)
- [Exemple Complet](#exemple-complet)
- [Prochaines Étapes](#prochaines-etapes)

---

## Introduction

Le système d'événements de Tonka permet de découpler les différentes parties de votre application. Il est basé sur le composant EventDispatcher de Symfony (déjà mentionné comme service `withEvent` sur la page [Architecture](architecture.md#services-intégrés-disponibles)) et supporte le broadcasting en temps réel via Mercure (`withEventBroadcasting`).

**Fonctionnalités :**
- ✅ Dispatch d'événements synchrones
- ✅ Listeners avec attributs PHP 8
- ✅ Priorité d'exécution
- ✅ Broadcasting en temps réel (Mercure)
- ✅ Découverte automatique des listeners

> **Pour les développeurs Symfony :** cette page ne réinvente rien — elle expose directement `EventDispatcherInterface` et l'attribut `#[AsEventListener]` de Symfony. Si vous connaissez déjà l'écosystème d'événements Symfony, vous pouvez appliquer vos connaissances telles quelles ici.
>
> **Pour les développeurs Laravel :** la logique reste similaire dans l'esprit (événement + listeners découplés, dispatch via un helper ou un service), mais l'implémentation diverge sensiblement de la classe `Event`/`Listener` propre à Laravel — attendez-vous à une syntaxe Symfony (attributs `#[AsEventListener]`, `EventDispatcherInterface`) plutôt qu'à `Event::dispatch()`.

---

## Définition d'un Événement

### Création d'un Événement

```bash
php tonka make:event UserRegistered
```

### Structure d'un Événement

```php
namespace App\Events;

class UserRegistered
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

Un événement Tonka est un simple objet immuable (aucune dépendance imposée, pas de classe de base à étendre) — il transporte les données pertinentes au moment où le fait s'est produit, sans logique propre.

---

## Listeners

### Création d'un Listener

```bash
php tonka make:event-listener SendWelcomeEmailListener UserRegistered
```

### Enregistrement avec Attributs

```php
namespace App\Listeners;

use App\Events\UserRegistered;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: UserRegistered::class, priority: 10)]
class SendWelcomeEmailListener
{
    public function __construct(
        private MailerInterface $mailer
    ) {}

    public function __invoke(UserRegistered $event): void
    {
        $this->mailer->to($event->getEmail())
            ->send(new WelcomeEmail($event->getName()));
    }
}
```

L'attribut `#[AsEventListener]` suffit à enregistrer le listener — aucune déclaration manuelle dans un fichier de configuration n'est nécessaire, le conteneur de services découvre et relie automatiquement la classe à l'événement ciblé.

### Priorité

```php
// Priorité la plus haute (exécuté en premier)
#[AsEventListener(event: UserRegistered::class, priority: 100)]

// Priorité par défaut (0)
#[AsEventListener(event: UserRegistered::class)]

// Priorité la plus basse (exécuté en dernier)
#[AsEventListener(event: UserRegistered::class, priority: -100)]
```

---

## Dispatch d'un Événement

### Via le Dispatcher

```php
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class UserController extends Controller
{
    public function __construct(
        private EventDispatcherInterface $dispatcher
    ) {}

    public function store(Request $request)
    {
        $user = User::create($request->validated());

        // Dispatch de l'événement
        $this->dispatcher->dispatch(
            new UserRegistered($user->id, $user->email, $user->name)
        );

        return redirect('/users');
    }
}
```

### Via le Helper

```php
// Dispatch d'un événement via le helper
event(new UserRegistered($user->id, $user->email, $user->name));

// Le helper retourne l'événement dispatché
$event = event(new UserRegistered($user->id, $user->email, $user->name));
```

Le helper `event()` évite d'injecter `EventDispatcherInterface` dans chaque classe ayant besoin de déclencher un événement ponctuellement — préférez néanmoins l'injection explicite du dispatcher dans un contrôleur ou un service dont le dispatch d'événements constitue une responsabilité centrale, pour rester testable (un mock d'interface s'injecte plus proprement qu'un helper global).

---

## Événements Broadcastés

### Définition

```php
namespace App\Broadcasting;

use Broadcaster\Event\ShouldBroadcastInterface;

class UserRegistered implements ShouldBroadcastInterface
{
    public function __construct(
        private int $userId,
        private string $email,
        private string $name
    ) {}

    // Canaux de diffusion
    public function broadcastOn(): array
    {
        return [
            'user.' . $this->userId,
            'admin.users'
        ];
    }

    // Nom de l'événement (frontend)
    public function broadcastAs(): string
    {
        return 'user.registered';
    }

    // Données à diffuser
    public function broadcastWith(): array
    {
        return [
            'user_id' => $this->userId,
            'email' => $this->email,
            'name' => $this->name,
            'registered_at' => now()->toDateTimeString()
        ];
    }

    // Condition de diffusion
    public function broadcastWhen(): bool
    {
        return true;
    }

    // Getters
    public function getUserId(): int { return $this->userId; }
    public function getEmail(): string { return $this->email; }
    public function getName(): string { return $this->name; }
}
```

`broadcastWhen()` permet de désactiver conditionnellement la diffusion sans annuler le dispatch de l'événement lui-même — utile, par exemple, pour ne diffuser un événement qu'en production, ou seulement lorsque certaines données ont réellement changé.

### Canaux de Broadcast

```php
// Canal public
public function broadcastOn(): array
{
    return ['admin.users'];
}

// Canal privé (préfixé par "private.")
public function broadcastOn(): array
{
    return ['private.user.' . $this->userId];
}

// Canaux multiples
public function broadcastOn(): array
{
    return [
        'admin.users',
        'private.user.' . $this->userId,
        'notifications'
    ];
}
```

Le préfixe `private.` n'est qu'une convention de nommage cosmétique : côté serveur, seul le token d'autorisation doit être vérifiée avant qu'un client puisse s'abonner à un canal privé.

### Données de Broadcast

```php
// Données par défaut (toutes les propriétés publiques)
public function broadcastWith(): array
{
    return [
        'user_id' => $this->userId,
        'email' => $this->email
    ];
}

// Données personnalisées
public function broadcastWith(): array
{
    return [
        'id' => $this->userId,
        'email' => $this->email,
        'name' => $this->name,
        'timestamp' => time()
    ];
}
```

> **Point d'attention sécurité :** `broadcastWith()` définit explicitement ce qui est envoyé au client — ne retournez jamais l'objet événement entier ou ses propriétés sans filtrage, au risque d'exposer des données sensibles (mot de passe haché, jetons internes) à tout abonné du canal.

---

## Listeners avec Méthode Personnalisée

```php
namespace App\Listeners;

use App\Events\UserRegistered;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: UserRegistered::class, method: 'handle')]
class AuditUserRegistrationListener
{
    public function handle(UserRegistered $event): void
    {
        // Log dans un fichier d'audit
        file_put_contents(
            storage_path('logs/audit.log'),
            sprintf(
                "[%s] User registered: %s (%s)\n",
                now()->toDateTimeString(),
                $event->getEmail(),
                $event->getUserId()
            ),
            FILE_APPEND
        );
    }
}
```

Le paramètre `method` permet de nommer explicitement la méthode invoquée, pour les listeners n'utilisant pas la convention `__invoke()` — utile lorsqu'une même classe écoute plusieurs événements différents via plusieurs méthodes distinctes.

---

## Exemple Complet

### 1. Événement

```php
namespace App\Events;

use Broadcaster\Event\ShouldBroadcastInterface;

class OrderShipped implements ShouldBroadcastInterface
{
    public function __construct(
        private int $orderId,
        private string $status,
        private array $orderData = []
    ) {}

    public function broadcastOn(): array
    {
        return [
            'private.order.' . $this->orderId,
            'admin.orders'
        ];
    }

    public function broadcastAs(): string
    {
        return 'order.shipped';
    }

    public function broadcastWith(): array
    {
        return [
            'order_id' => $this->orderId,
            'status' => $this->status,
            'data' => $this->orderData,
            'shipped_at' => now()->toDateTimeString()
        ];
    }

    public function getOrderId(): int { return $this->orderId; }
    public function getStatus(): string { return $this->status; }
}
```

### 2. Listeners

```php
// Listener 1 : mise à jour du statut
#[AsEventListener(event: OrderShipped::class, priority: 10)]
class UpdateOrderStatusListener
{
    public function __invoke(OrderShipped $event): void
    {
        Order::find($event->getOrderId())
            ->update(['status' => $event->getStatus()]);
    }
}

// Listener 2 : notification client
#[AsEventListener(event: OrderShipped::class, priority: 20)]
class NotifyCustomerListener
{
    public function __invoke(OrderShipped $event): void
    {
        $order = Order::find($event->getOrderId());
        Notification::send($order->customer, new OrderShippedNotification($order));
    }
}

// Listener 3 : broadcast automatique
#[AsEventListener(event: OrderShipped::class, priority: 30)]
class BroadcastOrderShippedListener
{
    public function __invoke(OrderShipped $event): void
    {
        // Le broadcasting est automatique via ShouldBroadcastInterface
        // Ce listener peut être utilisé pour des traitements supplémentaires
    }
}
```

> Les trois listeners s'exécutent dans l'ordre de leur priorité croissante (10, puis 20, puis 30) — la mise à jour du statut en base a donc lieu avant la notification du client, elle-même avant tout traitement additionnel lié au broadcast.

### 3. Contrôleur

```php
namespace App\Http\Controllers;

use App\Events\OrderShipped;
use App\Models\Order;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class OrderController extends Controller
{
    public function __construct(
        private EventDispatcherInterface $dispatcher
    ) {}

    public function ship(int $orderId)
    {
        $order = Order::find($orderId);
        $order->status = 'shipped';
        $order->save();

        // Dispatch de l'événement
        $this->dispatcher->dispatch(
            new OrderShipped(
                $order->id,
                'shipped',
                $order->toArray()
            )
        );

        return response()->json([
            'success' => true,
            'message' => 'Order shipped and broadcasted!'
        ]);
    }
}
```

### 4. Frontend (Réception)

```typescript
import TonkaBroadcaster from 'tonka-broadcaster';

const broadcaster = new TonkaBroadcaster({
    hubUrl: 'http://localhost:3000/.well-known/mercure',
    token: () => localStorage.getItem('tonka_jwt_token')
});

// Écoute des commandes expédiées (admin)
broadcaster.channel('admin.orders')
    .listen('order.shipped', (data) => {
        console.log('Order shipped:', data);
        refreshOrderList();
    });

// Écoute d'une commande spécifique (client)
broadcaster.private(`order.${orderId}`)
    .listen('order.shipped', (data) => {
        console.log('Your order has been shipped!');
        updateOrderStatus(data);
    });
```

---

## Prochaines Étapes

Maintenant que vous maîtrisez les événements :

1. **[Messenger](messenger.md)** → Système de queues asynchrones
2. **[Broadcasting](broadcasting.md)** → Communication temps réel
3. **[Task Scheduling](tasks.md)** → Tâches planifiées

---

**Événements compris !** 🚀 → [Messenger](messenger.md)