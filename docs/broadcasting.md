# Broadcasting

- [Introduction](#introduction)
- [Configuration Côté Serveur](#configuration-cote-serveur)
  - [Prérequis du Driver](#prerequis-du-driver)
  - [Configuration du Hub](#configuration-du-hub)
- [Vue d'Ensemble](#vue-d-39-ensemble)
  - [Fonctionnement du Broadcasting SSE](#fonctionnement-du-broadcasting-sse)
  - [Exemple d'Application](#exemple-dapplication)
- [Définition des Événements Broadcastés](#definition-des-evenements-broadcastes)
  - [Nom de Diffusion](#nom-de-diffusion)
  - [Données de Diffusion](#donnees-de-diffusion)
  - [Topics de Diffusion](#topics-de-diffusion)
  - [Conditions de Diffusion](#conditions-de-diffusion)
  - [Broadcasting et Transactions de Base de Données](#broadcasting-et-transactions-de-base-de-donnees)
- [Autorisation des Topics](#autorisation-des-topics)
  - [Topics Privés](#topics-prives)
  - [Callbacks d'Autorisation](#callbacks-dautorisation)
  - [Génération des Tokens d'Abonnement](#generation-des-tokens-dabonnement)
- [Dispatch des Événements Broadcastés](#dispatch-des-evenements-broadcastes)
  - [Dispatch depuis un Contrôleur](#dispatch-depuis-un-controleur)
  - [Diffusion vers Plusieurs Topics](#diffusion-vers-plusieurs-topics)
  - [Diffusion sans Classe d'Événement](#diffusion-sans-classe-d-39-evenement)
- [Configuration Côté Client](#configuration-cote-client)
  - [Installation](#installation)
  - [Connexion au Hub Mercure](#connexion-au-hub-mercure)
  - [Abonnement aux Topics Publics](#abonnement-aux-topics-publics)
  - [Abonnement aux Topics Privés](#abonnement-aux-topics-prives)
  - [Écoute des Événements](#ecoute-des-evenements)
  - [Reconnexion et Récupération des Événements](#reconnexion-et-recuperation-des-evenements)
  - [Fermeture des Connexions](#fermeture-des-connexions)
- [Broadcasting des Notifications](#broadcasting-des-notifications)
  - [Mise en Place](#mise-en-place)
  - [Formatage des Notifications Diffusées](#formatage-des-notifications-diffusees)
  - [Écoute des Notifications](#ecoute-des-notifications)
- [Hub Mercure](#hub-mercure)
  - [Installation du Hub](#installation-du-hub)
  - [Configuration du Hub (Caddyfile)](#configuration-du-hub-caddyfile)
  - [Exécution comme Service systemd](#execution-comme-service-systemd)
  - [Installation sous Windows](#installation-sous-windows)
  - [SSL et Reverse Proxy](#ssl-et-reverse-proxy)
  - [Scaling Multi-Nœuds](#scaling-multi-nœuds)
- [Prochaines Étapes](#prochaines-étapes)

---

## Introduction

Les applications web modernes exigent des capacités temps réel : système de notifications en direct, édition collaborative, tableau de bord dynamique... pouvoir pousser instantanément des mises à jour du serveur vers les clients connectés n'est plus un luxe, mais une attente.

Le système de broadcasting de Tonka repose sur une fondation unique : les **Server-Sent Events (SSE)** via le **protocole Mercure**. Contrairement aux approches WebSocket, qui exigent des connexions bidirectionnelles persistantes et une gestion de connexion complexe, SSE s'appuie sur la sémantique HTTP standard : le client ouvre une connexion HTTP de longue durée vers un hub Mercure, et le serveur y pousse les mises à jour au fil de l'eau. Cette architecture est nativement supportée par tous les navigateurs modernes via l'API `EventSource`, ne requiert aucune bibliothèque JavaScript spécifique côté réception, et s'intègre naturellement à l'infrastructure HTTP existante (load balancers, proxies, CDN).

L'architecture du broadcasting de Tonka repose sur trois concepts :

1. **Les événements broadcastés** — des classes d'événement côté serveur qui encapsulent les données à transmettre aux clients. Elles implémentent l'interface `ShouldBroadcastInterface` et précisent les topics sur lesquels les publier.
2. **Les topics** — des canaux de communication nommés, issus du modèle de topics de Mercure, auxquels les clients s'abonnent pour recevoir les événements pertinents. Un topic peut être public (accessible à tous) ou privé (autorisation par token JWT).
3. **Le hub Mercure** — un serveur autonome et léger (écrit en Go) qui joue le rôle de point de dispatch central. Votre application Tonka publie les événements au hub par requêtes HTTP POST, et le hub les diffuse à tous les clients abonnés.

```
┌─────────────────┐     HTTP POST (JWT)     ┌─────────────────┐     Flux SSE        ┌─────────────────┐
│   App Tonka     │────────────────────────▶│   Hub Mercure   │────────────────────▶│   Navigateur    │
│   (Éditeur)     │                         │   (Dispatcher)  │                     │   (Abonné)      │
└─────────────────┘                         └─────────────────┘                     └─────────────────┘
                                                    │
                                                    │ Flux SSE
                                                    ▼
                                            ┌─────────────────┐
                                            │   Navigateur    │
                                            │   (Abonné)      │
                                            └─────────────────┘
```

Cette architecture présente plusieurs avantages :

- **Aucune extension PHP supplémentaire.** Contrairement aux solutions basées sur Swoole, Mercure fonctionne comme un service séparé : votre application reste sans état et simple à déployer.
- **Support natif par les navigateurs.** L'API `EventSource` est intégrée à tous les navigateurs modernes.
- **Reconnexion automatique.** `EventSource` gère les interruptions réseau de façon transparente, en reprenant le flux d'événements sans code supplémentaire.
- **Scalabilité horizontale.** Le hub Mercure peut être mis à l'échelle indépendamment de vos serveurs applicatifs.

> **Pour les développeurs Laravel/Symfony :** ce système est l'équivalent fonctionnel de Laravel Broadcasting (avec Echo/Pusher/Reverb) — mais reposant sur Mercure, protocole aussi nativement supporté par Symfony. Si vous connaissez déjà Mercure côté Symfony, le modèle mental (hub + topics + JWT) est strictement identique.

---

## Configuration Côté Serveur

La configuration du broadcasting est centralisée dans le fichier `config/broadcasting.php`. Il définit les drivers disponibles et leurs paramètres de connexion. Tonka utilisant Mercure comme unique transport temps réel, cette configuration reste simple.

### Prérequis du Driver

Le driver Mercure requiert un hub Mercure en fonctionnement. Aucun paquet PHP supplémentaire n'est nécessaire : Tonka communique avec le hub par de simples requêtes HTTP. La seule exigence est que l'URL du hub soit joignable depuis votre serveur applicatif.

Pour installer le hub, téléchargez le dernier binaire depuis la [page des releases Mercure](https://github.com/dunglas/mercure/releases), ou lancez-le via Docker :

```bash
docker run \
    -e MERCURE_PUBLISHER_JWT_KEY='!ChangeThisMercureHubJWTSecretKey!' \
    -e MERCURE_SUBSCRIBER_JWT_KEY='!ChangeThisMercureHubJWTSecretKey!' \
    -p 3000:80 \
    dunglas/mercure
```

Les instructions détaillées d'installation et de déploiement figurent dans la section [Hub Mercure](#hub-mercure).

### Configuration du Hub

Le fichier de configuration précise comment Tonka se connecte au hub Mercure :

```php
return [

    /*
    |--------------------------------------------------------------------------
    | Default Broadcaster
    |--------------------------------------------------------------------------
    */

    'default' => env('BROADCAST_DRIVER', 'mercure'),

    /*
    |--------------------------------------------------------------------------
    | Broadcast Connections
    |--------------------------------------------------------------------------
    |
    | Supported: "mercure", "log", "null"
    |
    */

    'connections' => [

        'mercure' => [
            'driver' => 'mercure',
            'url'    => env('MERCURE_HUB_URL', 'http://localhost:3000/.well-known/mercure'),
            'secret' => env('MERCURE_JWT_SECRET', ''),
            'token'  => env('MERCURE_JWT_TOKEN', ''),
            'ttl'    => (int) env('MERCURE_JWT_TTL', 900),
        ],

        'log' => [
            'driver' => 'log',
        ],

        'null' => [
            'driver' => 'null',
        ],

    ],

    // Préfixe de route utilisé par l'endpoint d'autorisation générique
    // (voir routes/broadcasting.php) — ici, les tokens sont émis sous /broadcasting
    'auth_prefix' => env('BROADCAST_AUTH_PREFIX', 'broadcasting'),
];
```

Les variables d'environnement correspondantes se définissent dans votre fichier `.env` :

```env
BROADCAST_DRIVER=mercure
MERCURE_HUB_URL=http://localhost:3000/.well-known/mercure
MERCURE_JWT_SECRET=VotreCleSecreteEditeur
```

| Variable | Description |
|---|---|
| `BROADCAST_DRIVER` | Driver de broadcasting par défaut. À positionner sur `mercure` en production. |
| `MERCURE_HUB_URL` | URL complète du point de publication du hub Mercure, chemin `/.well-known/mercure` inclus. |
| `MERCURE_JWT_SECRET` | Clé secrète servant à signer le token. Elle doit correspondre exactement à celle configurée côté hub Mercure. |
| `MERCURE_JWT_TOKEN` | Token JWT accordant les droits de publication. Tonka le génère automatiquement à partir de la clé secrète ; si vous renseignez cette variable, Tonka utilisera ce token pour toute publication. Il est aussi possible de le générer avec la commande `broadcast:generate-token`. |
| `MERCURE_JWT_TTL` | Durée de vie par défaut, en secondes, des tokens d'abonnement générés par `Broadcast::subscribe()` (900s = 15 minutes par défaut). |

`MERCURE_JWT_TOKEN` est un élément de sécurité critique : ce token est envoyé en tant que Bearer token dans l'en-tête `Authorization` de chaque requête de publication, et doit être signé avec la même clé que celle configurée côté hub (`MERCURE_PUBLISHER_JWT_KEY`). Sans token valide, le hub rejette la publication avec une réponse `401 Unauthorized`.

> ⚠️ **Important :** ne committez jamais la clé secrète JWT ni le token de publication dans le contrôle de version. La clé doit être une chaîne aléatoire cryptographiquement robuste, propre à chaque environnement (vous pouvez en générer une avec la commande `broadcast:generate-key`). Injectez-la via des variables d'environnement ou un gestionnaire de secrets au déploiement.

Les drivers `log` et `null` sont destinés au développement local et aux tests :

- **`log`** : les événements sont écrits dans les journaux de l'application.
- **`null`** : tous les événements sont silencieusement ignorés.

---

## Vue d'Ensemble

Avant d'entrer dans le détail de l'implémentation, il est essentiel de comprendre comment fonctionne, de bout en bout, le broadcasting Mercure de Tonka. Contrairement aux architectures WebSocket classiques où le serveur maintient une connexion persistante avec chaque client, SSE/Mercure découple l'éditeur (votre application Tonka) des abonnés (les navigateurs) via un hub central.

### Fonctionnement du Broadcasting SSE

Lorsqu'un événement broadcasté est dispatché depuis votre application, le flux suivant se déroule :

1. **Dispatch de l'événement :** votre code appelle `event(new CommentPosted(...))` ou injecte `EventDispatcherInterface` et appelle `$dispatcher->dispatch(...)`. La classe d'événement implémente `ShouldBroadcastInterface`, signalant à Tonka que cet événement doit être transmis aux clients.
2. **Sérialisation :** Tonka sérialise les propriétés publiques de l'événement (ou le tableau retourné par `broadcastWith()`) en une charge utile JSON. L'événement reçoit un nom court — le nom de classe sans son namespace par défaut (`CommentPosted`), ou un nom personnalisé via `broadcastAs()`.
3. **Résolution des topics :** la méthode `broadcastOn()` retourne un ou plusieurs topics Mercure.
4. **Publication au hub :** Tonka publie l'événement au hub Mercure configuré, avec le token de l'éditeur, les données, les topics ciblés et le nom de l'événement.
5. **Dispatch par le hub :** le hub valide le token JWT, puis diffuse l'événement à tous les clients abonnés à au moins l'un des topics ciblés.
6. **Réception côté client :** la connexion `EventSource` du navigateur reçoit l'événement et déclenche le listener correspondant.

```
┌──────────────┐     ┌──────────────┐     ┌──────────────┐     ┌──────────────┐
│  Dispatch    │────▶│ Sérialisation│────▶│ Publication  │────▶│    Hub       │
│  événement   │     │  du payload  │     │  au hub      │     │   Mercure    │
└──────────────┘     └──────────────┘     └──────────────┘     └──────┬───────┘
                                                                       │
                                                             Flux SSE  │
                                                                       ▼
┌──────────────┐     ┌──────────────┐     ┌──────────────┐     ┌──────────────┐
│  Exécution   │◀────│ EventSource  │◀────│  Diffusion   │◀────│  Validation  │
│  du callback │     │  reçoit      │     │  aux clients │     │  du JWT      │
└──────────────┘     └──────────────┘     └──────────────┘     └──────────────┘
```

Une caractéristique clé de cette architecture : l'application Tonka n'interagit jamais directement avec les navigateurs. Elle traite le hub Mercure comme un simple point d'accès HTTP. Votre application reste sans état vis-à-vis des connexions temps réel, ce qui simplifie considérablement le scaling et le déploiement.

<div id="exemple-dapplication"></div>
### Exemple d'Application

Prenons un exemple concret : une application de collaboration d'équipe où les utilisateurs doivent être notifiés immédiatement lorsqu'un nouveau commentaire est posté sur un document partagé.

**Étape 1 : définir un événement broadcasté**

```php
namespace App\Events;

use App\Models\Comment;
use Broadcaster\Event\ShouldBroadcastInterface;

class CommentPosted implements ShouldBroadcastInterface
{
    /**
     * The comment instance.
     *
     * @var \App\Models\Comment
     */
    public $comment;

    /**
     * Create a new event instance.
     */
    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
    }

    /**
     * Get the topics the event should broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return ['documents.' . $this->comment->document_id];
    }
}
```

**Étape 2 : dispatcher l'événement**

```php
namespace App\Http\Controllers;

use App\Models\Comment;
use App\Events\CommentPosted;
use App\Http\Requests\StoreCommentRequest;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CommentController extends Controller
{
    public function __construct(
        private EventDispatcherInterface $dispatcher
    ) {}

    /**
     * Store a newly created comment.
     */
    public function store(StoreCommentRequest $request)
    {
        $comment = Comment::create($request->validated());

        // L'événement est publié au hub Mercure et délivré à tous les abonnés
        $this-dispatcher->dispatch(new CommentPosted($comment));

        return response()->status(201)->json($comment);
    }
}
```

**Étape 3 : s'abonner côté client**

```javascript
import broadcaster from './broadcaster';

// Abonnement à un canal public
const channel = broadcaster.channel('notifications');

// Écoute d'un événement spécifique
channel.listen('CommentPosted', (data) => {
    console.log('Notification received:', data.message);
    alert(`New Alert: ${data.title}`);
});
```

**Étape 4 : sécuriser le topic (s'il est privé)**

Si le document ne doit être accessible qu'aux membres autorisés de l'équipe, configurez le topic comme privé. Le client doit alors obtenir un JWT d'abonnement auprès de votre application.

Deux approches coexistent sur cette page pour émettre ce token : un endpoint générique centralisé (`routes/broadcasting.php`, section [Génération des Tokens d'Abonnement](#génération-des-tokens-dabonnement)) ou un endpoint dédié à une ressource précise, comme ici :

```php
// routes/web.php

use App\Models\Document;
use Clicalmani\Core\Http\RequestInterface;

Route::get('/documents/:doc/token', function (RequestInterface $request, int $doc) {
    $document = Document::find($doc);

    // Vérifier que l'utilisateur a accès à ce document
    if (!$document->team->hasMember($request->user())) {
        abort(403);
    }

    // Générer un JWT d'abonnement pour ce topic précis
    return response()->json([
        'token' => Broadcast::subscribe(["documents.{$document->id}"]),
    ]);
})->middleware('auth');
```

Côté client, le token est transmis à `TonkaBroadcaster` :

```javascript
const response = await fetch(`/documents/${documentId}/token`);
const { token } = await response.json();

import TonkaBroadcaster from 'tonka-broadcaster';

const broadcaster = new TonkaBroadcaster({
    hubUrl: 'http://localhost:3000/.well-known/mercure',
    token: () => token
});

export default broadcaster;
```

Le hub Mercure valide le token et n'autorise la connexion que s'il donne accès au topic demandé. Sans token valide, le hub répond `403 Forbidden` et la connexion `EventSource` échoue.

> 💡 **À retenir :** le broadcasting Mercure abstrait la complexité des connexions persistantes, de la logique de reconnexion et de la diffusion des événements. Votre application Tonka reste un simple service HTTP, ignorant le nombre de clients connectés ou l'état de leurs connexions.

---

## Définition des Événements Broadcastés

Un événement broadcasté est une classe standard qui implémente l'interface `ShouldBroadcastInterface` (`Broadcaster\Event\ShouldBroadcastInterface`). Cette interface signale au framework que l'événement doit être publié au hub Mercure et délivré via SSE. La seule méthode strictement obligatoire est `broadcastOn()`.

Pour générer un événement broadcasté :

```bash
php tonka make:event CommentPosted --broadcast
```

```php
namespace App\Events;

use Broadcaster\Event\ShouldBroadcastInterface;

class CommentPosted implements ShouldBroadcastInterface
{
    /**
     * Create a new event instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the topics the event should broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [];
    }
}
```

### Nom de Diffusion

Par défaut, Tonka utilise le nom court de la classe de l'événement (sans son namespace) comme nom de diffusion — par exemple `CommentPosted` pour `App\Events\CommentPosted`. Vous pouvez définir un nom personnalisé via `broadcastAs()` :

```php
/**
 * Get the broadcast name of the event.
 *
 * @return string
 */
public function broadcastAs()
{
    return 'comment.posted';
}
```

```javascript
channel.listen('comment.posted', (data) => {
    // ...
});
```

### Données de Diffusion

Toutes les propriétés publiques de l'événement sont automatiquement sérialisées dans la charge utile :

```php
namespace App\Events;

use App\Models\Comment;
use Broadcaster\Event\ShouldBroadcastInterface;

class CommentPosted implements ShouldBroadcastInterface
{
    /**
     * The comment instance.
     *
     * @var \App\Models\Comment
     */
    public $comment;

    /**
     * The user who posted the comment.
     *
     * @var \App\Models\User
     */
    public $author;

    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
        $this->author = $comment->author;
    }
}
```

Pour un contrôle plus fin, implémentez `broadcastWith()`, qui remplace entièrement la sérialisation par défaut des propriétés publiques :

```php
/**
 * Get the data to broadcast.
 *
 * @return array
 */
public function broadcastWith()
{
    return [
        'comment_id' => $this->comment->id,
        'document_id' => $this->comment->document_id,
        'body' => $this->comment->body,
        'author' => [
            'id' => $this->author->id,
            'name' => $this->author->name,
            'avatar' => $this->author->avatar_url,
        ],
        'created_at' => $this->comment->created_at->toIso8601String(),
    ];
}
```

Cette approche est recommandée pour ne transmettre que ce dont le client a strictement besoin, et éviter l'exposition accidentelle d'attributs sensibles (mots de passe hachés, métadonnées internes).

### Topics de Diffusion

Les topics se définissent dans `broadcastOn()` :

```php
public function broadcastOn()
{
    return [
        'documents.' . $this->comment->document_id,
        'team.' . $this->comment->document->team_id,
    ];
}
```

**Conventions de nommage**

| Motif | Exemple | Cas d'usage |
|---|---|---|
| `{ressource}.{id}` | `documents.42`, `orders.187` | Mises à jour propres à une ressource |
| `{ressource}.{id}.{sous-ressource}` | `documents.42.comments` | Événements de ressources imbriquées |
| `users.{id}` | `users.15` | Notifications propres à un utilisateur |
| `teams.{id}` | `teams.7` | Diffusions de groupe/équipe |
| `system.{événement}` | `system.maintenance` | Annonces globales |

> 💡 **Recommandation :** utilisez des noms simples et hiérarchiques séparés par des points pour les applications internes, et réservez les URI complets aux API publiques ou aux intégrations inter-services.

### Conditions de Diffusion

```php
/**
 * Determine if this event should broadcast.
 *
 * @return bool
 */
public function broadcastWhen()
{
    return $this->comment->document->isPublished();
}
```

Lorsque `broadcastWhen()` retourne `false`, l'événement n'est **pas** publié au hub — mais ses listeners côté serveur s'exécutent normalement.

### Broadcasting et Transactions de Base de Données

La requête de publication vers le hub étant un appel HTTP, elle peut aboutir **avant** le commit d'une transaction englobante, exposant les clients à une condition de course. Tonka résout ce problème via `ShouldDispatchAfterCommitInterface` :

```php
namespace App\Events;

use App\Models\Comment;
use Broadcaster\Event\ShouldBroadcastInterface;
use Broadcaster\Event\ShouldDispatchAfterCommitInterface;

class CommentPosted implements ShouldBroadcastInterface, ShouldDispatchAfterCommitInterface
{
    // ...

    public function attempts(): int
    {
        return 5;
    }

    public function sleep(): int
    {
        return 100;
    }
}
```

> En interne, Tonka utilise la méthode `deadlock()` de la classe `DB` pour gérer ces transactions, ce qui permet de faire face aux interblocages entre transactions concurrentes (voir [Gestion des Deadlocks](orm.md#gestion-des-deadlocks)). `attempts()` retourne le nombre maximal de tentatives, et `sleep()` le délai d'attente (en millisecondes) entre deux tentatives — soit exactement les deux arguments de `DB::deadlock($callback, $tentatives, $délaiMs)`.

```php
use Clicalmani\Core\Support\Facades\DB;
use App\Events\CommentPosted;

DB::transaction(function () use ($request) {
    $comment = Comment::create($request->validated());

    // L'événement n'est publié au hub qu'après le commit de la transaction
    event(new CommentPosted($comment));
});
```

> 💡 **Bonne pratique :** implémentez systématiquement `ShouldDispatchAfterCommitInterface` sur les événements broadcastés dispatchés au sein de transactions.

---

## Autorisation des Topics

Une application Tonka + Mercure distingue deux catégories de topics :

- **Topics publics :** tout client peut s'y abonner, sans token.
- **Topics privés :** l'abonnement requiert un token JWT valide, limité à des topics précis et à une durée de vie restreinte.

### Topics Privés

Le flux d'autorisation est le suivant :

1. Le client demande un token d'abonnement à votre application.
2. Votre application vérifie l'identité de l'utilisateur et détermine les topics auxquels il a droit.
3. Votre application génère un JWT signé contenant les topics autorisés dans la revendication `mercure.subscribe`.
4. Le client transmet ce token au hub à l'ouverture de l'`EventSource`.
5. Le hub valide la signature et les revendications du token. Si valide, l'abonnement est accepté ; sinon, `403 Forbidden`.

```
┌──────────────┐     1. Demande de token    ┌──────────────┐
│ Navigateur   │ ─────────────────────────▶ │   App Tonka  │
│  (Client)    │ ◀───────────────────────── │  (Auth)      │
└──────┬───────┘     2. Retour du JWT       └──────────────┘
       │
       │ 3. Connexion avec le JWT
       ▼
┌──────────────┐
│ Hub Mercure  │
└──────────────┘
```

<div id="callbacks-dautorisation"></div>
### Callbacks d'Autorisation

La logique d'autorisation des topics se définit dans `routes/channels.php`. Chaque règle s'enregistre via `Channel::make()`, qui accepte un motif de topic et un callback déterminant si l'utilisateur authentifié peut s'abonner :

```php
// routes/channels.php

use App\Models\Order;
use Broadcaster\Channels\Channel;

Channel::make('orders.{order}', function ($user, $orderId) {
    $order = Order::find($orderId);

    return $order && $order->user_id === $user->id;
});
```

Pour les applications multi-tenant :

```php
// routes/channels.php

use App\Models\Workspace;
use App\Models\Project;

Channel::make('workspaces.{workspace}.projects.{project}', function ($user, int $workspaceId, int $projectId) {
    $workspace = Workspace::find($workspaceId);

    if (!$workspace || !$workspace->hasMember($user)) {
        return false;
    }

    $project = Project::where('workspace_id = ? AND id = ?', [$workspaceId, $projectId])->first();

    return $project !== null;
});
```

Pour un topic lié à un document précis, avec une durée de vie dédiée plus courte que la valeur globale :

```php
// routes/channels.php

use App\Models\Document;

Channel::make('documents.{docId}', function ($user, $docId) {
    $document = Document::find($docId);

    return $document && $document->owner === $user->id;
})->ttl(120); // TTL court = 2 minutes
```

> ⚠️ **Sécurité :** ne faites jamais confiance aveuglément aux entrées utilisateur dans un callback d'autorisation. Vérifiez toujours que l'utilisateur authentifié a une relation légitime avec la ressource demandée — et surtout, assurez-vous que cette vérification est bien **atteignable** dans le code (un `return` prématuré, une condition inversée, ou une exception avalée silencieusement peuvent neutraliser une vérification par ailleurs correcte). Relisez chaque callback d'autorisation comme vous relieriez une vérification d'autorisation de contrôleur.

<div id="generation-des-tokens-dabonnement"></div>
### Génération des Tokens d'Abonnement

Deux façons d'émettre un token d'abonnement cohabitent dans cette documentation.

**Un endpoint générique**, centralisant l'émission pour n'importe quel ensemble de topics, en s'appuyant sur les règles de `routes/channels.php` :

```php
// routes/broadcasting.php

use Clicalmani\Core\Support\Facades\Route;

Route::post('/auth', function () {
    $user = auth()?->user();

    if (!$user) {
        return response()->unauthorized();
    }

    $topics = request()->input('topics', []);

    $granted = channel_authorizer()->authorize($user, $topics);
    $token = Broadcast::subscribe($granted['channels']);

    return response()->json(['token' => $token]);
});
```

Ce fichier est monté sous le préfixe défini par `auth_prefix` dans `config/broadcasting.php` (`broadcasting` par défaut) — l'endpoint final est donc `/broadcasting/auth`.

**Un endpoint dédié à une ressource**, pour un contrôle d'accès plus spécifique couplé à une logique métier propre au modèle :

```php
// routes/api.php

use App\Models\Document;
use Clicalmani\Core\Support\Facades\Broadcast;

Route::get('/documents/:docId/token', function (RequestInterface $request, int $docId) {
    $document = Document::find($docId);

    if (!$document || !$document->canSubscribe($request->user()->id)) {
        abort(403, 'You are not authorized to subscribe to this document.');
    }

    $topics = $request->input('topics', []);
    $granted = channel_authorizer()->authorize($request->user(), $topics);
    $token = Broadcast::subscribe($granted['channels']);

    return response()->json(['token' => $token]);
})->middleware('auth');
```

> **Quand utiliser lequel ?** L'endpoint générique convient à la majorité des cas — il se contente des règles déjà déclarées dans `routes/channels.php`. L'endpoint dédié a du sens lorsqu'une ressource précise porte une logique d'autorisation supplémentaire, propre à elle (ici, `$document->canSubscribe()`), en plus des règles génériques. L'endpoint dédié peut être aussi le choix ultime pour les API, car les routes définies dans `routes/broadcasting.php` ne sont pa stateless (elles sont associées nativement au middleware `web`).

`Broadcast::subscribe()` accepte un tableau de topics et une durée de vie optionnelle (en secondes, `MERCURE_JWT_TTL` par défaut), et retourne un JWT signé comprenant `mercure.subscribe` (la liste des topics autorisés), `iat` et `exp` :

```php
// Token valide 15 minutes (900 secondes)
$token = Broadcast::subscribe(
    ['documents.' . $document->id],
    900
);
```

Côté client :

```javascript
const response = await fetch(`/api/documents/${documentId}/token`, {
    headers: {
        'Authorization': `Bearer ${userAccessToken}`,
    },
});

if (!response.ok) {
    console.error('Failed to obtain subscription token');
    return;
}

const { token } = await response.json();

const broadcaster = new TonkaBroadcaster({
    hubUrl: 'http://localhost:3000/.well-known/mercure',
    token: () => token
});
```

> ℹ️ **Sécurité des tokens :** les tokens d'abonnement doivent avoir une durée de vie courte. Le client doit en demander un nouveau à l'expiration.

---

## Dispatch des Événements Broadcastés

### Dispatch depuis un Contrôleur

Deux façons équivalentes de dispatcher un événement — à ne pas combiner pour un même événement, au risque de le publier deux fois :

**Option A — le helper global `event()`, le plus simple :**

```php
namespace App\Http\Controllers;

use App\Models\Comment;
use App\Events\CommentPosted;
use App\Http\Requests\StoreCommentRequest;

class CommentController extends Controller
{
    public function store(StoreCommentRequest $request)
    {
        $comment = Comment::create($request->validated());

        event(new CommentPosted($comment));

        return response()->status(201)->json($comment);
    }
}
```

**Option B — injection explicite d'`EventDispatcherInterface`, pour un contrôleur dont le dispatch d'événements est une responsabilité centrale et testable :**

```php
namespace App\Http\Controllers;

use App\Models\Comment;
use App\Events\CommentPosted;
use App\Http\Requests\StoreCommentRequest;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CommentController extends Controller
{
    public function __construct(
        private EventDispatcherInterface $dispatcher
    ) {}

    public function store(StoreCommentRequest $request)
    {
        $comment = Comment::create($request->validated());

        $this->dispatcher->dispatch(new CommentPosted($comment));

        return response()->status(201)->json($comment);
    }
}
```

Lorsque l'une ou l'autre méthode reçoit une instance implémentant `ShouldBroadcastInterface`, Tonka effectue deux actions indépendantes : exécution des listeners synchrones côté serveur, et publication HTTP au hub Mercure. Un échec de l'une n'empêche pas l'autre.

### Diffusion vers Plusieurs Topics

```php
public function broadcastOn()
{
    return [
        'documents.' . $this->comment->document_id,
        'team.' . $this->comment->document->team_id,
        'users.' . $this->comment->document->author_id,
    ];
}
```

Le hub reçoit l'événement une seule fois et le diffuse à tous les abonnés de l'un quelconque des topics spécifiés.

Pour une sélection dynamique :

```php
public function broadcastOn()
{
    $topics = [
        'documents.' . $this->comment->document_id,
    ];

    if ($this->comment->document->team_id) {
        $topics[] = 'team.' . $this->comment->document->team_id;
    }

    foreach ($this->comment->mentionedUsers() as $user) {
        $topics[] = 'users.' . $user->id;
    }

    return $topics;
}
```

### Diffusion sans Classe d'Événement

```php
namespace App\Http\Controllers;

use App\Models\Document;

class DocumentLockController extends Controller
{
    public function lock(Document $document)
    {
        $document->lock($user = request()->user());

        broadcast('documents.' . $document->id, 'document.locked', [
            'document_id' => $document->id,
            'locked_by' => [
                'id' => $user->id,
                'name' => $user->name,
            ],
            'locked_at' => now()->toIso8601String(),
        ]);

        return response()->json(['status' => 'locked']);
    }
}
```

| Argument | Type | Description |
|---|---|---|
| `$topic` | `string` | Le topic Mercure ciblé. |
| `$event` | `string` | Le nom de l'événement diffusé. |
| `$payload` | `array` | Les données à transmettre aux clients. |

Pratique pour les cas simples, mais des classes d'événement dédiées restent recommandées en production (réutilisables, testables, et bénéficiant de `broadcastWhen()`, `broadcastWith()`, `ShouldDispatchAfterCommitInterface`).

---

## Configuration Côté Client

`TonkaBroadcaster` est une bibliothèque TypeScript légère encapsulant l'API native `EventSource`.

### Installation

```typescript
// resources/js/broadcaster.ts
import TonkaBroadcaster from 'tonka-broadcaster';

const broadcaster = new TonkaBroadcaster({
    hubUrl: import.meta.env.VITE_MERCURE_URL || 'http://localhost:3000/.well-known/mercure',
    token: () => getSubscriptionToken()
});

export default broadcaster;
```

```typescript
import broadcaster from './broadcaster';
```

### Connexion au Hub Mercure

```typescript
const broadcaster = new TonkaBroadcaster({
    hubUrl: 'http://localhost:3000/.well-known/mercure',
    token: () => getSubscriptionToken(),
});
```

La fonction `token` est appelée à chaque ouverture d'un canal privé.

### Abonnement aux Topics Publics

```typescript
const channel = broadcaster.channel('documents.42');

channel.listen('comment.posted', (data) => {
    console.log('New comment:', data);
    appendCommentToUI(data);
});
```

En interne, `TonkaBroadcaster` transforme le nom de canal en URI de topic Mercure complet :

```typescript
// 'documents.42' devient 'https://tonka.framework/channels/documents.42'
const topicUrl = `https://tonka.framework/channels/${channelName}`;
```

```typescript
broadcaster
    .channel('documents.42')
    .listen('comment.posted', handleNewComment)
    .listen('document.locked', handleDocumentLocked);
```

### Abonnement aux Topics Privés

```typescript
const channel = broadcaster.private('documents.42');

channel.listen('comment.posted', (data) => {
    appendCommentToUI(data);
});
```

`private()` préfixe automatiquement le canal par `private.` et appelle le résolveur `token`.

### Écoute des Événements

Le nom passé à `listen()` doit correspondre au nom de diffusion de l'événement : son nom de classe court (`CommentPosted`) par défaut, ou le nom défini via `broadcastAs()`.

```php
// Serveur
public function broadcastAs()
{
    return 'comment.posted';
}
```

```typescript
// Client
channel.listen('comment.posted', (data) => {
    console.log('Received:', data);
});
```

Sans `broadcastAs()` :

```typescript
channel.listen('CommentPosted', (data) => {
    console.log('Received:', data);
});
```

Écouter tous les événements d'un canal :

```typescript
channel.listen('*', (payload) => {
    console.log('Raw event received:', payload);
});
```

À réserver au développement et au débogage.

### Reconnexion et Récupération des Événements

Dès qu'une connexion est perdue, `TonkaChannel` ferme l'`EventSource` automatiquement et prend le relais avec sa propre politique de reconnexion — ce qui lui permet de **rafraîchir le token d'abonnement** et de **reprendre le flux sans perte** via `lastEventID`.

#### Politique de reconnexion

Par défaut, Tonka applique un **backoff exponentiel avec jitter** :

| Paramètre | Défaut | Rôle |
|---|---|---|
| `maxRetries` | `10` | Nombre maximal de tentatives consécutives avant abandon (`failed`) |
| `baseDelayMs` | `1000` | Délai de base entre deux tentatives |
| `maxDelayMs` | `30_000` | Plafond du délai (le backoff ne dépasse jamais cette valeur) |

Le facteur aléatoire (jitter entre 50 % et 100 %) évite que tous les clients d'un même déploiement se reconnectent simultanément après une coupure du hub — un classique « thundering herd ».

Ces valeurs sont configurables globalement au niveau du broadcaster :

```typescript
const broadcaster = new TonkaBroadcaster({
    hubUrl: 'http://localhost:3000/.well-known/mercure',
    token: () => getSubscriptionToken(),
    reconnect: {
        maxRetries: 5,
        baseDelayMs: 500,
        maxDelayMs: 10_000,
    },
});
```

Pour désactiver entièrement la reconnexion (utile en environnement de test, ou si vous gérez vous-même la reprise) :

```typescript
const broadcaster = new TonkaBroadcaster({
    hubUrl: '...',
    reconnect: false,
});
```

Dans ce cas, la première erreur réseau fait passer le cannal à l'état `failed` sans nouvelle tentative.

#### Rafraîchissement automatique du token

À chaque reconnexion, Tonka **réexécute le résolveur `token`** avant de rouvrir la connexion. C'est ce qui permet de rester connecté au-delà de la durée de vie (`ttl`) d'un JWT d'abonnement, sans aucun code de votre part :

```typescript
const broadcaster = new TonkaBroadcaster({
    hubUrl: 'http://localhost:3000/.well-known/mercure',
    // Appelé à chaque (re)connexion — le token peut donc être renouvelé à la volée
    token: async () => fetchSubscriptionToken(),
});
```

Si le résolveur échoue ou retourne `undefined` sur un cannal privé (et que `withCredentials` est désactivé), le cannal passe à l'état `failed` avec une `TonkaError` de code `AUTH_MISSING`.

#### Reprise sans perte (`lastEventID`)

Mercure conserve un historique d'événements et accepte le paramètre `lastEventID` : lors d'une reconnexion, Tonka transmet l'identifiant du dernier événement reçu, et le hub renvoie les événements manqués. Ce mécanisme est **automatique** — vous n'avez rien à faire.

> ⚠️ **Nuance importante :** cette reprise n'est effective que si le hub Mercure est configuré pour conserver un historique (`history` dans le Caddyfile, avec un backend de stockage). Sans historique, le hub ne peut pas rejouer les événements manqués ; Tonka se reconnecte simplement et reprend le flux à partir du présent.

#### Observer les erreurs

Chaque cannal expose une méthode `onError()` qui reçoit des `TonkaError` typées :

```typescript
const channel = broadcaster.channel('documents.42');

const unsubscribe = channel.onError((error) => {
    switch (error.code) {
        case 'CONNECTION':
            console.warn('Connexion perdue, reconnexion en cours…', error.message);
            break;
        case 'MAX_RETRIES':
            console.error('Abandon après trop de tentatives', error.channel);
            showOfflineBanner();
            break;
        case 'AUTH_MISSING':
            console.error('Token manquant ou invalide', error.channel);
            redirectToLogin();
            break;
        case 'PARSE':
            console.error('Payload JSON invalide reçu du hub', error.cause);
            break;
        case 'LISTENER':
            console.error('Une exception a été levée dans un de vos callbacks', error.cause);
            break;
        case 'UNSUPPORTED':
            console.error("EventSource n'est pas disponible dans ce navigateur");
            break;
    }
});

// Plus tard, pour retirer le handler
unsubscribe();
```

Les codes d'erreur possibles sont :

| Code | Signification |
|---|---|
| `UNSUPPORTED` | `EventSource` indisponible (SSR, navigateur obsolète) |
| `AUTH_MISSING` | Cannal privé sans token résolvable ni cookies |
| `CONNECTION` | Erreur réseau ou hub injoignable |
| `MAX_RETRIES` | Reconnexion abandonnée après `maxRetries` tentatives |
| `PARSE` | JSON invalide reçu du hub |
| `LISTENER` | Une exception a été levée dans l'un de vos callbacks |

Un **handler global** peut être branché au niveau du broadcaster, pour toutes les erreurs de tous les cannaux :

```typescript
const broadcaster = new TonkaBroadcaster({
    hubUrl: '...',
    onError: (error) => {
        console.error(`[${error.code}] ${error.message} (channel: ${error.channel})`);
        logToMonitoring(error);
    },
});
```

#### Observer les changements d'état

Chaque cannal expose également `onStateChange()`, qui notifie les transitions entre les cinq états possibles :

```typescript
const unsubscribe = channel.onStateChange((state) => {
    switch (state) {
        case 'connecting':   setStatus('Connexion en cours…'); break;
        case 'open':         setStatus('Connecté');            break;
        case 'reconnecting': setStatus('Reconnexion…');        break;
        case 'closed':       setStatus('Fermé');               break;
        case 'failed':       setStatus('Échec de connexion');  break;
    }
});
```

Utile par exemple pour afficher un bandeau « hors ligne » ou un indicateur de statut dans l'UI.

> 💡 **Astuce :** combinez `onStateChange` (pour l'état global de la connexion) et `onError` (pour le détail des erreurs). Un state `reconnecting` sans erreur `CONNECTION` associée est rare, mais possible si une erreur est levée avant la première tentative.

---

### Fermeture des Connexions

Deux méthodes permettent de fermer des connexions, à deux niveaux de granularité :

#### Fermer un cannal précis

`channel.close()` ferme une connexion SSE et libère les ressources associées :

```typescript
const channel = broadcaster.channel('documents.42');

// Plus tard — au démontage d'un composant, à la navigation, etc.
channel.close();
```

Après `close()` :
- l'`EventSource` est fermé et détaché ;
- le timer de reconnexion en cours, s'il existe, est annulé ;
- tous les listeners (`listen()`, `once()`), handlers d'erreur (`onError()`) et handlers d'état (`onStateChange()`) sont supprimés ;
- l'état du cannal passe à `closed` ;
- un `close()` déclenché pendant une attente de token invalide la connexion en cours (via un compteur interne `connectionId`), empêchant toute ouverture parasite ultérieure.

> ⚠️ `close()` est **définitif** : le cannal ne peut pas être rouvert. Un appel ultérieur à `broadcaster.channel('documents.42')` recrée une nouvelle instance de cannal.

#### Quitter un cannal via le broadcaster

`broadcaster.leave()` ferme un ou deux cannaux selon le `kind` passé en second argument :

```typescript
// Ferme le cannal public uniquement
broadcaster.leave('documents.42', 'public');

// Ferme le cannal privé uniquement (équivalent à 'private.documents.42')
broadcaster.leave('documents.42', 'private');

// Ferme les deux (public ET privé) — comportement par défaut
broadcaster.leave('documents.42');
```

| Signature | Effet |
|---|---|
| `leave(name)` | Ferme `name` **et** `private.name` s'ils existent |
| `leave(name, 'public')` | Ferme uniquement `name` |
| `leave(name, 'private')` | Ferme uniquement `private.name` |

C'est la méthode recommandée dans la plupart des cas : elle évite d'avoir à se souvenir si le cannal a été ouvert en public ou en privé, et nettoie l'entrée correspondante dans la table interne du broadcaster.

#### Fermer tous les cannaux

`broadcaster.disconnect()` ferme **tous** les cannaux actifs et vide la table interne :

```typescript
// Au logout, au unmount d'une page, ou avant un rechargement complet
broadcaster.disconnect();
```

C'est la méthode à privilégier lors d'une déconnexion utilisateur ou d'un changement de contexte d'application : elle garantit qu'aucune connexion SSE résiduelle ne subsiste.

#### Recréation après fermeture

Un cannal fermé (`closed`) ou ayant abandonné la reconnexion (`failed`) est automatiquement recréé au prochain appel de `broadcaster.channel()` ou `broadcaster.private()` du même nom :

```typescript
broadcaster.leave('documents.42');

// Plus tard — un nouvel EventSource est ouvert, une nouvelle instance est retournée
const fresh = broadcaster.channel('documents.42');
```

Ce comportement évite de manipuler des instances « mortes » : vous pouvez appeler `channel()` à tout moment, en sachant que le broadcaster vous retourne toujours une instance utilisable.

> 💡 **Bonne pratique :** la plupart des navigateurs plafonnent le nombre de connexions SSE simultanées par domaine en HTTP/1.1 (6 en général). Fermez systématiquement les cannaux inutilisés — `close()`, `leave()` ou `disconnect()` selon le contexte — pour éviter d'atteindre cette limite, qui se manifesterait par des connexions restant bloquées en `connecting`.

---

## Broadcasting des Notifications

Le système de notifications de Tonka s'intègre nativement à la couche de broadcasting Mercure.

Le flux est le suivant :

1. Votre application envoie une notification à un modèle utilisant le trait `Notifiable`.
2. Si la notification implémente `ShouldBroadcastInterface`, Tonka la publie au hub sur le topic `users.{id}`.
3. Le navigateur de l'utilisateur, abonné à ce topic privé, la reçoit via `EventSource`.
4. `TonkaBroadcaster` achemine la notification vers le listener approprié.

### Mise en Place

```bash
php tonka make:notification OrderShipped
```

```php
namespace App\Notifications;

use App\Models\Order;
use Clicalmani\Database\Notifications\NotificationInterface;
use Broadcaster\Event\ShouldBroadcastInterface;

class OrderShipped implements NotificationInterface, ShouldBroadcastInterface
{
    /**
     * The order instance.
     *
     * @var \App\Models\Order
     */
    protected $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['broadcast', 'database'];
    }
}
```

Pour activer la diffusion, la classe doit implémenter `ShouldBroadcastInterface` et inclure `'broadcast'` dans sa méthode `via()`.

> ⚠️ **Incohérence à clarifier avec toi :** cette section montre une notification implémentant directement `NotificationInterface`, tandis que la section suivante (« Formatage ») montre une notification `extends Notification` (classe abstraite `Clicalmani\Foundation\Notifications\Notification`). Les deux ne peuvent pas être la bonne convention simultanément — précise laquelle est la forme canonique.

Le topic de diffusion suit automatiquement la convention `users.{id}` — inutile d'implémenter `broadcastOn()` sur une notification.

```php
use App\Models\User;
use App\Notifications\OrderShipped;

$user = User::find(1);
$order = Order::find(42);

$user->notify(new OrderShipped($order));
```

### Formatage des Notifications Diffusées

Personnalisez la charge diffusée via `toBroadcast()` :

```php
namespace App\Notifications;

use App\Models\Order;
use Clicalmani\Foundation\Notifications\Notification;
use Broadcaster\Event\ShouldBroadcastInterface;

class OrderShipped extends Notification implements ShouldBroadcastInterface
{
    protected $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function via($notifiable)
    {
        return ['broadcast', 'database'];
    }

    /**
     * Get the broadcastable representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toBroadcast($notifiable)
    {
        return [
            'notification_id' => $this->id,
            'type' => 'order.shipped',
            'title' => 'Order Shipped',
            'message' => "Your order #{$this->order->reference} has been shipped and is on its way!",
            'action_url' => route('orders.show', $this->order->id),
            'shipped_at' => $this->order->shipped_at->toIso8601String(),
        ];
    }

    /**
     * Get the array representation for database storage.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toDatabase($notifiable)
    {
        return [
            'order_id' => $this->order->id,
            'order_reference' => $this->order->reference,
            'shipped_at' => $this->order->shipped_at,
        ];
    }
}
```

`toBroadcast()` retourne un tableau sérialisé et livré par SSE, totalement indépendant des données stockées par le canal `database`.

### Écoute des Notifications

```typescript
const broadcaster = new TonkaBroadcaster({
    hubUrl: 'http://localhost:3000/.well-known/mercure',
    token: () => getSubscriptionToken(),
});

const channel = broadcaster.private(`users.${currentUserId}`);

channel.listen('*', (notification) => {
    console.log('New notification received:', notification);
    incrementNotificationBadge();
    showToast(notification.title, notification.message);
});
```

Écouter un type de notification précis — en cohérence avec la convention de nom court des événements :

```typescript
channel.listen('OrderShipped', (notification) => {
    playSound('package-shipped');
    showCelebrationAnimation();
});
```

Ou avec un nom personnalisé :

```php
public function broadcastAs()
{
    return 'order.shipped';
}
```

```typescript
channel.listen('order.shipped', (notification) => {
    playSound('package-shipped');
    showCelebrationAnimation();
});
```

---

## Hub Mercure

Le hub Mercure est le serveur de dispatch central qui alimente le système de broadcasting de Tonka — un binaire autonome et léger écrit en Go, indépendant de votre application PHP.

### Installation du Hub

**Linux**

```bash
wget https://github.com/dunglas/mercure/releases/download/v0.16.1/mercure_0.16.1_Linux_x86_64.tar.gz
tar -xzf mercure_0.16.1_Linux_x86_64.tar.gz
sudo mv mercure /usr/local/bin/mercure
sudo chmod +x /usr/local/bin/mercure
mercure --version
```

**Windows**

Téléchargez le binaire depuis la page des releases et extrayez-le dans un répertoire du `PATH`, ou utilisez l'image Docker via Docker Desktop.

### Configuration du Hub (Caddyfile)

```bash
sudo mkdir -p /etc/mercure
```

```caddyfile
# /etc/mercure/Caddyfile

{
    admin 127.0.0.1:2022
    order mercure after encode
}

http://:3000 {
    route {
        mercure {
            publisher_jwt {$MERCURE_JWT_SECRET}
            subscriber_jwt {$MERCURE_JWT_SECRET}
            anonymous
            publish_origins *
            cors_origins http://localhost:8000

            # Historique des événements — nécessaire pour la reprise via lastEventID
            history_size 10000
            history_backend boltdb
            history_bolt_path /var/lib/mercure/history.db

            # Pour PostgreSQL (recommandé en production multi-nœuds) :
            # history_backend postgres
            # history_postgres_dsn "postgres://mercure:secret@127.0.0.1:5432/mercure?sslmode=disable"

            # Pour Redis :
            # history_backend redis
            # history_redis_addr 127.0.0.1:6379
            # history_redis_password secret
        }

        respond "Mercure Hub is operational."
    }
}
```

| Directive | Description |
|---|---|
| `admin` | Endpoint interne de l'API d'administration — à lier à `127.0.0.1`. |
| `publisher_jwt` / `subscriber_jwt` | Clé secrète de vérification des tokens d'éditeur/d'abonné. |
| `anonymous` | Autorise les abonnés sans JWT sur les topics publics. |
| `publish_origins` | Origines autorisées à publier (`*` = toutes — à restreindre en production). |
| `cors_origins` | Origines autorisées pour les requêtes CORS du front-end. |
| `history_size` | Nombre maximal d'événements conservés dans l'historique. Au-delà, les plus anciens sont évincés. |
| `history_backend` | Backend de stockage : `boltdb` (défaut), `postgres`, `redis`. |
| `history_bolt_path` | Chemin du fichier BoltDB (backend `boltdb` uniquement). |
| `history_postgres_dsn` | Chaîne de connexion PostgreSQL (backend `postgres` uniquement). |
| `history_redis_addr` / `history_redis_password` | Adresse et mot de passe Redis (backend `redis` uniquement). |

> ⚠️ **Critique :** générez une clé forte avec `openssl rand -base64 32`. Ne la committez jamais.

> ⚠️ **Sans `history_size` > 0, l'historique est désactivé** et la reprise via `lastEventID` n'a aucun effet : le hub ne peut pas rejouer les événements manqués. Tonka se reconnecte alors simplement et reprend le flux à partir du présent.

### Exécution comme Service systemd

```ini
# /etc/systemd/system/mercure.service

[Unit]
Description=Mercure Hub
After=network.target

[Service]
Type=exec
User=mercure
Group=mercure
Environment=MERCURE_JWT_SECRET="<votre-secret-genere>"
ExecStart=/usr/local/bin/mercure run --config /etc/mercure/Caddyfile
Restart=always
RestartSec=3s

[Install]
WantedBy=multi-user.target
```

> Créez l'utilisateur dédié au préalable : `sudo useradd --system --no-create-home mercure`. Évitez `User=root` pour un service exposé au réseau.

```bash
sudo systemctl daemon-reload
sudo systemctl enable mercure
sudo systemctl start mercure
sudo systemctl status mercure
```

```bash
sudo systemctl stop mercure
sudo systemctl restart mercure
sudo journalctl -u mercure -f
```

### Installation sous Windows

```powershell
$env:MERCURE_JWT_SECRET = "<votre-secret-genere>"
mercure run --config C:\mercure\Caddyfile
```

Avec NSSM :

```powershell
nssm install MercureHub "C:\Program Files\mercure\mercure.exe"
nssm set MercureHub AppParameters "run --config C:\mercure\Caddyfile"
nssm set MercureHub AppEnvironmentExtra "MERCURE_JWT_SECRET=<votre-secret-genere>"
nssm set MercureHub Start SERVICE_AUTO_START
nssm start MercureHub
```

### SSL et Reverse Proxy

```nginx
server {
    listen 443 ssl http2;
    server_name mercure.example.com;

    ssl_certificate     /etc/letsencrypt/live/mercure.example.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/mercure.example.com/privkey.pem;

    location /.well-known/mercure {
        proxy_set_header Connection '';
        proxy_http_version 1.1;
        chunked_transfer_encoding off;
        proxy_buffering off;
        proxy_cache off;
        proxy_pass http://127.0.0.1:3000;
        proxy_read_timeout 24h;
        proxy_send_timeout 24h;
    }
}
```

> ℹ️ `chunked_transfer_encoding off` et `proxy_buffering off` sont essentiels pour SSE : sans eux, Nginx peut mettre le flux en tampon et le livrer par lots.

### Scaling Multi-Nœuds

```nginx
upstream mercure_hubs {
    ip_hash;
    server 10.0.1.10:3000;
    server 10.0.1.11:3000;
    server 10.0.1.12:3000;
}
```

> 💡 Un seul nœud Mercure suffit à la plupart des applications. Surveillez l'usage CPU/mémoire sous trafic réel avant de passer à l'échelle horizontalement.

---

## Prochaines Étapes

Maintenant que vous maîtrisez le broadcasting :

1. **[Task Scheduling](tasks.md)** → Tâches planifiées
2. **[Événements](events.md)** → Approfondir le système d'événements
3. **[Messenger](messenger.md)** → Traitement asynchrone via files d'attente

---

**Broadcasting compris !** 🚀 → [Task Scheduling](tasks.md)