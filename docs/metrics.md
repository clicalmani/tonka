## Métriques

- [Introduction](#introduction)
- [Installation](#installation)
- [Configuration](#configuration)
  - [Fichier de configuration](#fichier-de-configuration)
  - [Variables d'environnement](#variables-d-39-environnement)
  - [Activation du Service](#activation-du-service)
  - [Le Service Provider](#le-service-provider)
- [Concepts](#concepts)
  - [Les quatre types de métriques](#les-quatre-types-de-metriques)
  - [Tags](#tags)
  - [Drivers](#drivers)
- [Utilisation de base](#utilisation-de-base)
  - [Compteurs](#compteurs)
  - [Gauges](#gauges)
  - [Timings](#timings)
  - [Mesure automatique](#mesure-automatique)
  - [Tags contextuels](#tags-contextuels)
  - [Chaînage](#chainage)
- [Drivers](#drivers-1)
  - [InMemoryDriver](#inmemorydriver)
  - [LogDriver](#logdriver)
  - [StatsdDriver](#statsddriver)
  - [Écrire un driver personnalisé](#ecrire-un-driver-personnalise)
- [Sampling](#sampling)
- [Cardinality Guard](#cardinality-guard)
- [Flush](#flush)
- [Auto-instrumentation](#auto-instrumentation)
  - [HTTP](#http)
  - [Database](#database)
  - [Queue](#queue)
  - [Notifications](#notifications)
- [Profiling SQL](#profiling-sql)
- [Tests](#tests)
- [Référence API](#reference-api)
- [Bonnes pratiques](#bonnes-pratiques)

---

## Introduction

Le sous-système **Metrics** de Tonka fournit une API simple et performante pour collecter des mesures numériques sur le comportement de votre application : nombre de requêtes HTTP, latence des requêtes SQL, taux d'échec des notifications, taille des queues, etc.

Les métriques répondent à la question **« COMBIEN ? »** — combien d'événements, à quelle fréquence, avec quelle latence. Elles ne racontent pas l'histoire d'une requête (rôle des logs) ni son chemin (rôle des traces distribuées).

L'API minimale :

```php
metrics()->increment('api.requests');
metrics()->timing('db.query.duration', 12.4);
metrics()->gauge('queue.pending', 42);
```

Chaque appel est **synchrone** et **non-bloquant** (les drivers sont responsables de cette garantie). Aucune exception n'est levée par un appel de métrique — un problème d'infrastructure metrics ne doit jamais casser une requête métier.

> **Pour les développeurs Laravel/Symfony :** ce sous-système occupe la même niche que Laravel Pulse ou le Symfony Profiler couplé à StatsD/Prometheus — une API d'instrumentation légère, découplée du backend de visualisation (Datadog, Grafana...).

---

## Installation

Le sous-système Metrics est fourni par le package `clicalmani/metrics`. Aucune dépendance externe n'est requise pour les drivers `memory` et `log`.

Pour utiliser le driver `statsd`, aucune extension PHP particulière n'est nécessaire — les métriques sont envoyées par UDP via `stream_socket_client()`.

```bash
composer require clicalmani/metrics
```

Publiez la configuration si vous ne l'avez pas déjà :

```bash
php tonka vendor:publish metrics
```

Cela crée `config/metrics.php`.

---

## Configuration

### Fichier de configuration

`config/metrics.php` contrôle le comportement global :

```php
return [

    /*
    |--------------------------------------------------------------------------
    | Default Driver
    |--------------------------------------------------------------------------
    |
    | Supported: "memory", "log", "statsd"
    |
    */

    'driver' => env('METRICS_DRIVER', 'memory'),

    /*
    |--------------------------------------------------------------------------
    | Default Tags
    |--------------------------------------------------------------------------
    |
    | Tags applied to every metric. Keep cardinality low — avoid user IDs,
    | request IDs, or timestamps.
    |
    */

    'tags' => [
        'env'     => env('APP_ENV', 'production'),
        'host'    => gethostname() ?: 'unknown',
        'region'  => env('APP_REGION', 'local'),
        'version' => env('APP_VERSION', '1.0.0'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Drivers
    |--------------------------------------------------------------------------
    */

    'drivers' => [
        'memory' => [
            'driver' => 'memory',
        ],

        'log' => [
            'driver'  => 'log',
            'channel' => env('METRICS_LOG_CHANNEL', null),
            'level'   => env('METRICS_LOG_LEVEL', 'info'),
        ],

        'statsd' => [
            'driver' => 'statsd',
            'host'   => env('STATSD_HOST', '127.0.0.1'),
            'port'   => env('STATSD_PORT', 8125),
            'prefix' => env('STATSD_PREFIX', 'app.'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Sampling
    |--------------------------------------------------------------------------
    |
    | Percentage of events to capture. Patterns use `*` wildcards and are
    | evaluated in order. First match wins.
    |
    */

    'sampling' => [
        // 'http.request'    => 0.1,
        // 'db.query.*'      => 0.05,
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto-Instrumentation
    |--------------------------------------------------------------------------
    |
    | Emit metrics automatically for common framework events. Enable only
    | what you actually use.
    |
    */

    'auto' => [
        'http'          => env('METRICS_AUTO_HTTP', false),
        'database'      => env('METRICS_AUTO_DB', false),
        'queue'         => env('METRICS_AUTO_QUEUE', false),
        'notifications' => env('METRICS_AUTO_NOTIFICATIONS', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Flush Interval
    |--------------------------------------------------------------------------
    |
    | Seconds between automatic flushes of buffered drivers. 0 disables
    | periodic flushing (flush still happens on shutdown).
    |
    */

    'flush_interval' => env('METRICS_FLUSH_INTERVAL', 0),

    /*
    |--------------------------------------------------------------------------
    | Cardinality Limit
    |--------------------------------------------------------------------------
    |
    | Maximum unique tag combinations per metric key. Beyond this, new
    | combinations are dropped and a warning is logged. 0 disables.
    |
    */

    'cardinality_limit' => env('METRICS_CARDINALITY_LIMIT', 0),

    /*
    |--------------------------------------------------------------------------
    | Fake When Testing
    |--------------------------------------------------------------------------
    |
    | When true and APP_ENV=testing, the manager uses InMemoryDriver
    | regardless of the configured driver. Prevents metrics leaks.
    |
    */

    'fake_when_testing' => env('METRICS_FAKE_WHEN_TESTING', true),

];
```

### Variables d'environnement
<div id="variables-denvironnement"></div>

```env
# Driver: memory | log | statsd
METRICS_DRIVER=memory

# Default tags
APP_REGION=eu-west-1
APP_VERSION=1.0.0

# Log driver
METRICS_LOG_CHANNEL=metrics
METRICS_LOG_LEVEL=info

# StatsD driver
STATSD_HOST=127.0.0.1
STATSD_PORT=8125
STATSD_PREFIX=app.

# Auto-instrumentation
METRICS_AUTO_HTTP=true
METRICS_AUTO_DB=true
METRICS_AUTO_QUEUE=false
METRICS_AUTO_NOTIFICATIONS=true

# Advanced
METRICS_FLUSH_INTERVAL=10
METRICS_CARDINALITY_LIMIT=1000
METRICS_FAKE_WHEN_TESTING=true
```

### Activation du Service

Comme les autres services optionnels du framework (voir [Architecture](architecture.md#le-conteneur-de-services)), le sous-système Metrics s'active via le bootstrap fluide :

```php
// bootstrap/app.php

return \Clicalmani\Core\Maker\Application::setup(rootPath: dirname(__DIR__))
        ->withMetrics();
```

### Le Service Provider

`withMetrics()` active le service central (le manager et la façade `metrics()`/`Metrics`), mais l'auto-instrumentation et le flush de fin de requête restent à câbler explicitement via un Service Provider dédié — les deux étapes sont complémentaires, pas redondantes :

```php
namespace App\Providers;

use Clicalmani\Core\Support\Facades\Config;
use Clicalmani\Core\Support\Facades\Metrics;
use Clicalmani\Metrics\AutoInstrumentation;
use Clicalmani\Metrics\MetricsServiceProvider as BaseService;
use Override;

class MetricsServiceProvider extends BaseService
{
    #[Override]
    public function boot(): void
    {
        $config = array_combine(
            array_keys(config('metrics.auto', [])), 
            array_map(
                fn($key) => Config::boolean('metrics.auto.' . $key, false), 
                array_keys(config('metrics.auto', []))
            )
        );
        
        // Auto-instrumentation
        AutoInstrumentation::register($config);

        // Flush final à la fin de la requête (ou à l'arrêt du worker)
        register_shutdown_function(function () {
            try {
                Metrics::flush();
            } catch (\Throwable $e) {
                // Silencieux — on ne veut pas casser le shutdown
            }
        });
    }
}
```

Enregistrez `MetricsServiceProvider` dans `config/app.php` :

```php
// config/app.php

'providers' => [
    // ...
    \App\Providers\MetricsServiceProvider::class,
],
```

---

## Concepts

### Les quatre types de métriques

| Méthode | Type | Question à laquelle elle répond | Exemple |
|---|---|---|---|
| `increment()` | Counter | Combien de fois ? | Nombre de requêtes |
| `decrement()` | Counter | Combien de fois en moins ? | Places libres |
| `gauge()` | Gauge | Combien en ce moment ? | Taille de queue |
| `timing()` | Timing | Combien de temps ? | Latence |

Un **counter** est cumulatif et monotone (sauf `decrement`). Un **gauge** est une valeur instantanée qui peut monter et descendre. Un **timing** est une durée en millisecondes.

### Tags

Les tags segmentent les métriques. Ils transforment une métrique plate en une série multidimensionnelle :

```php
metrics()->increment('http.request', tags: ['method' => 'GET', 'status' => 200]);
```

Sans tags, vous savez seulement qu'il y a eu N requêtes. Avec tags, vous pouvez savoir combien de `GET` ont retourné `200`, combien de `POST` ont retourné `500`, etc.

**Attention à la cardinalité** : chaque combinaison unique de tags crée une nouvelle série. Évitez les tags à haute cardinalité (`user_id`, `request_id`, `timestamp`). Préférez les catégories (`env`, `region`, `channel`).

### Drivers

Un driver décide **où** vont les métriques :

| Driver | Destination | Cas d'usage |
|---|---|---|
| `memory` | RAM du processus | Dev, tests |
| `log` | Fichier de log | Prod sans infra |
| `statsd` | Agent StatsD via UDP | Prod avec Datadog, Telegraf |

Le driver actif peut être configuré à chaud :

```php
metrics()->setDriver(new \Clicalmani\Metrics\Drivers\InMemoryDriver());
```

---

## Utilisation de base

### Compteurs

```php
// Incrémenter de 1
metrics()->increment('api.requests');

// Incrémenter d'une valeur
metrics()->increment('api.requests', 5);

// Avec tags
metrics()->increment('api.requests', tags: [
    'method' => 'GET',
    'route'  => 'users.index',
]);

// Décrémenter
metrics()->decrement('queue.pending');
metrics()->decrement('inventory.stock', 3, tags: ['sku' => 'ABC-123']);
```

Les compteurs sont typiquement utilisés pour mesurer des **événements discrets** : requêtes, erreurs, envois, connexions.

### Gauges

```php
// Valeur instantanée
metrics()->gauge('queue.pending', 42);
metrics()->gauge('memory.usage_mb', memory_get_usage(true) / 1024 / 1024);
metrics()->gauge('users.online', $count, tags: ['region' => 'eu']);

// Les gauges peuvent monter et descendre
metrics()->gauge('cache.hit_rate', 0.94);
```

Les gauges sont typiquement utilisés pour mesurer une **quantité à un instant T** : taille de queue, mémoire, nombre de connexions actives.

### Timings

```php
// Durée en millisecondes
metrics()->timing('db.query.duration', 12.4);
metrics()->timing('http.request.duration', 142.8, tags: ['route' => 'users.index']);
```

Les timings sont typiquement utilisés pour mesurer des **latences** ou des **durées d'opération**.

### Mesure automatique

`measure()` exécute une closure et enregistre sa durée :

```php
$users = metrics()->measure('users.fetch', function () {
    return User::all();
});

// Avec tags
$result = metrics()->measure('report.generate', function () use ($params) {
    return Report::generate($params);
}, tags: ['type' => 'monthly']);
```

La closure est exécutée dans tous les cas, et le timing est enregistré même si elle lève une exception.

### Tags contextuels

`withTags()` applique des tags à tous les appels effectués dans une closure :

```php
metrics()->withTags(['tenant' => $tenant->id], function () use ($order) {
    metrics()->increment('orders.created');
    metrics()->increment('orders.items', $order->items_count);
    metrics()->timing('orders.process_duration', $order->duration);
});

// Toutes les métriques ci-dessus portent le tag tenant.
```

Les tags contextuels sont fusionnés avec les tags globaux (`config('metrics.tags')`) et les tags passés explicitement.

### Chaînage

Toutes les méthodes retournent le manager, ce qui permet le chaînage :

```php
metrics()
    ->increment('checkout.started')
    ->gauge('checkout.cart_value', $cart->total)
    ->timing('checkout.render', 32.5, tags: ['page' => 'cart']);
```

---

## Drivers
<div id="drivers-1"></div>

### InMemoryDriver

Stocke tout en mémoire. Idéal pour le développement et les tests.

```php
use Clicalmani\Metrics\Drivers\InMemoryDriver;

metrics()->setDriver(new InMemoryDriver());

metrics()->increment('test.metric');
metrics()->increment('test.metric');

$all = metrics()->all();
// [
//   'counters' => ['test.metric' => 2],
//   'gauges'   => [],
//   'timings'  => [],
// ]
```

Les données sont perdues à la fin du processus. Aucune persistance.

### LogDriver

Écrit chaque métrique dans le logger applicatif. Utile en production sans infrastructure dédiée.

```php
use Clicalmani\Metrics\Drivers\LogDriver;

metrics()->setDriver(new LogDriver(
    logger:  logger(),
    channel: 'metrics',
    level:   'info',
));

metrics()->increment('orders.created', tags: ['channel' => 'web']);
// → [info] metric.counter.orders.created {"value":1,"channel":"web"}
```

`LogDriver` est **fire-and-forget** : `metrics()->all()` retourne un tableau vide. Les métriques sont consultables via les logs uniquement.

### StatsdDriver

Envoie les métriques par UDP à un agent StatsD (Datadog, Telegraf, Etsy StatsD). Le driver **bufferise** les métriques en mémoire et les envoie en batch lors du `flush()`.

```php
use Clicalmani\Metrics\Drivers\StatsdDriver;

metrics()->setDriver(new StatsdDriver(
    host:   'statsd.internal',
    port:   8125,
    prefix: 'myapp.',
));

metrics()->increment('orders.created', tags: ['channel' => 'web']);
// → bufferisé, envoyé au prochain flush

metrics()->flush();
// → datagramme UDP envoyé à statsd.internal:8125
```

Le driver regroupe les métriques en datagrammes respectant la MTU (1432 octets par défaut) pour éviter la fragmentation UDP.

Trois mécanismes déclenchent un flush :

1. **Automatique par taille** : dès que le buffer atteint 500 entrées.
2. **Automatique par intervalle** : si `METRICS_FLUSH_INTERVAL` > 0, toutes les N secondes.
3. **Automatique au shutdown** : à la fin de la requête ou du worker PHP.

### Écrire un driver personnalisé

Implémentez `MetricsDriver` :

```php
namespace App\Metrics\Drivers;

use Clicalmani\Metrics\Contracts\MetricsDriver;

final class MyCustomDriver implements MetricsDriver
{
    public function increment(string $key, int $value = 1, array $tags = []): void
    {
        // ...
    }

    public function decrement(string $key, int $value = 1, array $tags = []): void
    {
        // ...
    }

    public function gauge(string $key, float|int $value, array $tags = []): void
    {
        // ...
    }

    public function timing(string $key, float $milliseconds, array $tags = []): void
    {
        // ...
    }

    public function all(): array
    {
        return [];
    }

    public function flush(): void
    {
        // ...
    }
}
```

Si votre driver bufferise et a besoin d'exposer `pending()`, implémentez `FlushableDriver` à la place :

```php
use Clicalmani\Metrics\Contracts\FlushableDriver;

final class MyBufferedDriver implements FlushableDriver
{
    public function pending(): int
    {
        return count($this->buffer);
    }

    // ... autres méthodes
}
```

Puis utilisez-le :

```php
metrics()->setDriver(new MyCustomDriver());
```

---

## Sampling

Le sampling réduit le volume de métriques émises pour les événements à haut débit. Utile quand vous avez 10 000 requêtes/seconde et que mesurer 1% suffit statistiquement.

### Configuration

```php
// config/metrics.php

'sampling' => [
    'http.request'   => 0.1,   // 10% des requêtes HTTP
    'db.query.*'     => 0.05,  // 5% des requêtes DB
    'notifications.*' => 1.0,  // 100% des notifications
],
```

Les patterns utilisent les wildcards `*` (via `fnmatch`). Le **premier pattern qui matche** gagne. Si aucun pattern ne matche, la métrique est émise à 100%.

### Comportement

À chaque appel, un tirage aléatoire décide si la métrique est émise :

```php
// Avec 'db.query.*' => 0.05
metrics()->increment('db.query.count');
// Émis environ 1 fois sur 20
```

**Attention** : le sampling s'applique **avant** la fusion des tags. Deux appels au même pattern ont chacun leur propre tirage.

### Samplers personnalisés

Pour un contrôle total :

```php
use Clicalmani\Metrics\Sampling\Sampler;

final class EnvironmentSampler implements Sampler
{
    public function shouldSample(string $key): bool
    {
        return config('app.env') === 'production' || $key === 'critical.event';
    }
}

metrics()->sampler(new EnvironmentSampler());
```

Samplers fournis :

| Classe | Comportement |
|---|---|
| `AlwaysSampler` | Émet toujours (défaut) |
| `NeverSampler` | N'émet jamais (désactivation) |
| `PatternSampler` | Filtre par motifs (utilisé par la config) |

---

## Cardinality Guard

Le garde-fou de cardinalité protège contre les tags à haute cardinalité qui font exploser le nombre de séries côté backend.

### Configuration

```php
// config/metrics.php

'cardinality_limit' => 1000,
```

### Comportement

Pour chaque **clé de métrique** (ex: `notifications.sent`), le système suit le nombre de **combinaisons de tags** distinctes. Au-delà de la limite, les nouvelles combinaisons sont rejetées et un warning est loggé.

```php
// Avec cardinality_limit = 1000

for ($userId = 1; $userId <= 2000; $userId++) {
    metrics()->increment('notifications.sent', tags: [
        'channel' => 'mail',
        'user_id' => $userId,   
    ]);
}

// Résultat :
// - 1000 premières combinaisons sont émises
// - 1000 suivantes sont rejetées + warning loggé
```

### Callback de rejet

Vous pouvez brancher un callback pour réagir au dépassement :

```php
// Dans le provider
metrics()->cardinalityLimit(
    limit: 1000,
    onDrop: function (string $key, array $tags) {
        logger()->warning("Cardinality limit exceeded for {$key}", $tags);
    }
);
```

### Inspection

```php
metrics()->cardinality()->count('notifications.sent');  // nombre de combinaisons vues
```

### Bonnes pratiques

- **N'incluez jamais** `user_id`, `request_id`, `session_id`, ou un timestamp dans les tags.
- **Préférez** des catégories : `plan`, `region`, `channel`, `status_code`.
- **Si vous avez besoin d'identifier un utilisateur**, utilisez les logs, pas les métriques.

---

## Flush

Certains drivers bufferisent les métriques avant de les envoyer. Le `flush()` les force à vider leur buffer.

### Flush automatique

Trois déclencheurs :

**1. Par taille** — automatique dès que le buffer atteint sa limite interne (StatsD : 500 par défaut).

**2. Par intervalle** — si `METRICS_FLUSH_INTERVAL > 0` :

```env
METRICS_FLUSH_INTERVAL=10
```

À chaque émission, le manager vérifie si le dernier flush date de plus de N secondes. Si oui, il flush avant de continuer.

**3. Au shutdown** — à la fin de la requête ou du worker PHP, `register_shutdown_function` appelle `flush()` pour ne pas perdre les métriques en buffer.

### Flush manuel

```php
metrics()->flush();
```

### Inspection du buffer

Pour les drivers `FlushableDriver` :

```php
$pending = metrics()->pending();
// Nombre d'entrées en attente d'envoi
```

### Bonnes pratiques

- **En CLI/worker**, ajoutez un flush à la fin de chaque itération de boucle si le worker tourne longtemps.
- **En HTTP**, le shutdown s'en charge, mais vous pouvez forcer un flush avant une réponse longue (ex: streaming).

---

## Auto-instrumentation

L'auto-instrumentation émet des métriques **sans écrire une ligne de code métier**. Chaque hook est activé par configuration.

### HTTP

Émet `http.request.count` et `http.request.duration`, taggés par méthode, route et statut.

```env
METRICS_AUTO_HTTP=true
```

Résultat :

```
http.request.count{method:GET,route:users.index,status:200}    1523
http.request.count{method:POST,route:users.store,status:422}     12
http.request.duration{method:GET,route:users.index}            avg 45ms
```

### Database

Émet `db.query.count` et `db.query.duration`, taggés par connexion et type de requête.

```env
METRICS_AUTO_DB=true
```

Résultat :

```
db.query.count{connection:default,type:SELECT}     12847
db.query.count{connection:default,type:INSERT}       234
db.query.duration{connection:default,type:SELECT}  avg 3.2ms
```

Voir aussi la section [Profiling SQL](#profiling-sql) pour les détails par phase.

### Queue

Émet des métriques pour chaque étape du cycle de vie des jobs — à rapprocher du système [Messenger](messenger.md) déjà documenté.

```env
METRICS_AUTO_QUEUE=true
```

Résultat :

```
queue.job.pushed{queue:notifications,job:OrderShipped}      128
queue.job.processed{queue:notifications}                    126
queue.job.failed{queue:notifications,error:RuntimeException}  2
```

### Notifications

Émet des métriques pour chaque événement de cycle de vie des notifications — ces mêmes événements (`NotificationSending`, `NotificationSent`, `NotificationFailed`, `NotificationSkipped`) déjà documentés sur la page [Mail & Notifications](mail-notifications.md#événements-de-cycle-de-vie).

```env
METRICS_AUTO_NOTIFICATIONS=true
```

Résultat :

```
notifications.sending{channel:mail}                             128
notifications.sent{channel:mail,event:OrderShipped}             126
notifications.failed{channel:mail,error:SwiftException}           2
notifications.duration{channel:mail}                          avg 142ms
notifications.skipped{channel:slack}                              4
```

### Désactiver un hook

Chaque hook est indépendant. Désactivez-en un en le passant à `false` :

```env
METRICS_AUTO_HTTP=true
METRICS_AUTO_DB=false
METRICS_AUTO_QUEUE=true
METRICS_AUTO_NOTIFICATIONS=true
```

---

## Profiling SQL

Le profiling SQL (via `SHOW PROFILE`) est une technique **d'investigation**, complémentaire aux métriques. Il décompose une requête en phases et identifie où le temps est passé.

### Différence avec les métriques

| Métriques DB | Profiling SQL |
|---|---|
| Agrège toutes les requêtes | Décompose **une** requête |
| Coût négligeable | Coût significatif |
| Toujours actif | À la demande |
| Détecte qu'il y a un problème | Explique **pourquoi** |

### Activation globale (mode dev)

```env
// .env
DB_PROFILING_ENABLED=true
```

Toutes les requêtes sont profilées. Chaque profil émet :

```
db.profile.total{connection:default}                          avg 12.4ms
db.profile.phase{connection:default,phase:executing}          avg 11.8ms
db.profile.phase{connection:default,phase:sending data}       avg 0.5ms
db.profile.phase{connection:default,phase:optimizing}         avg 0.1ms
```

⚠️ **Déprécié depuis MySQL 5.7, supprimé dans MySQL 8.0.** Sur MySQL 8+, utilisez `explain` via `EXPLAIN ANALYZE`.

### Détection automatique des requêtes lentes

```env
DB_SLOW_QUERY_THRESHOLD=500       # ms
DB_PROFILE_SLOW_QUERIES=false     # re-profiler automatiquement
```

Toute requête dépassant le seuil :

1. Émet `db.slow_query` en métrique.
2. Logge un warning structuré.
3. Si `DB_PROFILE_SLOW_QUERIES=true`, exécute un `profile()` et émet `db.profile.phase`.

### Profiling ponctuel en dev

```php
$profile = db_profile()->profile(function () use ($user) {
    $orders = $user->orders()->with('items')->get();
    $stats  = $user->orders()->selectRaw('COUNT(*) as count')->first();
});

dump(db_profile()->summary($profile));
// [
//   'total_ms' => 423.5,
//   'phases'   => [
//     'executing'    => 412.3,
//     'sending data' => 12.1,
//     'optimizing'   => 0.1,
//   ]
// ]
```

### Profiling d'une requête précise

```php
$profile = db_profile()->profile(
    'SELECT * FROM orders WHERE status = ?',
    ['pending']
);
```

### Alternative moderne — `explain`

Sur MySQL 8.0+ ou MariaDB 10.6+ :

```php
$analysis = db_profile()->explain(
    'SELECT * FROM orders WHERE user_id = ? AND status = ?',
    [1, 'pending']
);

dump($analysis['plan']);
// → Node: Index lookup on orders using idx_user_status ...
```

Le plan est émis en métrique `db.explain.uses_index{result:yes|no}`.

---

## Tests

### Isolation automatique

Avec `fake_when_testing = true` (défaut), le driver bascule automatiquement sur `InMemoryDriver` quand `APP_ENV=testing`. Aucune fuite de métriques vers un backend réel.

```env
METRICS_FAKE_WHEN_TESTING=true
```

### Test d'une métrique émise

```php
use Clicalmani\Core\Support\Facades\Metrics;
use Clicalmani\Metrics\Drivers\InMemoryDriver;

class OrderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Metrics::setDriver(new InMemoryDriver());
    }

    public function test_order_creation_increments_counter(): void
    {
        Order::create(['total' => 100]);

        $all = Metrics::all();

        $this->assertSame(1, $all['counters']['orders.created']);
    }

    public function test_order_duration_is_timed(): void
    {
        Order::create(['total' => 100]);

        $all = Metrics::all();

        $this->assertArrayHasKey('orders.process_duration', $all['timings']);
    }
}
```

> Ce test suppose que votre propre code applicatif émet `orders.created`/`orders.process_duration` (par exemple depuis un Observer, comme dans l'exemple de [Tags contextuels](#tags-contextuels)) — ce ne sont pas des métriques émises automatiquement par le framework, à la différence des hooks listés sous [Auto-instrumentation](#auto-instrumentation).

### Test avec tags

```php
public function test_metric_is_tagged_by_channel(): void
{
    Metrics::increment('notifications.sent', tags: ['channel' => 'mail']);

    $all = Metrics::all();

    $this->assertArrayHasKey('notifications.sent[channel=mail]', $all['counters']);
}
```

> La clé `notifications.sent[channel=mail]` illustre la façon dont `InMemoryDriver` encode les tags dans la clé de stockage interne (`clé[tag=valeur,...]`) — un détail d'implémentation propre à ce driver, qui n'affecte pas la façon dont vous émettez une métrique.

### Test du sampling

```php
use Clicalmani\Metrics\Sampling\NeverSampler;

public function test_sampling_drops_metrics(): void
{
    Metrics::setDriver(new InMemoryDriver());
    Metrics::sampler(new NeverSampler());

    Metrics::increment('any.metric');

    $this->assertArrayNotHasKey('any.metric', Metrics::all()['counters']);
}
```

### Test de la cardinalité

```php
public function test_cardinality_guard_blocks_after_limit(): void
{
    Metrics::setDriver(new InMemoryDriver());
    Metrics::cardinalityLimit(2);

    Metrics::increment('m', tags: ['id' => 1]);
    Metrics::increment('m', tags: ['id' => 2]);
    Metrics::increment('m', tags: ['id' => 3]);  // bloqué

    $this->assertSame(2, Metrics::cardinality()->count('m'));
}
```

---

## Référence API

### `MetricsManager`

| Méthode | Signature | Description |
|---|---|---|
| `increment` | `(string $key, int $value = 1, array $tags = []): self` | Incrémente un compteur |
| `decrement` | `(string $key, int $value = 1, array $tags = []): self` | Décrémente un compteur |
| `gauge` | `(string $key, float\|int $value, array $tags = []): self` | Définit une gauge |
| `timing` | `(string $key, float $ms, array $tags = []): self` | Enregistre une durée |
| `measure` | `(string $key, callable $fn, array $tags = []): mixed` | Exécute et mesure une closure |
| `withTags` | `(array $tags, callable $fn): mixed` | Tags contextuels |
| `sampling` | `(array $rules): self` | Configure le sampling |
| `sampler` | `(Sampler $s): self` | Remplace le sampler |
| `cardinalityLimit` | `(int $limit, ?\Closure $onDrop = null): self` | Configure la cardinalité |
| `cardinality` | `(): CardinalityGuard` | Accès au garde-fou |
| `flushInterval` | `(int $seconds): self` | Intervalle de flush |
| `flush` | `(): self` | Flush immédiat |
| `pending` | `(): int` | Entrées en buffer |
| `driver` | `(): MetricsDriver` | Driver actif |
| `setDriver` | `(MetricsDriver $d): self` | Remplace le driver |
| `use` | `(string $class, mixed ...$args): self` | Instancie un driver |
| `all` | `(): array` | Récupère toutes les métriques |
| `defaultTags` | `(array $tags): self` | Tags globaux |

### `MetricsDriver` (interface)

```php
interface MetricsDriver
{
    public function increment(string $key, int $value = 1, array $tags = []): void;
    public function decrement(string $key, int $value = 1, array $tags = []): void;
    public function gauge(string $key, float|int $value, array $tags = []): void;
    public function timing(string $key, float $milliseconds, array $tags = []): void;
    public function all(): array;
    public function flush(): void;
}
```

### `FlushableDriver` (interface)

```php
interface FlushableDriver extends MetricsDriver
{
    public function pending(): int;
}
```

### `Sampler` (interface)

```php
interface Sampler
{
    public function shouldSample(string $key): bool;
}
```

### `CardinalityGuard`

| Méthode | Description |
|---|---|
| `allow(string $key, array $tags): bool` | Vérifie si la série est autorisée |
| `count(string $key): int` | Nombre de séries vues pour cette clé |
| `reset(): void` | Réinitialise |

### Helper `metrics()`

```php
function metrics(): \Clicalmani\Metrics\MetricsManager;
```

Retourne le singleton depuis le conteneur.

---

## Bonnes pratiques

### Nommage

Utilisez une nomenclature **hiérarchique et cohérente** :

```
<domain>.<entity>.<action>
```

Exemples :

```
http.request.count
http.request.duration
db.query.count
db.query.duration
orders.created
orders.shipped
notifications.sent
notifications.failed
queue.job.processed
```

**À faire :**

- Toujours le même préfixe par domaine (`http.*`, `db.*`, `orders.*`).
- Suffixes explicites : `.count`, `.duration`, `.size`, `.rate`.
- Minuscules, séparateur `.`.

**À éviter :**

- Noms dynamiques (`orders.created.{$userId}`).
- Abréviations ambiguës (`o.c`).
- Mélange de styles (`orderCreated`, `order_created`, `orders.created`).

### Cardinalité

Règle d'or : **un tag doit avoir un nombre fini et petit de valeurs possibles**.

| Tag | Cardinalité | Verdict |
|---|---|---|
| `env` | 3 (dev/staging/prod) | ✅ |
| `region` | ~20 | ✅ |
| `status_code` | ~50 | ✅ |
| `route` | ~100 | ⚠️ surveiller |
| `user_id` | 10 000+ | ❌ |
| `request_id` | ∞ | ❌ |
| `timestamp` | ∞ | ❌ |

### Performance

- Le sampling est votre ami pour les événements à haut débit.
- Le `StatsdDriver` bufferise : ne forcez pas `flush()` à chaque métrique.
- N'émettez pas de métriques dans des boucles serrées sans sampling.
- Les métriques sont **non-bloquantes** : un agent StatsD down ne ralentit pas l'application.

### Observabilité

- Émettez les **quatre signaux RED** pour chaque composant critique : **R**ate, **E**rrors, **D**uration.
- Ne mesurez pas tout — mesurez ce qui vous aidera à diagnostiquer une panne.
- Croisez les métriques avec les **logs** (pour le « comment ») et les **traces** (pour le « où »).

### Environnements

| Env | Driver recommandé | Auto-instrumentation |
|---|---|---|
| `local` | `memory` | Désactivée |
| `testing` | `memory` (forcé) | Désactivée |
| `staging` | `statsd` (préfixe distinct) | Complète |
| `production` | `statsd` | Complète |

---
