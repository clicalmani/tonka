# Les Queues

- [Introduction](#introduction)
- [Installation](#installation)
- [Configuration](#configuration)
  - [Fichier de configuration](#fichier-de-configuration)
  - [Variables d'environnement](#variables-d-39-environnement)
  - [Enregistrement du provider](#enregistrement-du-provider)
  - [Migration de la table jobs](#migration-de-la-table-jobs)
- [Concepts](#concepts)
  - [Pourquoi une queue ?](#pourquoi-une-queue-)
  - [Architecture](#architecture)
  - [Cycle de vie d'un job](#cycle-de-vie-dun-job)
- [Utilisation de base](#utilisation-de-base)
  - [Marquer une notification comme queuable](#marquer-une-notification-comme-queuable)
  - [Envoyer une notification en queue](#envoyer-une-notification-en-queue)
  - [Envoyer une notification différée](#envoyer-une-notification-differee)
  - [Forcer l'envoi synchrone](#forcer-l-39-envoi-synchrone)
- [Drivers](#drivers)
  - [SyncQueueDriver](#syncqueuedriver)
  - [DatabaseQueueDriver](#databasequeuedriver)
  - [RedisQueueDriver](#redisqueuedriver)
  - [Écrire un driver personnalisé](#ecrire-un-driver-personnalise)
- [Options d'une notification queuée](#options-d-39-une-notification-queuee)
  - [Queue cible](#queue-cible)
  - [Nombre de tentatives](#nombre-de-tentatives)
  - [Backoff](#backoff)
  - [Timeout](#timeout)
- [Le Worker](#le-worker)
  - [Lancer un worker](#lancer-un-worker)
  - [Options du worker](#options-du-worker)
  - [Supervision en production](#supervision-en-production)
  - [Arrêt propre](#arret-propre)
  - [Redéploiement](#redeploiement)
- [Événements de queue](#evenements-de-queue)
- [Observabilité](#observabilite)
- [Référence API](#reference-api)
- [Bonnes pratiques](#bonnes-pratiques)
- [FAQ](#faq)

---

## Introduction

Le sous-système **Queue** de Tonka permet de différer l'exécution de tâches longues ou coûteuses en arrière-plan. Il est principalement utilisé pour les **notifications** (voir [Mail & Notifications](mail-notifications.md)), mais son architecture est générique et peut être étendue à d'autres types de jobs.

Trois cas d'usage typiques :

1. **Découpler la réponse HTTP du travail réel.** Un utilisateur s'inscrit, vous voulez envoyer un email de bienvenue — mais pas attendre 500 ms de SMTP avant de répondre. La notification est mise en queue, la réponse part immédiatement, le worker envoie l'email en arrière-plan.

2. **Absorber les pics de charge.** Black Friday : 10 000 commandes/heure. Les notifications de confirmation sont mises en queue et traitées à un rythme contrôlé.

3. **Retenter automatiquement en cas d'échec.** Le fournisseur SMTP tombe ? Le job est retenté 3 fois avec un backoff progressif avant d'être marqué comme échoué.

La queue est **optionnelle** : une notification sans `ShouldQueue` s'exécute de manière synchrone. Vous choisissez, canal par canal, notification par notification.

> **Pour les développeurs Laravel :** ce sous-système occupe exactement le rôle des Jobs/Queues de Laravel, avec la même philosophie opt-in via une interface marqueur (`ShouldQueue`).

---

## Installation

Le sous-système Queue est fourni par le package `clicalmani/queue`. Aucune dépendance externe n'est requise pour les drivers `sync` et `database`.

Pour le driver `redis`, l'extension `redis` de PHP est nécessaire :

```bash
pie install phpredis/phpredis

# Alternative pure PHP, sans extension native
composer require predis/predis
```

Publiez la configuration :

```bash
php tonka vendor:publish queue
```

Cela crée `config/queue.php`.

---

## Configuration

### Fichier de configuration

`config/queue.php` contrôle le comportement global :

```php
return [

    /*
    |--------------------------------------------------------------------------
    | Default Queue Connection
    |--------------------------------------------------------------------------
    |
    | Supported: "sync", "database", "redis"
    |
    */

    'connection' => env('QUEUE_CONNECTION', 'sync'),

    /*
    |--------------------------------------------------------------------------
    | Queue Connections
    |--------------------------------------------------------------------------
    */

    'connections' => [

        /*
        |----------------------------------------------------------------------
        | Sync Driver
        |----------------------------------------------------------------------
        |
        | Executes jobs inline. Useful for local development and testing.
        | No persistence — jobs are executed the moment they are pushed.
        |
        */

        'sync' => [
            'driver' => 'sync',
        ],

        /*
        |----------------------------------------------------------------------
        | Database Driver
        |----------------------------------------------------------------------
        |
        | Stores jobs in a database table. Simple and reliable — no external
        | infrastructure required. Requires the `jobs` table.
        |
        */

        'database' => [
            'driver' => 'database',
            'table'  => env('QUEUE_TABLE', 'jobs'),
            'queue'  => env('QUEUE_DEFAULT', 'default'),
        ],

        /*
        |----------------------------------------------------------------------
        | Redis Driver
        |----------------------------------------------------------------------
        |
        | Uses Redis lists and sorted sets. Lowest latency, highest
        | throughput. Recommended for production at scale.
        |
        */

        'redis' => [
            'driver'   => 'redis',
            'scheme'   => env('REDIS_SCHEME', 'tcp'),
            'host'     => env('REDIS_HOST', '127.0.0.1'),
            'port'     => env('REDIS_PORT', 6379),
            'username' => env('REDIS_USERNAME', null),
            'password' => env('REDIS_PASSWORD', null),
            'database' => env('REDIS_DB', 0),
            'prefix'   => env('REDIS_PREFIX', 'queue:'),
            'queue'    => env('QUEUE_DEFAULT', 'default'),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Worker Defaults
    |--------------------------------------------------------------------------
    */

    'worker' => [

        /*
        | Queues to process, in order of priority.
        */

        'queues' => explode(',', env('QUEUE_WORKER_QUEUES', 'default')),

        /*
        | Seconds to sleep when no job is available.
        */

        'sleep' => env('QUEUE_WORKER_SLEEP', 1),

        /*
        | Maximum memory (MB) before the worker exits gracefully.
        */

        'memory' => env('QUEUE_WORKER_MEMORY', 128),

        /*
        | Maximum job timeout (seconds) enforced by the worker.
        */

        'timeout' => env('QUEUE_WORKER_TIMEOUT', 60),

    ],

];
```

### Variables d'environnement
<div id="variables-denvironnement"></div>

```env
# ----------------------------------------------------------------------------
# Queue
# ----------------------------------------------------------------------------

# Connection: sync | database | redis
QUEUE_CONNECTION=database

# Database driver
QUEUE_TABLE=jobs
QUEUE_DEFAULT=default

# Redis driver
QUEUE_PREFIX=queue:

# Worker defaults
QUEUE_WORKER_QUEUES=default
QUEUE_WORKER_SLEEP=1
QUEUE_WORKER_MEMORY=128
QUEUE_WORKER_TIMEOUT=60

# -------------------------------------------------------------------------
# REDIS
# -------------------------------------------------------------------------

# Connexion
REDIS_SCHEME=tcp
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# Authentification (optionnel)
REDIS_USERNAME=
REDIS_PASSWORD=

# Base de données (0-15 par défaut)
REDIS_DB=0

# Préfixe appliqué à toutes les clés (optionnel)
REDIS_PREFIX=myapp:
```

### Enregistrement du provider

```php
// bootstrap/app.php
return \Clicalmani\Core\Maker\Application::setup(rootPath: dirname(__DIR__))
        ->withQueue();
```

Le provider expose un singleton `queue` dans le conteneur et câble le driver approprié.

### Migration de la table jobs

Pour le driver `database`, créez la table `jobs` :

```bash
php tonka queue:migrate
```

---

## Concepts

### Pourquoi une queue ?

Sans queue, une notification est **synchrone** :

```
Requête HTTP ────▶ notify() ────▶ mail.send()  ────▶ Réponse HTTP
                                  142 ms
                        Total : 142 ms ajoutés à la réponse
```

Avec queue :

```
Requête HTTP ────▶ notify() ────▶ push en queue ────▶ Réponse HTTP
                                    2 ms
                        Total : 2 ms ajoutés à la réponse

                     [worker en arrière-plan]
                     pull ────▶ mail.send()  ────▶ OK
                                  142 ms (hors requête)
```

L'utilisateur ne paie plus la latence d'envoi.

### Architecture

```
┌────────────────────┐
│  Application       │
│  $user->notify()   │
└──────────┬─────────┘
           │
           ▼
┌────────────────────┐
│  NotificationJob   │   ← unité de travail sérialisable
│  - notification    │
│  - notifiable      │
│  - options         │
└──────────┬─────────┘
           │
           ▼
┌────────────────────┐
│  QueueManager      │   ← orchestrateur
└──────────┬─────────┘
           │
           ├──▶ SyncQueueDriver     (exécute inline)
           ├──▶ DatabaseQueueDriver (INSERT jobs)
           └──▶ RedisQueueDriver    (LPUSH queue)
                     │
                     ▼
┌────────────────────────────────────┐
│  Worker                            │
│  php tonka queue:work              │
│                                    │
│  while true:                       │
│     $job = driver->pop()           │
│     $job->handle()                 │
│     driver->delete($job)           │
└────────────────────────────────────┘
```

Trois couches, trois responsabilités :

| Couche | Rôle |
|---|---|
| **NotificationJob** | Porte la notification et le notifiable, sait se sérialiser |
| **QueueManager** | Décide quel driver utiliser, gère push/pop/retry/fail |
| **Worker** | Boucle infinie de consommation |

### Cycle de vie d'un job
<div id="cycle-de-vie-dun-job"></div>

```
1. PUSH
   Application ─▶ NotificationJob ─▶ driver.push()
   → event(JobPushed)

2. POP
   Worker ─▶ driver.pop()
   → le job est marqué "reserved"

3. HANDLE
   Worker ─▶ job.handle() ─▶ NotificationDispatcher::dispatchNow()
   → notification exécutée sur tous ses canaux

4. SUCCESS
   Worker ─▶ driver.delete($id)
   → event(JobProcessed)

   OU

4'. FAILURE
   Worker ─▶ attempts < tries ?
   ├─ OUI ─▶ driver.release($id, backoff)
   └─ NON ─▶ driver.fail($id, error) + job.failed()
             → event(JobFailed)
```

**Important** : quand le worker exécute `job.handle()`, il appelle `NotificationDispatcher::dispatchNow()` — pas `dispatch()`. Sinon, la notification serait re-mise en queue à l'infini.

---

## Utilisation de base

### Marquer une notification comme queuable

Une notification devient queuable en implémentant `ShouldQueue`, en plus de `NotificationInterface` (voir [Mail & Notifications](mail-notifications.md#structure-dune-notification)) :

```php
namespace App\Notifications;

use App\Models\Order;
use Clicalmani\Database\Notifications\NotificationInterface;
use Clicalmani\Queue\Contracts\ShouldQueue;

class OrderShipped implements NotificationInterface, ShouldQueue
{
    public function __construct(
        protected Order $order,
    ) {}

    public function via(): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    // Options de queue (voir note ci-dessous sur leur caractère réellement optionnel)
    public function queue(): string     { return 'notifications'; }
    public function tries(): int        { return 3; }
    public function backoff(): array    { return [10, 30, 60]; }
    public function timeout(): int      { return 30; }

    // ... toMail, toDatabase, toBroadcast
}
```

Sans `ShouldQueue`, la notification est synchrone. **Vous choisissez notification par notification.**

### Envoyer une notification en queue

L'appel est **identique** à une notification synchrone :

```php
$user->notify(new OrderShipped($order));
```

Le dispatcher détecte `ShouldQueue` et bascule automatiquement en mode queue.

Via la façade :

```php
use Clicalmani\Core\Support\Facades\Notification;

Notification::send($user, new OrderShipped($order));
// ou
Notification::send([$user1, $user2], new OrderShipped($order));
```

### Envoyer une notification différée

Pour différer l'exécution (utile pour les rappels, follow-ups) :

```php
// Dans 5 minutes (300 secondes)
$user->notifyLater(new OrderShipped($order), delay: 300);
```

Ou via la façade :

```php
Notification::sendLater($user, new OrderShipped($order), delay: 300);
```

Le job est inséré avec `available_at = now() + delay`. Le worker ne le verra qu'à ce moment-là.

### Forcer l'envoi synchrone
<div id="forcer-lenvoi-synchrone"></div>

Parfois, une notification `ShouldQueue` doit partir **tout de suite** — par exemple, une alerte critique qui doit partir même si le worker est down :

```php
$user->notifyNow(new CriticalAlert($server));
```

Ou via la façade :

```php
Notification::sendNow($user, new CriticalAlert($server));
```

`notifyNow()` appelle `dispatchNow()` directement, sans passer par la queue.

---

## Drivers

### SyncQueueDriver

Exécute les jobs **inline**, sans persistance. Le push déclenche immédiatement l'exécution.

```php
use Clicalmani\Queue\Drivers\SyncQueueDriver;

queue()->setDriver(new SyncQueueDriver());

$user->notify(new OrderShipped($order));
// → Exécution immédiate, synchrone
```

**Cas d'usage** : développement local, tests unitaires.

**Avantages** : aucun setup, aucune infra, debugging facile (on suit le flux normalement).

**Inconvénients** : pas de queue réelle — la latence s'ajoute à la requête.

### DatabaseQueueDriver

Persiste les jobs dans une table SQL. Le worker les consomme de façon asynchrone.

```php
use Clicalmani\Queue\Drivers\DatabaseQueueDriver;

queue()->setDriver(new DatabaseQueueDriver('jobs'));
```

**Cas d'usage** : production simple, sans Redis.

**Avantages** : aucune infra externe, fiabilité SQL, inspection facile (`SELECT * FROM jobs`).

**Inconvénients** : performance limitée par la DB (mais largement suffisant jusqu'à ~100 jobs/s).

**Fonctionnement** : un job est « réservé » via `UPDATE ... SET reserved_at = NOW()` dans une transaction, ce qui empêche plusieurs workers de le consommer en double.

### RedisQueueDriver

Utilise Redis pour une latence minimale et un débit élevé.

```php
use Clicalmani\Queue\Drivers\RedisQueueDriver;

queue()->setDriver(new RedisQueueDriver($redis, 'queue:'));
```

**Cas d'usage** : production à fort débit.

**Avantages** : latence sous-milliseconde, millions de jobs/jour, atomicité native.

**Inconvénients** : dépendance Redis.

**Fonctionnement** : les jobs immédiats vont dans une **liste** (`LPUSH`/`RPOP`), les jobs différés dans un **sorted set** (`ZADD` sur timestamp). Le worker déplace les jobs dus vers la liste avant chaque pop.

### Écrire un driver personnalisé

Implémentez `QueueDriver` :

```php
namespace App\Queue\Drivers;

use Clicalmani\Queue\Contracts\QueueDriver;

final class SqsQueueDriver implements QueueDriver
{
    public function push(string $queue, string $payload, int $delay = 0): string
    {
        // Envoi vers AWS SQS
    }

    public function pop(string $queue): ?array
    {
        // Réception depuis SQS
    }

    public function delete(string $id): void {}
    public function release(string $id, int $delay = 0): void {}
    public function fail(string $id, string $reason): void {}
    public function size(string $queue): int { return 0; }
}
```

Puis :

```php
queue()->setDriver(new SqsQueueDriver());
```

---

## Options d'une notification queuée
<div id="options-dune-notification-queuée"></div>

Toutes ces méthodes sont optionnelles - des valeurs par défaut s'appliquent alors si elles ne sont pas redéfinies.

### Queue cible

```php
public function queue(): string
{
    return 'notifications-high';
}
```

**Défaut** : `'default'`.

**Usage** : séparer les notifications critiques (haute priorité) des notifications marketing (basse priorité). Le worker peut traiter plusieurs queues dans l'ordre :

```bash
php tonka queue:work --queue=notifications-high,notifications-low,default
```

Les jobs de `notifications-high` sont toujours traités en premier.

### Nombre de tentatives

```php
public function tries(): int
{
    return 3;
}
```

**Défaut** : `3`.

**Usage** : combien de fois le job peut être rejoué après échec. Après `tries`, le job est marqué `failed` et `job->failed()` est appelé.

### Backoff

```php
public function backoff(): array
{
    return [10, 30, 60];
}
```

Ou un délai fixe :

```php
public function backoff(): int
{
    return 30;
}
```

**Défaut** : `5` secondes.

**Usage** : délai entre les tentatives. Un tableau permet un backoff progressif — la 1ʳᵉ retentative attend 10 s, la 2ᵉ 30 s, la 3ᵉ 60 s. Le backoff progressif laisse le temps à un service externe de se rétablir avant de réessayer.

### Timeout

```php
public function timeout(): int
{
    return 30;
}
```

**Défaut** : `30` secondes.

**Usage** : durée maximale d'exécution d'un job. Au-delà, le job est tué et marqué comme échoué.

---

## Le Worker

### Lancer un worker

```bash
php tonka queue:work
```

Par défaut, le worker consomme la queue `default`.

### Options du worker

```bash
php tonka queue:work \
    --queue=notifications-high,default \
    --sleep=1 \
    --memory=128 \
    --timeout=60
```

| Option | Description | Défaut |
|---|---|---|
| `--queue` | Queues à consommer, dans l'ordre de priorité | `default` |
| `--sleep` | Secondes d'attente quand la queue est vide | `1` |
| `--memory` | Mémoire max (MB) avant arrêt gracieux | `128` |
| `--timeout` | Timeout par job en secondes | `60` |
| `--once` | Traite un seul job puis sort | — |
| `--stop-when-empty` | Sort quand la queue est vide | — |

**`--once`** est utile pour un cron (un job par minute par exemple).

**`--stop-when-empty`** est utile pour les scripts qui veulent vider la queue puis rendre la main.

### Supervision en production

Un worker qui crash doit être redémarré automatiquement. Utilisez **systemd** ou **supervisord**.

#### Exemple systemd

`/etc/systemd/system/tonka-worker.service` :

```ini
[Unit]
Description=Tonka Queue Worker
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/myapp
ExecStart=/usr/bin/php /var/www/myapp/tonka queue:work --sleep=1 --memory=128
Restart=always
RestartSec=5
StandardOutput=append:/var/log/tonka-worker.log
StandardError=append:/var/log/tonka-worker.err

[Install]
WantedBy=multi-user.target
```

Puis :

```bash
sudo systemctl enable tonka-worker
sudo systemctl start tonka-worker
sudo systemctl status tonka-worker
```

#### Exemple supervisord

`/etc/supervisor/conf.d/tonka-worker.conf` :

```ini
[program:tonka-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/myapp/tonka queue:work --sleep=1
directory=/var/www/myapp
autostart=true
autorestart=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/var/log/tonka-worker.log
stopwaitsecs=3600
```

**`numprocs=4`** lance 4 workers en parallèle pour traiter plusieurs jobs simultanément.

### Arrêt propre

Le worker intercepte `SIGTERM` et `SIGINT` pour finir le job en cours avant de sortir :

```php
pcntl_signal(SIGTERM, fn() => $this->stop());
pcntl_signal(SIGINT,  fn() => $this->stop());
```

Envoyez `SIGTERM` (ou `Ctrl+C`) pour un arrêt propre. Le worker termine le job courant, puis s'arrête.

**Ne tuez jamais un worker avec `kill -9`** — vous risquez de laisser un job « réservé » bloqué indéfiniment (en DB) ou perdu (Redis).

### Redéploiement

Après un déploiement, redémarrez les workers pour qu'ils chargent le nouveau code :

```bash
sudo systemctl restart tonka-worker
```

Ou avec `queue:restart` :

```bash
php tonka queue:restart
```

Cette commande envoie un signal aux workers pour qu'ils sortent après leur job courant — à distinguer de `queue:kill` (voir [FAQ](#faq)), qui arrête un worker sans intention de le relancer immédiatement.

---

## Événements de queue

Trois événements permettent d'observer le cycle de vie :

| Événement | Émis quand | Propriétés |
|---|---|---|
| `JobPushed` | Un job est poussé dans la queue | `id`, `queue`, `notification`, `notifiable` |
| `JobProcessed` | Un job a été traité avec succès | `id`, `queue` |
| `JobFailed` | Un job a définitivement échoué | `job`, `exception` |

Exemple d'écoute :

```php
use Clicalmani\Queue\Events\{
    JobPushed,
    JobProcessed,
    JobFailed,
};

Event::listen(JobPushed::class, function (JobPushed $e) {
    logger()->info("Job pushed to {$e->queue}", [
        'notification' => $e->notification::class,
    ]);
});

Event::listen(JobProcessed::class, function (JobProcessed $e) {
    // ...
});

Event::listen(JobFailed::class, function (JobFailed $e) {
    logger()->error("Job failed", [
        'error' => $e->exception->getMessage(),
    ]);
});
```

---

## Observabilité

Comme pour HTTP et Database (voir [Metrics](metrics.md#auto-instrumentation)), la queue produit des métriques via le pattern **événement → listener → métriques**.

Activez l'auto-instrumentation :

```env
METRICS_AUTO_QUEUE=true
```

Résultat :

| Métrique | Type | Tags |
|---|---|---|
| `queue.job.pushed` | Counter | queue, job |
| `queue.job.processed` | Counter | queue |
| `queue.job.failed` | Counter | queue, error |

Ces trois métriques répondent aux questions :

| Question | Métrique |
|---|---|
| Combien de jobs sont poussés par minute ? | `queue.job.pushed` |
| Combien sont traités avec succès ? | `queue.job.processed` |
| Combien échouent ? | `queue.job.failed` |
| Quel type de notification est la plus fréquente ? | `queue.job.pushed` group by job |
| Quelle est la taille de la queue ? | `queue()->size()` (à échantillonner) |

**Signal RED** :
- **R**ate : `queue.job.pushed` / `queue.job.processed`
- **E**rrors : `queue.job.failed`
- **D**uration : (à ajouter via un `queue.job.duration` si besoin)

### Santé de la queue

Pour un dashboard, exposez régulièrement la taille :

```php
// Dans un scheduler, toutes les minutes
metrics()->gauge('queue.size', queue()->size('default'), tags: ['queue' => 'default']);
metrics()->gauge('queue.size', queue()->size('notifications'), tags: ['queue' => 'notifications']);
```

Une queue qui grossit sans cesse indique un worker down ou sous-dimensionné.

---

## Référence API

### `ShouldQueue` (interface)

```php
interface ShouldQueue
{
    public function queue(): string;
    public function tries(): int;
    public function backoff(): int|array;
    public function timeout(): int;
}
```

### `QueueDriver` (interface)

```php
interface QueueDriver
{
    public function push(string $queue, string $payload, int $delay = 0): string;
    public function pop(string $queue): ?array;
    public function delete(string $id): void;
    public function release(string $id, int $delay = 0): void;
    public function fail(string $id, string $reason): void;
    public function size(string $queue): int;
}
```

### Trait `Notifiable`

| Méthode | Description |
|---|---|
| `notify(object $event): void` | Envoi standard (respecte `ShouldQueue`) |
| `notifyNow(object $event): void` | Envoi synchrone forcé |
| `notifyLater(object $event, int $delay): void` | Envoi différé |

### Helper `queue()`

```php
function queue(): \Clicalmani\Queue\QueueManager;
```

### Facade `Queue`

```php
Queue::push($job);
Queue::size('default');
Queue::assertPushed(fn($job) => ...);   // en test
```

---

## Bonnes pratiques

### Toujours mettre les notifications lentes en queue

Les canaux lents — `mail`, `sms`, APIs tierces — doivent toujours être queués. Les canaux rapides — `database`, `broadcast` — peuvent rester synchrones.

Pour un mix, utilisez `sendNow()` sur un canal critique et queue pour le reste :

```php
$user->notifyNow(new CriticalAlert($server));   // immédiat
$user->notify(new OrderShipped($order));        // queue
```

### Nommer les queues par priorité

- `notifications-critical` — alertes, sécurité
- `notifications-high` — transactionnel (email de commande)
- `notifications-default` — notifications in-app
- `notifications-low` — marketing, newsletters

Puis lancez plusieurs workers :

```bash
php tonka queue:work --queue=notifications-critical,notifications-high
php tonka queue:work --queue=notifications-low
```

### Superviser la taille des queues

Une queue qui grossit est un signal :

- Worker down
- Charge anormale
- Job bloqué

Exposez `queue.size` en gauge et créez une alerte si la valeur dépasse un seuil.

### Ne jamais bloquer un worker

Un job qui attend un service externe down bloque le worker pour les autres jobs. Utilisez des timeouts courts et un backoff pour libérer rapidement.

### Redémarrer après déploiement

Le worker charge le code au démarrage. Après un déploiement, redémarrez-les pour éviter que du vieux code ne tourne.

```bash
php tonka queue:restart
```

---

## FAQ

### Comment lancer le worker en développement ?

```bash
php tonka queue:work
```

Ou avec le driver `sync` pour tout exécuter immédiatement :

```env
QUEUE_CONNECTION=sync
```

### Pourquoi mes notifications partent-elles immédiatement malgré `ShouldQueue` ?

Trois causes possibles :

1. `QUEUE_CONNECTION=sync` dans `.env` — le driver sync exécute inline.
2. La notification n'implémente pas `ShouldQueue`.
3. Vous appelez `notifyNow()` au lieu de `notify()`.

### Comment déboguer un job qui échoue ?

```bash
php tonka queue:failed:show
```

Puis regardez les logs — `job->failed()` y écrit un message structuré.

### Comment rejouer un job échoué ?

```bash
php tonka queue:failed:retry 123
```

### Comment gérer 10 000 jobs/heure ?

Trois leviers :

1. **Augmenter le nombre de workers** (systemd `numprocs=4`, ou supervisord avec plusieurs programmes).
2. **Passer au driver Redis** — latence sous-milliseconde vs quelques ms pour la DB.
3. **Paralléliser les canaux** — un job par canal plutôt qu'un job par notification (nécessite un changement dans le dispatcher).

### Peut-on utiliser la queue pour autre chose que des notifications ?

Oui, l'architecture est générique. `NotificationJob` porte spécifiquement une notification, mais on peut créer d'autres types de jobs :

```php
class SendWeeklyReportJob
{
    public function handle(): void
    {
        // ...
    }
}

queue()->push(new SendWeeklyReportJob());
```

Le contrat `QueueDriver` est indépendant des notifications.

### Comment migrer de `sync` à `database` en production ?

1. Créez la table `jobs` (`php tonka migrate:entity Job`).
2. Changez `QUEUE_CONNECTION=database` dans `.env`.
3. Démarrez un worker (`php tonka queue:work`).
4. Vérifiez que les jobs sont bien traités (`SELECT COUNT(*) FROM jobs`).

Aucun changement de code nécessaire — le `QueueManager` s'adapte automatiquement.

> **Bug corrigé :** le document source mentionnait ici un `NotificationManager`, terme qui n'apparaît nulle part ailleurs sur cette page — c'est `QueueManager` (voir [Architecture](#architecture) ci-dessus) qui orchestre le choix du driver. Corrigé par cohérence interne.

### Comment arrêter proprement un worker ?

```bash
php tonka queue:kill
```

### Le worker consomme de la mémoire — comment gérer ?

PHP a une fuite mémoire naturelle en longue durée. Trois remèdes :

1. **Limiter la mémoire** : `--memory=128` (défaut). Le worker sort quand il atteint la limite et est redémarré par systemd.
2. **Limiter les jobs** : ajouter une option `--max-jobs=1000` pour sortir après N jobs.
3. **Redémarrer périodiquement** : avec systemd, `Restart=always` + un `RuntimeMaxSec=3600` force un redémarrage toutes les heures.

---

**Queues maîtrisées !** 🚀