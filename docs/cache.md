# Cache

* [Cache](#cache)
* [Introduction](#introduction)
* [Architecture](#architecture)
* [Drivers & Configuration](#drivers-amp-configuration)
* [Pilotes Supportés](#pilotes-supportes)
* [Configuration](#configuration)
* [Stores Multiples](#stores-multiples)


* [Utilisation Basique](#utilisation-basique)
* [Stockage (`set` / `put` / `add` / `replace` / `forever`)](#stockage)
* [Récupération (`get`)](#recuperation-code-get-code-)
* [Vérification & Suppression (`has` / `missing` / `delete` / `deleteMany`)](#verification-amp-suppression)


* [Motifs Avancés](#motifs-avances)
* [Motif `remember`](#motif-code-remember-code-)
* [`pull` et `rememberForever`](#-code-pull-code-et-code-rememberforever-code-)
* [Incrémentation & Décrémentation](#incrementation-amp-decrementation)
* [Vidage complet (`clear`)](#vidage-complet-code-clear-code-)


* [Adapter Database](#adapter-database)
* [Schéma de la table](#schema-de-la-table)
* [Configuration](#configuration-database)
* [Purge automatique](#purge-automatique)


* [Extension : Drivers Personnalisés](#extension-drivers-personnalises)
* [Le contexte d'adaptateurs](#le-contexte-d-39-adaptateurs)
* [Enregistrer un driver custom](#enregistrer-un-driver-custom)
* [Surcharger un driver natif](#surcharger-un-driver-natif)
* [Drivers composés](#drivers-composes)


* [Utilisation via Facade vs Injection](#utilisation-via-facade-vs-injection)
* [Prochaines Étapes](#prochaines-etapes)

---

## Introduction

Le sous-système de **Cache** de Tonka fournit une API unifiée et expressive pour manipuler différents moteurs de mise en cache. Il s'appuie en interne sur des adaptateurs performants (notamment le composant standard **`symfony/cache`** / PSR-6 / PSR-16) afin d'accélérer le temps de réponse en réduisant les appels répétés à la base de données ou les calculs coûteux.

**Fonctionnalités :**

* ✅ Interface simple et unifiée (`Cache::get()`, `Cache::set()`, etc.)
* ✅ Compatibilité PSR-6 / PSR-16 via `symfony/cache`
* ✅ Support multi-drivers (Redis, Memcached, Filesystem, Array, Database)
* ✅ Multi-store : `Cache::store('redis')->get(...)` ou `Cache::redis()->get(...)`
* ✅ Gestion simplifiée de l'expiration (*TTL*) et du motif *Remember*
* ✅ Adaptateurs extensibles : ajoutez vos propres drivers sans toucher au cœur

---

## Architecture

Le cache s'articule autour de quatre composants :

| Composant | Rôle |
| --- | --- |
| `CacheManager` | Orchestre les stores, résout le store par défaut, expose `store()` |
| `CacheAdapterContext` | Registry extensible des factories d'adaptateurs |
| `CacheStore` | Adapte l'API PSR-6 vers l'API expressive (`remember`, `increment`…) |
| `Adapters\*` | Implémentations PSR-6 (Symfony natifs + `DatabaseAdapter` custom) |

Le `CacheManager` ne connaît **aucun adaptateur concret** — il délègue toute instanciation au contexte. C'est ce qui rend le système extensible : un développeur peut enregistrer un driver custom sans modifier le cœur.

---

## Drivers & Configuration

### Pilotes Supportés

Tonka supporte nativement plusieurs types d'adaptateurs :

| Driver | Description | Cas d'usage recommandé |
| --- | --- | --- |
| `file` / `filesystem` | Stockage sur disque local (`storage/framework/cache/data/`) | Développement & projets simples |
| `redis` | In-memory store Redis haute performance | Production & clusters distribués |
| `memcached` | Cache en mémoire distribué | Production |
| `array` | Stockage temporaire en mémoire RAM (durée de la requête) | Tests unitaires & requêtes isolées |
| `database` | Stockage en base de données via l'ORM Tonka | Environnements sans Redis/Memcached, cache partagé |
| `chain` | Composition de plusieurs adaptateurs (L1/L2/L3) | Cache multi-niveaux |

### Configuration

Les options de cache sont définies dans le fichier `config/cache.php` :

```php
return [

    /*
    |--------------------------------------------------------------------------
    | Driver par défaut
    |--------------------------------------------------------------------------
    */
    'default' => env('CACHE_DRIVER', 'file'),

    /*
    |--------------------------------------------------------------------------
    | Stores de Cache
    |--------------------------------------------------------------------------
    */
    'stores' => [

        'file' => [
            'driver' => 'file',
            'path' => storage_path('framework/cache/data'),
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
            'dsn' => env('REDIS_URL', 'redis://127.0.0.1:6379'),
        ],

        'memcached' => [
            'driver' => 'memcached',
            'dsn' => env('MEMCACHED_URL', 'memcached://127.0.0.1:11211'),
        ],

        'array' => [
            'driver' => 'array',
        ],

        'database' => [
            'driver' => 'database',
            'table' => 'cache',
            'lifetime' => 0,    // TTL par défaut (0 = permanent)
            'lock' => true,     // SELECT ... FOR UPDATE sur increment()
        ],

        'chain' => [
            'driver' => 'chain',
            'stores' => ['array', 'redis', 'database'],
            'default_lifetime' => 0,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Préfixe des clés
    |--------------------------------------------------------------------------
    */
    'prefix' => env('CACHE_PREFIX', 'tonka_cache_'),

];
```

### Stores Multiples

Chaque store défini dans `config/cache.php` est accessible individuellement :

```php
use Clicalmani\Core\Support\Facades\Cache;

// Store par défaut (celui de config('cache.default'))
Cache::set('stats.daily', 1450, 3600);

// Store explicite via store()
Cache::store('redis')->set('stats.daily', 1450, 3600);

// Sucre syntaxique : le nom du store est appelé comme méthode
Cache::redis()->set('stats.daily', 1450, 3600);
Cache::database()->get('site.settings');
Cache::array()->has('foo');
```

Le `CacheManager` met en cache les instances de `CacheStore` : appeler `Cache::redis()` deux fois ne crée qu'un seul adaptateur.

---

## Utilisation Basique

Vous pouvez interagir avec le cache à l'aide de la facade `Clicalmani\Core\Support\Facades\Cache`.

### Stockage

Cinq méthodes couvrent les cas d'écriture :

```php
use Clicalmani\Core\Support\Facades\Cache;

// set() : écrit (écrase si la clé existe déjà)
Cache::set('stats.daily', 1450, 3600);   // TTL = 1 heure
Cache::set('site.settings', $settings);  // TTL = permanent

// put() : alias de set() (sémantique Laravel)
Cache::put('stats.daily', 1450, 3600);

// add() : écrit uniquement si la clé n'existe pas déjà
Cache::add('lock.job.42', true, 60);     // retourne true si créé, false sinon

// replace() : écrit uniquement si la clé existe déjà
Cache::replace('stats.daily', 1500, 3600);

// forever() : stockage permanent sans TTL
Cache::forever('site.settings', $settings);
```

### Récupération (`get`)

Récupérez la valeur associée à une clé. Si la clé est absente ou expirée, la méthode retourne `null` ou la valeur par défaut spécifiée :

```php
// Récupération simple
$dailyUsers = Cache::get('stats.daily'); // retourne 1450 ou null

// Avec valeur de repli (default)
$dailyUsers = Cache::get('stats.daily', 0); // retourne 0 si absent
```

### Vérification & Suppression

```php
// Vérifier l'existence d'une clé active
if (Cache::has('stats.daily')) {
    // La clé existe et n'a pas expiré
}

// Vérification inverse
if (Cache::missing('stats.daily')) {
    // La clé est absente ou expirée
}

// Supprimer une clé spécifique
Cache::delete('stats.daily');

// Supprimer plusieurs clés d'un coup
Cache::deleteMany(['stats.daily', 'site.settings', 'page.views.10']);
```

---

## Motifs Avancés

### Motif `remember`

La méthode `remember` tente de récupérer une clé depuis le cache. Si elle n'existe pas, la fonction de fermeture (*closure*) passée en paramètre est exécutée, son résultat est sauvegardé en cache pour la durée spécifiée (*TTL*), puis retourné :

```php
use Clicalmani\Core\Support\Facades\Cache;
use Clicalmani\Core\Support\Facades\DB;

$categories = Cache::remember('categories.all', 86400, function() {
    // Cette requête BDD n'est exécutée que si la clé 'categories.all' est absente ou expirée
    return DB::table('categories')->where('active = ?', [1])->get();
});
```

### `pull` et `rememberForever`

Deux variantes utiles :

```php
// pull() : récupère la valeur PUIS supprime la clé (pop atomique)
$oneTimeToken = Cache::pull('password.reset.42');

// rememberForever() : comme remember() mais sans expiration
$settings = Cache::rememberForever('site.settings', function() {
    return DB::table('settings')->get();
});
```

### Incrémentation & Décrémentation

Utile pour les compteurs d'invalidation, le *rate limiting* ou le suivi de visites :

```php
// Incrémenter la valeur d'une clé (par défaut +1)
Cache::increment('page.views.10');

// Incrémenter de 5
Cache::increment('page.views.10', 5);

// Décrémenter
Cache::decrement('stock.item.42');

// Décrémenter de 3
Cache::decrement('stock.item.42', 3);
```

> **Note** : sur le driver `database`, `increment()` et `decrement()` sont atomiques (transaction isolée + `SELECT ... FOR UPDATE`).

### Vidage complet (`clear`)

Pour purger l'intégralité des éléments stockés dans le driver actif :

```php
Cache::clear();             // vide le store par défaut
Cache::store('redis')->clear();  // vide un store spécifique
```

---

## Adapter Database

L'adapter `database` stocke les entrées de cache dans une table via l'ORM Tonka. Il est utile quand Redis ou Memcached ne sont pas disponibles, mais qu'on veut un cache **partagé entre plusieurs processus** (contrairement à `file` qui reste local, ou `array` qui meurt à la fin de la requête).

### Schéma de la table

```sql
CREATE TABLE `cache` (
    `key`        VARCHAR(255)  NOT NULL,
    `value`      MEDIUMTEXT    NOT NULL,
    `expiration` INT UNSIGNED  NOT NULL DEFAULT 0,
    PRIMARY KEY (`key`),
    INDEX `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Points importants :**

* `key` en `PRIMARY KEY` — les collisions sont gérées par un UPSERT dans `save()`.
* `expiration` en `INT UNSIGNED` — `0` signifie « permanent », toute autre valeur est un timestamp Unix.
* `value` en `MEDIUMTEXT` — ~16 Mo max, largement suffisant pour du JSON de cache.
* Index sur `expiration` — accélère `prune()` et les purges probabilistes.
* **`key` est un mot réservé MySQL** — d'où les backticks dans les requêtes.

### Configuration
<div id="configuration-database"></div>

```php
'database' => [
    'driver'   => 'database',
    'table'    => 'cache',
    'lifetime' => 0,        // TTL par défaut (0 = permanent)
    'lock'     => true,     // active SELECT ... FOR UPDATE sur increment()
],
```

### Sérialisation

Les valeurs sont encodées en **JSON** par défaut. Si `json_encode()` échoue (objets non sérialisables, ressources, closures), l'adapter bascule automatiquement sur `base64_encode(serialize($value))`. À la lecture, la désérialisation détecte le format et restaure la valeur d'origine.

### Purge automatique

À chaque `get()`, l'adapter déclenche une purge des entrées expirées avec une probabilité de **2 %** (configurable). Cela évite que la table ne grossisse indéfiniment sans nécessiter de tâche cron dédiée.

Pour purger manuellement l'intégralité des entrées expirées :

```php
$store = Cache::store('database');
$count = $store->getAdapter()->prune(); // retourne le nombre d'entrées supprimées
```

---

## Extension : Drivers Personnalisés

### Le contexte d'adaptateurs

Le `CacheAdapterContext` est un registry de factories. Chaque factory reçoit la configuration du store et retourne un adaptateur PSR-6 :

```php
public function extend(string $driver, callable $factory): static
```

La signature d'une factory est :

```php
function (array $config, string $prefix, CacheManager $manager): CacheItemPoolInterface
```

Le troisième argument `$manager` permet à un driver de résoudre d'autres stores (utile pour les drivers composés comme `chain`).

### Enregistrer un driver custom

Imaginons que vous vouliez brancher DynamoDB. Créez d'abord un adapter PSR-6 :

```php
namespace App\Cache;

use Psr\Cache\CacheItemPoolInterface;

class DynamoDbAdapter implements CacheItemPoolInterface
{
    // ... implémentation PSR-6 ...
}
```

Puis enregistrez-le dans un Service Provider :

```php
namespace App\Providers;

use Clicalmani\Core\Providers\ServiceProvider;

class CustomCacheServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        app('cache')->extend('dynamodb', function (array $config, string $prefix) {
            return new \App\Cache\DynamoDbAdapter(
                $config['table'] ?? 'cache',
                $config['region'] ?? 'us-east-1',
                $prefix
            );
        });
    }
}
```

Dans `config/cache.php`, déclarez le store :

```php
'dynamodb' => [
    'driver' => 'dynamodb',
    'table'  => 'app_cache',
    'region' => 'eu-west-3',
],
```

Aucune modification du cœur n'est nécessaire. Le développeur peut maintenant utiliser :

```php
Cache::store('dynamodb')->set('foo', 'bar', 60);
// ou
Cache::dynamodb()->set('foo', 'bar', 60);
```

### Surcharger un driver natif

L'enregistrement écrase la factory existante — **le dernier enregistré gagne**. Utile pour remplacer une implémentation par défaut :

```php
// Remplacer Redis par Predis au lieu de phpredis
app('cache')->extend('redis', function (array $config, string $prefix) {
    $client = new \Predis\Client($config['dsn']);
    return new \Symfony\Component\Cache\Adapter\RedisAdapter($client, $prefix);
});
```

### Drivers composés

Un driver peut résoudre d'autres stores via le `CacheManager` passé en troisième argument :

```php
// Cache multi-niveaux : array (L1) → redis (L2) → database (L3)
app('cache')->extend('tiered', function (array $config, string $prefix, $manager) {
    return new \Symfony\Component\Cache\Adapter\ChainAdapter([
        $manager->store('array')->getAdapter(),
        $manager->store('redis')->getAdapter(),
        $manager->store('database')->getAdapter(),
    ], 0);
});
```

---

## Utilisation via Facade vs Injection

Grâce à l'architecture du conteneur d'injection de Tonka, vous pouvez manipuler le cache soit via la facade, soit par injection directe ou via le helper `cache()` :

```php
namespace App\Http\Controllers;

use Clicalmani\Core\Support\Facades\Cache;

class ArticleController
{
    // 1. Via la Facade
    public function showFacade(int $id)
    {
        return Cache::remember("article.{$id}", 3600, function() use ($id) {
            return DB::table('articles')->where('id = ?', [$id])->first();
        });
    }

    // 2. Via le Helper
    public function showHelper(int $id)
    {
        return cache()->get("article.{$id}");
    }

    // 3. Via un store explicite
    public function showRedis(int $id)
    {
        return Cache::store('redis')->remember("article.{$id}", 3600, function() use ($id) {
            return DB::table('articles')->where('id = ?', [$id])->first();
        });
    }
}
```

> **Note** : le helper `cache()` retourne directement un `CacheStore` (le store par défaut), donc `cache()->get('foo')` est équivalent à `Cache::get('foo')`. Pour cibler un store, utilisez `cache('redis')->get('foo')` ou `Cache::store('redis')`.

---

## Prochaines Étapes

Maintenant que vous maîtrisez la mise en cache :

1. **[Service Providers](service-providers.md)** → Enregistrement de services personnalisés
2. **[Facades](facades.md)** → Toutes les facades disponibles
3. **[Commandes Console](commands.md)** → Référence des commandes CLI

---

**Cache compris !** 🚀 → [Service Providers](service-providers.md)

---