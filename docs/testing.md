# Tests

- [Introduction](#introduction)
- [Installation et configuration](#installation-et-configuration)
    - [Installation de PHPUnit](#installation-de-phpunit)
    - [Le fichier `phpunit.xml`](#le-fichier-code-phpunit-xml-code-)
    - [Le fichier `.env.testing`](#le-fichier-code-env-testing-code-)
- [Création des tests](#creation-des-tests)
    - [Génération des classes de test](#generation-des-classes-de-test)
    - [Structure d'un test](#structure-d-39-un-test)
- [Tests unitaires et tests fonctionnels](#tests-unitaires-et-tests-fonctionnels)
- [Tests fonctionnels HTTP](#tests-fonctionnels-http)
    - [Émission de requêtes](#emission-de-requetes)
    - [Test des API JSON](#test-des-api-json)
    - [Assertions sur les réponses](#assertions-sur-les-reponses)
    - [Authentification](#authentification)
- [Tests de base de données](#tests-de-base-de-donnees)
    - [Réinitialisation de la base de données](#reinitialisation-de-la-base-de-donnees)
    - [Factories de modèles](#factories-de-modeles)
    - [Assertions sur la base de données](#assertions-sur-la-base-de-donnees)
- [Test du conteneur de services](#test-du-conteneur-de-services)
- [Simulacres et fakes](#simulacres-et-fakes)
    - [Simuler le bus de messages](#simuler-le-bus-de-messages)
    - [Simuler les e-mails et les notifications](#simuler-les-e-mails-et-les-notifications)
    - [Simuler la diffusion d'événements](#simuler-la-diffusion-d-39-evenements)
    - [Simuler les clients HTTP](#simuler-les-clients-http)
- [Test des commandes console](#test-des-commandes-console)
- [Exécution des tests](#execution-des-tests)
    - [La commande `test`](#la-commande-test)
    - [Tests en parallèle](#tests-en-parallele)
- [Couverture de code](#couverture-de-code)
- [Bonnes pratiques](#bonnes-pratiques)

---

## Introduction

Tonka est conçu pour être testable. Le framework fournit une base solide pour l'écriture de tests automatisés, des tests unitaires isolés aux tests fonctionnels HTTP qui exercent l'intégralité du cycle de vie d'une requête. Tester une application tôt et régulièrement est le moyen le plus fiable de préserver sa stabilité à mesure qu'elle évolue.

La couche de test de Tonka repose sur **PHPUnit**, la référence de facto pour le test en PHP. Elle expose une API claire et expressive pour simuler des requêtes HTTP, interagir avec la base de données, simuler des dépendances externes et vérifier les résultats obtenus. Qu'il s'agisse de tester un simple objet-valeur ou un point d'entrée d'API complexe qui émet des messages, Tonka fournit les outils nécessaires pour écrire des tests rapides, lisibles et maintenables.

Le répertoire `tests` d'une application Tonka s'organise en deux sous-répertoires principaux :

- **`tests/Unit`** : tests ciblant une portion réduite et isolée du code. La plupart des tests unitaires visent une méthode ou une classe unique et ne démarrent pas l'application complète.
- **`tests/Feature`** : tests exerçant une portion plus large de l'application, incluant le routage, les middlewares, les contrôleurs et les interactions avec la base de données. Ces tests démarrent l'application et simulent des requêtes HTTP.

> 💡 **Recommandation :** dans la plupart des applications, la majorité des tests devraient être des tests fonctionnels. Ils offrent le meilleur niveau de confiance quant au bon fonctionnement du système dans son ensemble. Les tests unitaires restent utiles pour la logique métier complexe, mais ils ne détectent pas les problèmes d'intégration entre composants.

---

## Installation et configuration

### Installation de PHPUnit

Tonka n'embarque pas de framework de test par défaut. Vous devez installer PHPUnit (et, optionnellement, Pest) via Composer, en tant que dépendances de développement :

```bash
composer require --dev phpunit/phpunit
```

Si vous préférez Pest, un DSL de test plus expressif construit au-dessus de PHPUnit :

```bash
composer require --dev pestphp/pest
```

Tonka est compatible avec les deux outils. Les exemples de cette documentation utilisent la syntaxe PHPUnit, mais les concepts s'appliquent directement à Pest.

### Le fichier `phpunit.xml`

Un fichier `phpunit.xml` est requis à la racine du projet. Il configure le comportement de PHPUnit : suites de tests, variables d'environnement et fichier d'amorçage (*bootstrap*) :

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true">
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory>tests/Feature</directory>
        </testsuite>
    </testsuites>

    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="DB_CONNECTION" value="sqlite"/>
        <env name="DB_DATABASE" value=":memory:"/>
        <env name="BROADCAST_DRIVER" value="null"/>
        <env name="MESSENGER_TRANSPORT" value="sync"/>
    </php>
</phpunit>
```

La section `<php>` est essentielle : elle définit des variables d'environnement appliquées **uniquement** lors de l'exécution des tests. Elle garantit ainsi que vos tests n'atteignent jamais votre base de données de développement ou de production, votre serveur de messagerie ou votre hub Mercure.

### Le fichier `.env.testing`

Pour les variables d'environnement trop complexes pour être définies directement dans `phpunit.xml`, ou que vous souhaitez exclure du contrôle de version, créez un fichier `.env.testing` à la racine du projet :

```env
APP_ENV=testing
APP_KEY=base64:test-key-here

DB_CONNECTION=sqlite
DB_DATABASE=:memory:

MAIL_MAILER=log
BROADCAST_DRIVER=null
MESSENGER_TRANSPORT=sync
```

Ce fichier est chargé automatiquement à l'exécution de PHPUnit et prend le pas sur les valeurs du fichier `.env` standard.

---

## Création des tests

### Génération des classes de test

Tonka fournit la commande console `make:test` pour générer le squelette d'une classe de test :

```bash
php tonka make:test UserRegistrationTest
```

Par défaut, cette commande génère un test fonctionnel dans `tests/Feature`. Pour créer un test unitaire, utilisez l'option `--unit` :

```bash
php tonka make:test UserServiceTest --unit
```

La classe générée étend la classe de base `TestCase` de Tonka, qui fournit les méthodes utilitaires employées tout au long de cette documentation.

### Structure d'un test

Une classe de test fonctionnel typique se présente comme suit :

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class UserRegistrationTest extends TestCase
{
    /**
     * Vérifie qu'un utilisateur peut s'inscrire avec des données valides.
     */
    public function test_user_can_register_with_valid_data(): void
    {
        $response = $this->postJson('/register', [
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('user.email', 'alice@example.com');
    }
}
```

Chaque méthode de test doit vérifier **un comportement unique**. Évitez de tester plusieurs préoccupations dans la même méthode : en cas d'échec, vous devez pouvoir identifier précisément ce qui ne fonctionne plus.

---

## Tests unitaires et tests fonctionnels

| Critère | Test unitaire | Test fonctionnel |
|---|---|---|
| **Périmètre** | Une classe ou une méthode, en isolation. | Une requête HTTP complète, du routage à la réponse. |
| **Démarre l'application ?** | Non. | Oui. |
| **Accède à la base de données ?** | Non (sauf simulation explicite). | Oui, avec `RefreshDatabase`. |
| **Vitesse** | Très rapide. | Plus lent (démarrage du noyau, exécution des middlewares). |
| **Cas d'usage** | Logique métier, objets-valeurs, services dont les dépendances sont simulées. | Contrôleurs, routes, middlewares, validation, interactions avec la base de données. |

**Choisir le bon type de test :**

- Privilégiez les **tests unitaires** pour les fonctions pures, les calculs complexes et les services dont vous souhaitez isoler une logique précise.
- Privilégiez les **tests fonctionnels** dès que le scénario implique HTTP, la base de données, l'authentification ou la collaboration de plusieurs composants.

---

## Tests fonctionnels HTTP

Les tests fonctionnels simulent des requêtes HTTP vers l'application et permettent de vérifier la réponse obtenue. Tonka fournit des méthodes fluides pour tous les verbes HTTP courants.

### Émission de requêtes

Les méthodes `get`, `post`, `put`, `patch` et `delete` simulent l'envoi de requêtes :

```php
// Requête GET
$response = $this->get('/users');

// Requête POST avec des données de formulaire
$response = $this->post('/users', [
    'name' => 'Bob',
    'email' => 'bob@example.com',
]);

// Requête PUT avec un corps JSON
$response = $this->putJson('/users/1', [
    'name' => 'Robert',
]);
```

Les variantes `*Json` (`getJson`, `postJson`, `putJson`, `patchJson`, `deleteJson`) définissent les en-têtes `Content-Type: application/json` et `Accept: application/json`, et sérialisent le corps de la requête en JSON.

**En-têtes personnalisés**

```php
$response = $this->withHeaders([
    'X-Custom-Header' => 'value',
])->get('/endpoint');
```

**Données de session**

```php
$response = $this->withSession(['key' => 'value'])->get('/dashboard');
```

### Test des API JSON

Pour tester une API JSON, utilisez les méthodes `*Json` ainsi que les assertions spécifiques au format JSON :

```php
public function test_api_returns_user_list(): void
{
    $response = $this->getJson('/api/users');

    $response->assertStatus(200);
    $response->assertJsonCount(3);
    $response->assertJsonStructure([
        'data' => [
            '*' => ['id', 'name', 'email'],
        ],
    ]);
}
```

### Assertions sur les réponses

Tonka fournit un ensemble complet d'assertions sur les réponses HTTP :

| Assertion | Description |
|---|---|
| `assertStatus(200)` | Vérifie le code de statut HTTP. |
| `assertOk()` | Vérifie un statut 200. |
| `assertCreated()` | Vérifie un statut 201. |
| `assertNotFound()` | Vérifie un statut 404. |
| `assertForbidden()` | Vérifie un statut 403. |
| `assertUnauthorized()` | Vérifie un statut 401. |
| `assertJson(array $data)` | Vérifie que la réponse JSON contient le sous-ensemble indiqué. |
| `assertJsonPath('key', $value)` | Vérifie qu'un chemin JSON donné est égal à la valeur attendue. |
| `assertJsonCount(3)` | Vérifie que le tableau JSON contient le nombre d'éléments indiqué. |
| `assertJsonStructure(array $structure)` | Vérifie que la réponse JSON respecte la structure indiquée. |
| `assertSee('text')` | Vérifie que le corps de la réponse contient la chaîne indiquée. |
| `assertRedirect('/path')` | Vérifie que la réponse est une redirection vers le chemin indiqué. |
| `assertSessionHasErrors('field')` | Vérifie qu'une erreur de validation existe pour le champ indiqué. |

### Authentification

Pour tester des routes protégées, utilisez la méthode `actingAs`, qui simule un utilisateur authentifié :

```php
use App\Models\User;

public function test_authenticated_user_can_view_dashboard(): void
{
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertStatus(200);
}
```

Pour les tests d'API, vous pouvez également transmettre un jeton d'authentification (*bearer token*) :

```php
$response = $this->withToken($apiToken)->getJson('/api/user');
```

---

## Tests de base de données

Les tests fonctionnels qui interagissent avec la base de données doivent la réinitialiser entre chaque test afin de garantir leur isolation. Tonka fournit pour cela le trait `RefreshDatabase` : il exécute les migrations une seule fois, puis encapsule chaque test dans une transaction annulée (*rollback*) à la fin de celui-ci.

### Réinitialisation de la base de données

Utilisez le trait `RefreshDatabase` dans votre classe de test :

```php
<?php

namespace Tests\Feature;

use Clicalmani\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_is_created(): void
    {
        $this->post('/users', [
            'name' => 'Alice',
            'email' => 'alice@example.com',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'alice@example.com',
        ]);
    }
}
```

> ⚠️ **Important :** n'exécutez jamais vos tests sur une base de données de production ou de développement. Configurez systématiquement une base dédiée aux tests dans `phpunit.xml` ou `.env.testing`. Le mode en mémoire de SQLite (`:memory:`) est idéal pour la rapidité, mais si vous utilisez des fonctionnalités dépendantes du moteur de base de données, utilisez une instance de test distincte du même moteur que celui de production.

### Factories de modèles

Les factories de modèles génèrent des données de test réalistes, sans multiplier les appels répétitifs à `create()`. Définissez une factory pour chaque modèle :

```php
<?php

namespace Database\Factories;

use App\Models\User;
use Clicalmani\Database\Factory\Factory;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => password_hash('password', PASSWORD_BCRYPT),
        ];
    }
}
```

Utilisez ensuite la factory dans vos tests :

```php
use App\Models\User;

// Créer un utilisateur
$user = User::factory()->create();

// Créer plusieurs utilisateurs
$users = User::factory()->count(5)->create();

// Créer un utilisateur avec des attributs spécifiques
$admin = User::factory()->create(['role' => 'admin']);
```

### Assertions sur la base de données

Tonka fournit des assertions pour vérifier l'état de la base de données :

| Assertion | Description |
|---|---|
| `assertDatabaseHas('users', ['email' => '...'])` | Vérifie qu'une ligne correspondant aux valeurs de colonnes indiquées existe. |
| `assertDatabaseMissing('users', ['email' => '...'])` | Vérifie qu'aucune ligne correspondante n'existe. |
| `assertDatabaseCount('users', 3)` | Vérifie que la table contient le nombre de lignes indiqué. |
| `assertSoftDeleted('users', ['id' => 1])` | Vérifie qu'une ligne a fait l'objet d'une suppression logique (*soft delete*). |

---

## Test du conteneur de services

Tonka s'appuyant largement sur un conteneur de services, il peut être nécessaire de substituer certaines implémentations pendant les tests. Le conteneur expose pour cela la méthode `set`, qui permet de surcharger une liaison :

```php
use App\Services\PaymentGateway;
use App\Services\FakePaymentGateway;

public function test_order_processing_uses_payment_gateway(): void
{
    $fakeGateway = new FakePaymentGateway();
    container()->set(PaymentGateway::class, $fakeGateway);

    $this->post('/orders', ['amount' => 100]);

    $this->assertTrue($fakeGateway->wasCharged());
}
```

Cette technique est particulièrement utile pour remplacer les clients de services externes, les dépôts de données (*repositories*) ou toute dépendance qui effectuerait autrement de véritables appels réseau.

---

## Simulacres et fakes

### Simuler le bus de messages

Pour tester du code qui émet des messages, utilisez un faux bus de messages : il évite toute écriture réelle sur le transport et permet de vérifier les messages émis :

```php
use Symfony\Component\Messenger\MessageBusInterface;
use Tests\Support\MessengerFake;

public function test_registration_dispatches_welcome_email(): void
{
    $fake = new MessengerFake();
    container()->set('messenger', $fake);

    $this->post('/register', [
        'name' => 'Alice',
        'email' => 'alice@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $fake->assertDispatched(SendWelcomeEmail::class);
    $fake->assertDispatchedCount(SendWelcomeEmail::class, 1);
}
```

Consultez la documentation [Messagerie](#) pour l'implémentation complète de `MessengerFake`.

### Simuler les e-mails et les notifications

Pour empêcher l'envoi réel d'e-mails pendant les tests, utilisez les fakes `Mail` et `Notification` :

```php
use Clicalmani\Core\Support\Facades\Mail;
use Clicalmani\Foundation\Notifications\Notification;

public function test_welcome_email_is_sent(): void
{
    Mail::fake();

    $this->post('/register', [
        'name' => 'Alice',
        'email' => 'alice@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    Mail::assertSent(WelcomeMail::class, function ($mail) {
        return $mail->hasTo('alice@example.com');
    });
}

public function test_welcome_notification_is_sent(): void
{
    Notification::fake();

    $user = User::factory()->create();
    $user->notify(new WelcomeNotification($user));

    Notification::assertSentTo($user, WelcomeNotification::class);
}
```

### Simuler la diffusion d'événements

Pour supprimer la publication vers Mercure pendant les tests, utilisez le fake `Broadcast` :

```php
use Clicalmani\Broadcasting\Facades\Broadcast;

public function test_comment_broadcasts_event(): void
{
    Broadcast::fake();

    $this->postJson('/comments', [
        'document_id' => 1,
        'body' => 'Test comment',
    ]);

    Broadcast::assertDispatched(CommentPosted::class);
}
```

Consultez la documentation [Diffusion d'événements](#) pour la liste complète des assertions disponibles.

### Simuler les clients HTTP

Si votre application émet des requêtes HTTP sortantes vers des API externes, utilisez le fake du client HTTP pour les intercepter :

```php
use Clicalmani\Foundation\Support\Facades\Http;

public function test_external_api_is_called(): void
{
    Http::fake([
        'api.example.com/*' => Http::response(['status' => 'ok'], 200),
    ]);

    $this->post('/process');

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.example.com/process';
    });
}
```

---

## Test des commandes console

Les commandes console peuvent être testées en les invoquant directement depuis un test :

```php
public function test_newsletter_command_sends_emails(): void
{
    Mail::fake();

    $this->artisan('newsletter:send');

    Mail::assertSent(NewsletterMail::class, 10);
}
```

La méthode `artisan` retourne un code de sortie, sur lequel il est possible de faire une assertion :

```php
$exitCode = $this->artisan('import:users', ['file' => 'users.csv']);

$this->assertSame(0, $exitCode);
```

---

## Exécution des tests

### La commande `test`

Tonka fournit la commande console `test`, qui encapsule PHPUnit et propose une sortie plus lisible :

```bash
php tonka test
```

Vous pouvez cibler une suite de tests ou interrompre l'exécution au premier échec :

```bash
php tonka test --testsuite=Feature --stop-on-failure
```

### Tests en parallèle

Pour les suites de tests volumineuses, l'exécution en parallèle réduit considérablement le temps total. Installez `brianium/paratest` :

```bash
composer require --dev brianium/paratest
```

Lancez ensuite les tests avec l'option `--parallel` :

```bash
php tonka test --parallel
```

Tonka crée et migre automatiquement une base de données de test distincte pour chaque processus parallèle, ce qui garantit leur isolation. Le nom de la base est suffixé par un identifiant unique de processus (par exemple `your_db_test_1`, `your_db_test_2`).

> ⚠️ **Remarque :** les tests en parallèle nécessitent un moteur de base de données qui supporte plusieurs connexions simultanées. Les bases SQLite en mémoire ne peuvent pas être utilisées dans ce mode : utilisez plutôt une instance MySQL ou PostgreSQL dédiée aux tests.

---

## Couverture de code

Pour mesurer la proportion de code exercée par vos tests, activez la couverture de code. Elle nécessite l'extension **Xdebug** ou **PCOV** :

```bash
# Rapport HTML
php tonka test --coverage-html=coverage

# Rapport Clover (XML), exploitable par les outils d'intégration continue
php tonka test --coverage-clover=coverage/clover.xml
```

Le choix du format de rapport est indépendant du pilote utilisé. PCOV est généralement plus rapide que Xdebug pour la seule mesure de couverture. Le rapport HTML est généré dans le répertoire `coverage` et peut être consulté dans un navigateur.

**Objectifs de couverture**

Une couverture de 100 % est rarement nécessaire, mais il est recommandé de viser un taux élevé sur la logique métier critique :

| Composant | Couverture cible |
|---|---|
| Modèles et logique de domaine | 90 % et plus |
| Services | 85 % et plus |
| Contrôleurs | 80 % et plus |
| Global | 75 % et plus |

> 💡 **Privilégiez le comportement plutôt que les lignes.** Une suite de tests à 80 % de couverture qui exerce des scénarios réalistes est plus précieuse qu'une suite à 100 % qui se limite à tester des accesseurs et mutateurs triviaux.

---

## Bonnes pratiques

1. **Un seul comportement par test.** Un test doit vérifier un comportement unique. En cas d'échec, son nom doit indiquer précisément ce qui ne fonctionne plus.

2. **Des noms de test explicites.** `test_guest_cannot_create_post` est plus parlant que `testCreatePost`.

3. **Des tests indépendants.** Ne vous appuyez jamais sur l'ordre d'exécution des tests ni sur un état laissé par un test précédent. Utilisez `RefreshDatabase` et les factories pour repartir d'un état propre à chaque test.

4. **Des factories plutôt que des données écrites à la main.** Les factories rendent les tests plus lisibles et réduisent la duplication. `User::factory()->create()` est plus clair que cinq lignes de `User::create([...])`.

5. **Ne testez pas le framework.** Tonka et Symfony sont déjà testés. Concentrez-vous sur le comportement de *votre* application, et non sur le fonctionnement interne du framework.

6. **Ne simulez qu'aux frontières.** Simulez les services externes (API, e-mails, files d'attente), pas vos propres classes de domaine. Un excès de simulacres produit des tests qui passent même lorsque l'application est défaillante.

7. **Exécutez les tests fréquemment.** Intégrez `php tonka test` à votre pipeline d'intégration continue. Des tests exécutés à chaque *push* détectent immédiatement les régressions.

8. **Testez le cas nominal et les cas limites.** N'oubliez pas les échecs d'autorisation, les erreurs de validation, les enregistrements introuvables et les conditions aux limites.

---

**Les tests n'ont plus de secret pour vous !** 🚀 → [Validation](validation.md)