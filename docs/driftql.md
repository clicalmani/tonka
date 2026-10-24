# DriftQL

- [Introduction](#introduction)
- [Installation](#installation)
- [Configuration](#configuration)
  - [Génération automatique](#generation-automatique)
  - [Configuration frontend](#configuration-frontend)
  - [Configuration backend](#configuration-backend)
- [DriftQL React](#driftql-react)
  - [Initialisation du provider](#initialisation-du-provider)
  - [Définition d'un modèle](#definition-d-39-un-modele)
  - [Hooks](#hooks)
  - [Opérations CRUD](#operations-crud)
- [QueryBuilder](#querybuilder)
  - [Création d'une requête](#creation-d-39-une-requete)
  - [Conditions WHERE](#conditions-where)
  - [Tri et groupement](#tri-et-groupement)
  - [Limite et pagination](#limite-et-pagination)
  - [Jointures](#jointures)
- [Sécurité](#securite)
  - [Liste blanche](#liste-blanche)
  - [Politiques d'accès](#politiques-d-39-acces)
  - [Colonnes exclues](#colonnes-exclues)
  - [Validation](#validation)
- [Exemple complet](#exemple-complet)
- [Prochaines étapes](#prochaines-etapes)

---

## Introduction

**DriftQL** est une couche d'accès aux données qui permet d'interroger la base de données directement depuis le frontend, sans exposer de points d'entrée REST dédiés. Il allie la flexibilité d'un langage de requête déclaratif, à la manière de GraphQL, aux garanties de sécurité d'Elegant ORM : chaque requête est contrôlée côté serveur avant d'atteindre la base de données.

**Fonctionnalités :**

- ✅ Requêtes sécurisées émises depuis le frontend
- ✅ Construction de requêtes fluide via le `QueryBuilder`
- ✅ Modèles TypeScript typés
- ✅ Politiques d'autorisation par modèle et par rôle
- ✅ Exclusion configurable des colonnes sensibles
- ✅ Validation automatique des requêtes
- ✅ Intégration native avec React

---

## Installation

### Backend

```bash
composer require tonka/driftql
```

### Frontend

```bash
npm install driftql-react
```

---

## Configuration

### Génération automatique

Tonka fournit une commande qui génère les fichiers de configuration frontend et backend :

```bash
php tonka driftql:config
```

Cette commande crée, ou remplace s'ils existent déjà, les fichiers suivants :

1. **`drift.config.js`** : configuration frontend ;
2. **`config/driftql.php`** : configuration backend.

Une fois générés, ces fichiers peuvent être inspectés et adaptés à vos besoins.

### Configuration frontend

Le fichier `drift.config.js` généré se présente comme suit :

```javascript
// drift.config.js
export default {
  // URL de base de la passerelle backend DriftQL
  baseURL: '/api/bridge',

  // Délai d'expiration des requêtes, en millisecondes
  timeout: 5000,

  // Stratégie de mise en cache (RequestCache)
  cache: 'default',

  // Clé publique permettant d'authentifier les requêtes auprès du backend
  bridge_public_key: import.meta.env.VITE_DRIFTQL_BRIDGE_KEY || ''
};
```

Reportez la clé publique générée dans votre fichier `.env` :

```env
VITE_DRIFTQL_BRIDGE_KEY='<VotreClePubliqueDriftQL>'
```

Initialisez ensuite le client dans votre point d'entrée (par exemple `main.tsx` ou `App.tsx`) :

```javascript
import { Client } from 'driftql-react';
import config from './drift.config';

Client.init(config);
```

### Configuration backend

Le fichier `config/driftql.php` centralise la sécurité : limites de requêtes, liste blanche des modèles, politiques d'accès et colonnes exclues.

```php
// config/driftql.php
return [
    'enabled' => true,

    // Clé publique du bridge
    'bridge_public_key' => env('DRIFTQL_BRIDGE_KEY', ''),

    // Limites de requêtes
    'limits' => [
        'default_limit' => 100,
        'max_limit' => 500,
    ],

    // Liste blanche des modèles accessibles depuis le frontend
    'whitelist' => [
        'allowed_models' => [
            'User' => 'App\Models\User',
            'Post' => 'App\Models\Post',
            'Comment' => 'App\Models\Comment',
        ]
    ],

    // Politiques d'accès, par modèle et par rôle
    'policies' => [
        'App\Models\User' => [
            // Les administrateurs accèdent à tout (aucun filtre)
            'admin' => null,

            'editor' => [
                'column' => 'id',
                'operator' => '=',
                'value' => 'current_user_id'
            ]
        ]
    ],

    // Paramètres de sécurité
    'security' => [
        'strict_column_check' => true,
    ]
];
```

Reportez de la même manière la clé publique générée dans votre fichier `.env` côté backend :

```env
DRIFTQL_BRIDGE_KEY="<VotreClePubliqueDriftQL>"
```

---

## DriftQL React

`driftql-react` fait le pont entre votre application Tonka et React. Il expose vos modèles Elegant ORM au frontend à travers une API déclarative, typée et sécurisée.

### Initialisation du provider

```tsx
// App.tsx
import { ElegantProvider } from 'driftql-react';

function App() {
  return (
    <ElegantProvider config={{
      timeout: 10000,
      cache: true
    }}>
      <YourApp />
    </ElegantProvider>
  );
}
```

Une fois le provider en place, les hooks `useElegant` et `useQueryBuilder` sont disponibles dans n'importe quel composant de l'arborescence.

### Définition d'un modèle

La commande `driftql:model` génère le modèle TypeScript correspondant à un modèle backend :

```bash
php tonka driftql:model User
```

Elle crée le fichier `User.ts` dans le répertoire `resources/js/database` :

```typescript
import { Elegant } from 'driftql-react';

export class User extends Elegant {
  static resourceName = 'User';

  declare id: number;

  // Attributs du modèle User
  public name?: string;
  public email?: string;
  public role?: 'admin' | 'user' | 'moderator';
  public status?: 'active' | 'inactive' | 'pending';
  public created_at?: string;
  public updated_at?: string;

  // Méthodes personnalisées
  get isAdmin(): boolean {
    return this.role === 'admin';
  }

  get isActive(): boolean {
    return this.status === 'active';
  }

  get displayName(): string {
    return `${this.name} (${this.email})`;
  }
}
```

### Hooks

#### `useElegant`

Le hook `useElegant` donne accès aux opérations de base d'un modèle (`all`, `find`, `delete`, etc.) :

```tsx
// components/UserList.tsx
import { useState, useEffect } from 'react';
import { useElegant } from 'driftql-react';

interface User {
  id: number;
  name: string;
  email: string;
}

function UserList() {
  const { all, find, delete: deleteUser } = useElegant<User>('User');
  const [users, setUsers] = useState<User[]>([]);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    setLoading(true);
    all()
      .then(setUsers)
      .finally(() => setLoading(false));
  }, []);

  const handleDelete = async (id: number) => {
    await deleteUser(id);
    setUsers(users.filter(u => u.id !== id));
  };

  if (loading) return <div>Loading...</div>;

  return (
    <ul>
      {users.map(user => (
        <li key={user.id}>
          {user.name} - {user.email}
          <button onClick={() => handleDelete(user.id)}>Delete</button>
        </li>
      ))}
    </ul>
  );
}
```

Ce composant affiche la liste des utilisateurs et permet d'en supprimer, sans qu'aucune API REST dédiée n'ait à être développée.

#### `useQueryBuilder`

Le hook `useQueryBuilder` fournit un `QueryBuilder` lié à un modèle, pour les requêtes plus élaborées :

```tsx
// shared/UserSearch.tsx
import { useState } from 'react';
import { useQueryBuilder } from 'driftql-react';

function UserSearch() {
  const { getBuilder } = useQueryBuilder<User>('User');
  const [results, setResults] = useState<User[]>([]);

  const search = async (term: string) => {
    const builder = getBuilder();
    const data = await builder
      .where({ column: 'name', operator: 'LIKE', value: `%${term}%` })
      .where({ column: 'status', operator: '=', value: 'active' })
      .orderBy('name', 'ASC')
      .get();
    setResults(data);
  };

  return (
    <div>
      <input onChange={(e) => search(e.target.value)} placeholder="Search..." />
      <ul>
        {results.map(user => (
          <li key={user.id}>{user.name}</li>
        ))}
      </ul>
    </div>
  );
}
```

Cet exemple implémente une recherche dynamique : à chaque modification du champ de saisie, le terme est transmis au backend, qui renvoie les utilisateurs actifs dont le nom correspond.

### Opérations CRUD

#### Create

Insère un nouvel enregistrement en base de données :

```ts
import { User } from '@/database/User';

const newUser = await User.store({
  name: 'John Doe',
  email: 'john@example.com',
  role: 'user',
  status: 'active'
});

newUser.name // John Doe
newUser.email // john@example.com
```

#### Read

Récupère un ou plusieurs enregistrements :

```ts
import { User } from '@/database/User';

const user = await User.find(123);
const allUsers = await User.all();
const activeUsers = await User
  .where({ column: 'status', operator: '=', value: 'active' })
  .get();
```

Les colonnes déclarées comme [exclues](#colonnes-exclues) ne sont jamais retournées.

#### Update

Met à jour un enregistrement existant :

```ts
import { User } from '@/database/User';

const updated = await User.update(123, {
  name: 'Jane Doe',
  role: 'admin'
});
```

#### Delete

Supprime un enregistrement :

```ts
import { User } from '@/database/User';

await User.delete(123);
```

#### Save

Persiste une instance construite dynamiquement :

```ts
const user = new User();
user.name = 'John';
user.email = 'john@example.com';
await user.save();
```

---

## QueryBuilder

Le `QueryBuilder` construit des requêtes de lecture par chaînage de méthodes. Les colonnes y sont désignées par leur nom. Chaque colonne est validée côté serveur : elle doit exister dans l'entité du modèle et ne pas figurer parmi les [colonnes exclues](#colonnes-exclues), faute de quoi la requête est rejetée (voir [Validation des colonnes](#2-validation-des-colonnes)).

### Création d'une requête

```typescript
import { QueryBuilder } from 'driftql-react';

// Requête de base
const users = await new QueryBuilder('User')
    .get();
```

### Conditions WHERE

```typescript
// Condition simple
const users = await new QueryBuilder('User')
    .where({ column: 'status', operator: '=', value: 'active' })
    .get();

// Conditions multiples (AND)
const users = await new QueryBuilder('User')
    .where({ column: 'status', operator: '=', value: 'active' })
    .where({ column: 'role', operator: '=', value: 'admin' })
    .get();

// Condition OR
const users = await new QueryBuilder('User')
    .where({ column: 'role', operator: '=', value: 'admin' })
    .orWhere({ column: 'role', operator: '=', value: 'moderator' })
    .get();

// WHERE IN
const users = await new QueryBuilder('User')
    .whereIn('role', ['admin', 'moderator'])
    .get();
```

### Tri et groupement

```typescript
// Tri
const users = await new QueryBuilder('User')
    .orderBy('created_at', 'DESC')
    .orderBy('name', 'ASC')
    .get();

// Groupement
const users = await new QueryBuilder('User')
    .groupBy('role')
    .get();
```

### Limite et pagination

```typescript
// Limite
const users = await new QueryBuilder('User')
    .limit(0, 10)  // Décalage de 0, 10 résultats
    .get();

// Top (10 premiers résultats)
const users = await new QueryBuilder('User')
    .top(10)
    .get();

// Pagination
const page = 1;
const perPage = 15;
const users = await new QueryBuilder('User')
    .paginate(page, perPage);
```

### Jointures

Dans une jointure, chaque colonne est référencée sous la forme `Ressource.attribut`, où `Ressource` est la clé déclarée dans la [liste blanche](#liste-blanche).

```typescript
// INNER JOIN
const posts = await new QueryBuilder('Post')
    .join({
        resource: 'User',
        type: 'inner',
        fkey: 'Post.user_id',
        okey: 'User.id'
    })
    .where({ column: 'User.role', operator: '=', value: 'admin' })
    .get();

// LEFT JOIN
const posts = await new QueryBuilder('Post')
    .join({
        resource: 'User',
        type: 'left',
        fkey: 'Post.user_id',
        okey: 'User.id'
    })
    .get();

// Jointures multiples
const posts = await new QueryBuilder('Post')
    .join({
        resource: 'User',
        type: 'inner',
        fkey: 'Post.user_id',
        okey: 'User.id'
    })
    .join({
        resource: 'Category',
        type: 'inner',
        fkey: 'Post.category_id',
        okey: 'Category.id'
    })
    .where({ column: 'Category.slug', operator: '=', value: 'tech' })
    .get();
```

---

## Sécurité

DriftQL applique quatre niveaux de contrôle côté serveur :

1. la **liste blanche** des modèles exposés ;
2. les **politiques d'accès** par rôle ;
3. l'**exclusion des colonnes sensibles** ;
4. la **validation** de chaque requête.

Ces contrôles sont complémentaires : aucun ne se substitue aux autres.

### Liste blanche

Avant chaque requête, Tonka vérifie que le modèle ciblé figure dans la liste blanche. Aucune opération ne peut être menée sur un modèle non déclaré. Toute ressource utilisée côté frontend, y compris dans une jointure (comme `Category` dans les exemples du `QueryBuilder`), doit donc y être déclarée.

```php
// config/driftql.php
'whitelist' => [
    'allowed_models' => [
        'User' => 'App\Models\User',
        'Post' => 'App\Models\Post',
        'Category' => 'App\Models\Category',
        'Comment' => 'App\Models\Comment',
    ]
];
```

La liste est indexée par clé : la résolution se fait par accès direct, sans parcourir l'ensemble des entrées. Cette clé sert également d'identifiant de la ressource côté frontend. Dans l'exemple ci-dessus, la clé `User` désigne le modèle `App\Models\User` :

```typescript
// database/User.ts
import { Elegant } from 'driftql-react';

export interface UserData {
  id: number;
  name: string;
  email: string;
  role: 'admin' | 'user' | 'moderator';
  status: 'active' | 'inactive' | 'pending';
  created_at: string;
  updated_at: string;
}

export class User extends Elegant implements UserData {
  static resourceName = 'User'; // Clé de la ressource dans la liste blanche

  declare id: number;
}
```

### Politiques d'accès

Chaque modèle exposé doit définir sa politique d'accès. Les clés `store`, `update` et `destroy` désignent les `FormRequest` appliqués lors des opérations CRUD. Toute autre clé est interprétée comme un **rôle** : sa valeur est comparée à l'attribut `role` du modèle utilisateur déclaré dans la propriété `$userModel` de `AuthServiceProvider` :

```php
namespace App\Providers;

use Clicalmani\Core\Auth\Authenticate;

class AuthServiceProvider extends Authenticate
{
    protected string $userModel = \App\Models\User::class;
}
```

```php
// config/driftql.php
'policies' => [
    'App\Models\User' => [
        // Les administrateurs accèdent à tout (aucun filtre)
        'admin' => null,

        // Les éditeurs ne peuvent pas consulter les administrateurs
        'editor' => [
            'column' => 'role',
            'operator' => '!=',
            'value' => 'admin',
            // Note : 'value' accepte aussi 'current_user_id' pour une valeur dynamique
        ],

        // Les utilisateurs standards n'accèdent qu'à leurs propres données
        'user' => [
            'column' => 'id',
            'operator' => '=',
            'value' => 'current_user_id',
        ],

        /**
         * FormRequests
         */
        'store' => \App\Http\Requests\StoreUserRequest::class,
        'update' => \App\Http\Requests\UpdateUserRequest::class,
        'destroy' => \App\Http\Requests\DestroyUserRequest::class,
    ]
];
```

### Colonnes exclues

Certaines colonnes ne doivent jamais être accessibles depuis le frontend : mots de passe, jetons d'authentification, secrets d'authentification à deux facteurs, etc. Déclarez-les au niveau de la propriété `hidden` du modèle.

```php
// app/Models/User.php

protected array $hidden = ['password', 'remember_token']
```

Les colonnes déclarées sous la clé `*` s'appliquent à l'ensemble des modèles de la liste blanche. Celles déclarées sous une classe de modèle s'ajoutent aux précédentes pour ce modèle uniquement.

Une colonne exclue est **totalement inaccessible** dans les requêtes de lecture :

| Contexte | Comportement |
|----------|--------------|
| Résultats | La colonne n'est jamais retournée au frontend. |
| `where`, `orWhere`, `whereIn`, `having` | La requête est rejetée. |
| `orderBy`, `groupBy` | La requête est rejetée. |
| Jointures (`fkey`, `okey`) | La requête est rejetée. |

Le rejet d'une colonne dans une condition ou un tri est volontaire : sans lui, un attaquant pourrait déduire la valeur d'une colonne non retournée par essais successifs, par exemple en testant `LIKE 'a%'`, puis `LIKE 'ab%'`, et ainsi de suite.

Une requête qui référence une colonne exclue lève une `DriftQLException`, au même titre qu'une colonne inexistante dans l'entité.

> **Bonne pratique :** déclarez en priorité sous la clé `*` les colonnes sensibles communes à l'ensemble de vos modèles (`password`, `remember_token`, etc.). Passez en revue la liste à chaque ajout d'une colonne sensible dans une entité.

### Validation

#### Opérations CRUD

Pour les opérations d'écriture, Tonka s'appuie sur trois méthodes de `FormRequest` :

##### 1. La méthode `authorize()`

`authorize()` détermine si l'utilisateur courant est autorisé à effectuer l'opération. Elle est évaluée avant tout traitement de la requête :

```php
namespace App\Http\Requests;

use Clicalmani\Core\Http\Request;

class StoreUserRequest extends Request
{
    public function authorize(): bool
    {
        return $this->user()->can('create');
    }
}
```

##### 2. La méthode `prepareForValidation()`

`prepareForValidation()` permet de normaliser ou d'enrichir les données avant leur validation :

```php
public function prepareForValidation(): void
{
    // Renommage d'un attribut
    $this->user_name = $this->name;

    // Affectation d'un attribut
    $this->company_id = $this->user()->company_id;
    $this->created_at = now();
}
```

##### 3. La méthode `signatures()`

`signatures()` déclare les règles de validation de chaque attribut :

```php
public function signatures(): array
{
    return [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
        'password' => 'required|password|min:8'
    ];
}
```

#### Lecture

Pour les opérations de lecture, la validation s'effectue en trois étapes.

##### 1. La politique d'accès

Tonka évalue d'abord la politique du modèle. Lorsqu'elle est définie sous forme de tableau, les clés `column`, `operator` et `value` sont lues pour restreindre davantage le jeu de résultats.

Pour la politique suivante :

```php
// config/driftql.php
'policies' => [
    'App\Models\User' => [
        'editor' => [
            'column' => 'role',
            'operator' => '!=',
            'value' => 'admin'
        ],
    ]
];

// Tonka ajoute implicitement la condition suivante à la requête
User::where('role != ?', ['admin']);
```

Lorsqu'une fonction de rappel (*callable*) est fournie à la place d'un tableau, elle reçoit la requête et ses paramètres liés, et doit retourner le couple modifié :

```php
// config/driftql.php
'policies' => [
    // Closure
    'App\Models\User' => function(string $query, array $bindings): array {
        // Logique personnalisée
        return [$query, $bindings];
    }
];
```

##### 2. Validation des colonnes

Chaque colonne référencée dans la requête (conditions, tri, groupement, jointures) est validée par rapport à l'entité du modèle. Cette vérification s'effectue sans aucune requête vers la base de données.

Une colonne est acceptée si, et seulement si :

1. elle correspond à un attribut de l'entité du modèle ;
2. elle ne figure pas dans les [colonnes exclues](#colonnes-exclues).

Dans le cas contraire, une exception `DriftQLException` est levée.

Exemple, avec l'entité suivante :

```php
// database/entities
class UserEntity extends Entity
{
    #[Property(length: 10, unsigned: true, autoIncrement: true), PrimaryKey]
    public Integer $id;

    #[Property(length: 255)]
    public VarChar $name;

    #[Property(values: ['active', 'on-leave', 'resigned'])]
    public Enum $status;

    #[Property(length: 255)]
    public VarChar $password;
}
```

```tsx
import { User } from '@/database/User';

// ✅ Colonne existante et non exclue
await User.where({ column: 'status', operator: '=', value: 'active' }).get();

// ❌ Colonne inexistante dans UserEntity : DriftQLException
await User.where({ column: 'unknown', operator: '=', value: 'x' }).get();

// ❌ Colonne exclue ('*' => ['password', ...]) : DriftQLException
await User.where({ column: 'password', operator: 'LIKE', value: 'a%' }).get();
```

Les valeurs, quant à elles, ne sont jamais concaténées dans la requête SQL : elles sont systématiquement transmises sous forme de paramètres liés.

##### 3. Les jointures

Exemple de jointure :

```ts
import { User } from '@/database/User';

const users = await User.where<User>({ column: 'status', operator: '=', value: 'active' })
                .join({ resource: 'Post', fkey: 'Post.user_id', okey: 'User.id' })
                .get();
```

Une jointure fait l'objet de deux validations :

1. **Validation des ressources :** `Post` et `User` sont utilisés comme clés de la liste blanche pour retrouver les modèles `App\Models\Post` et `App\Models\User`.
2. **Validation des clés :** les clés doivent respecter le format `Ressource.attribut`. Si l'entité de la ressource ne contient pas l'attribut indiqué, ou si cet attribut est exclu, une `DriftQLException` est levée.

> **Remarque :** DriftQL ne permet pas encore de sélectionner des champs spécifiques lors des requêtes de lecture. Une solution respectant l'exigence de non-exposition des données est à l'étude.

---

## Exemple complet

### Backend

```php
// config/driftql.php
return [
    'enabled' => true,
    'bridge_public_key' => 'your_public_key',
    'whitelist' => [
        'allowed_models' => [
            'User' => 'App\Models\User',
            'Post' => 'App\Models\Post',
            'Comment' => 'App\Models\Comment',
        ]
    ],
    'security' => [
        'strict_column_check' => true,
    ]
];
```

### Frontend

```typescript
// models/Post.ts
import { useState, useEffect } from 'react';
import { Elegant } from 'driftql-react';

export class Post extends Elegant {
    static resourceName = 'Post';

    id!: number;
    title!: string;
    content!: string;
    user_id!: number;
    published!: boolean;
}

// Composant
export function PostList({ userId }: { userId: number }) {
    const [posts, setPosts] = useState([]);

    useEffect(() => {
        Post
            .join({
                resource: 'User',
                type: 'inner',
                fkey: 'Post.user_id',
                okey: 'User.id'
            })
            .where({ column: 'User.id', operator: '=', value: userId })
            .where({ column: 'published', operator: '=', value: 1 })
            .orderBy('created_at', 'DESC')
            .get()
            .then(setPosts);
    }, [userId]);

    return (
        <div>
            {posts.map(post => (
                <div key={post.id}>
                    <h3>{post.title}</h3>
                    <p>{post.content}</p>
                </div>
            ))}
        </div>
    );
}
```

---

## Prochaines étapes

Vous disposez désormais des bases nécessaires pour exploiter DriftQL. Pour poursuivre :

1. **[Vite](vite.md)** : outil de build ;
2. **[Broadcaster Client](broadcaster-client.md)** : événements en temps réel.

---

**Vous maîtrisez maintenant DriftQL !** 🚀 → [Vite](vite.md)