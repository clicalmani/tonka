# Elegant ORM

- [Elegant ORM](#elegant-orm)
- [Introduction](#introduction)
- [Définition d'un Modèle](#definition-d-39-un-modele)
  - [Création d'un Modèle](#creation-d-39-un-modele)
  - [Propriétés](#proprietes)
  - [Attributs Personnalisés](#attributs-personnalises)
- [Requêtes](#requetes)
  - [Récupération de Données](#recuperation-de-donnees)
  - [Filtrage](#filtrage)
  - [Pagination](#pagination)
  - [Agrégations](#agregations)
- [Relations](#relations)
  - [BelongsTo (Appartient à)](#belongsto-appartient-a-)
  - [HasMany (A plusieurs)](#hasmany-a-plusieurs-)
  - [HasOne (A un)](#hasone-a-un-)
  - [BelongsToMany (Appartient à plusieurs)](#belongstomany-appartient-a-plusieurs-)
  - [MorphOne (A un polymorphique)](#morphone-a-un-polymorphique-)
  - [MorphMany (A plusieurs polymorphiques)](#morphmany-a-plusieurs-polymorphiques-)
  - [MorphTo (Appartient à polymorphique)](#morphto-appartient-a-polymorphique-)
  - [MorphToMany (Appartient à plusieurs polymorphiques)](#morphtomany-appartient-a-plusieurs-polymorphiques-)
  - [HasOneThrough](#hasonethrough)
  - [HasManyThrough](#hasmanythrough)
  - [Relations Polymorphiques](#relations-polymorphiques)
- [Eager Loading](#eager-loading)
  - [Chargement de Base](#chargement-de-base)
  - [Chargement Imbriqué](#chargement-imbrique)
  - [Chargement Conditionnel](#chargement-conditionnel)
  - [Lazy Eager Loading](#lazy-eager-loading)
  - [With Exists](#with-exists)
- [Événements](#evenements)
  - [Définition](#definition)
  - [Cycle de Vie](#cycle-de-vie)
  - [Observers](#observers)
- [Auto-casting](#auto-casting)
  - [Activation par Défaut](#activation-par-defaut)
  - [Désactivation](#desactivation)
  - [Formatter Personnalisé](#formatter-personnalise)
- [Soft Delete](#soft-delete)
  - [Activation](#activation)
  - [Utilisation](#utilisation)
- [Jointures](#jointures)
  - [Jointures Simples](#jointures-simples)
  - [Jointures avec Clés Personnalisées](#jointures-avec-cles-personnalisees)
  - [Jointures Avancées avec Conditions](#jointures-avancees-avec-conditions)
  - [Jointures Multiples](#jointures-multiples)
  - [Jointures avec Alias](#jointures-avec-alias)
  - [Sous-Requêtes dans les Jointures](#sous-requetes-dans-les-jointures)
  - [Jointures Polymorphiques](#jointures-polymorphiques)
  - [Méthodes de Jointure Disponibles](#methodes-de-jointure-disponibles)
- [Scopes](#scopes)
- [Mutations](#mutations)
- [Query Scopes Dynamiques](#query-scopes-dynamiques)
- [Chunking](#chunking)
- [Transactions](#transactions)
  - [Transaction Simple](#transaction-simple)
  - [Gestion des Deadlocks](#gestion-des-deadlocks)
  - [Isolation des Transactions](#isolation-des-transactions)
  - [Bonnes Pratiques](#bonnes-pratiques)
- [Raw Queries](#raw-queries)
- [Référence](#reference)
- [Prochaines Étapes](#prochaines-etapes)

---

## Introduction

**Elegant** est l'**ORM** (**O**bject-**R**elational **M**apping) de Tonka. Il fournit une interface expressive et puissante pour interagir avec votre base de données.

Elegant s'inspire d'Eloquent (Laravel) mais avec des fonctionnalités supplémentaires :
- ✅ **Auto-casting** : conversion automatique des types
- ✅ **Auto-formatting** : formatage automatique des valeurs
- ✅ **Entités** : définition séparée de la structure des tables
- ✅ **Relations avancées** : support complet des relations polymorphiques
- ✅ **Événements** : cycle de vie complet du modèle
- ✅ **Soft Delete** : suppression réversible intégrée
- ✅ **Scopes** : requêtes réutilisables

---

## Définition d'un Modèle

### Création d'un Modèle

```bash
php tonka make:model User users id
```

Vous pouvez aussi créer un modèle avec son [entité](entities.md) en même temps :

```bash
php tonka make:entity User users id
```

### Propriétés

```php
namespace App\Models;

use Clicalmani\Core\Acme\Model as Elegant;

class User extends Elegant
{
    // Table et alias
    protected string $table = "users u";

    // Entité associée
    protected string $entity = \Database\Entities\UserEntity::class;

    // Clé primaire (peut être un tableau pour une clé composite)
    protected string|array $primaryKey = "u.id";

    // Attributs assignables en masse
    protected $fillable = [
        'name', 'email', 'password', 'role', 'is_active'
    ];

    // Attributs protégés contre l'assignation en masse
    protected $guarded = ['id', 'created_at', 'updated_at'];

    // Attributs masqués lors de la sérialisation
    protected $hidden = [
        'password',
        'remember_token',
        'deleted_at'
    ];

    // Auto-Cast
    protected bool $autoCast = true;

    // Attributs à récupérer avec un préfixe
    protected $asFresh = [
        'birthdate'
    ];

    // Préfixe à utiliser
    protected string $asFreshPrefix = 'fresh_';

    // Relations à charger par défaut
    protected $with = ['profile'];

    // Observers
    protected $observers = [
        \App\Observers\UserObserver::class
    ];
}
```

### Attributs Personnalisés

```php
// database/entities/UserEntity.php
namespace Database\Entities;

use Clicalmani\Database\Factory\Entity;

class UserEntity extends Entity
{ 
    // Accesseur pour full_name
    public string $full_name {
        get => $this->model->first_name . ' ' . $this->model->last_name;
    }

    // Accesseur pour avatar_url
    public string $avatar_url
    {
        get {
            return $this->avatar 
                ? assets('storage/' . $this->avatar) 
                : assets('images/default-avatar.png');
        }
    }

    // Accesseur pour role_label
    public string $role_label {
        get {
            $roles = [
                'admin' => 'Administrateur',
                'user' => 'Utilisateur',
                'moderator' => 'Modérateur'
            ];
            return $roles[$this->role] ?? $this->role;
        }
    }
}
```

---

## Requêtes

### Récupération de Données

```php
// Tous les enregistrements
$users = User::all();

// Avec pagination
$users = User::paginate(1, 10);

// Récupération avec conditions
$users = User::where('active = ?', [1])->get();

// Premier enregistrement
$user = User::where('email = ?', ['john@example.com'])->first();

// Trouver par ID
$user = User::find(1);
$user = User::findOrFail(1);

// Trouver plusieurs IDs
$users = User::findMany([1, 2, 3]);

// Récupération de colonnes spécifiques
$users = User::select('id, name, email')->get();

// Pluck (une colonne)
$emails = User::pluck('email', 'id');

// Valeur unique
$name = User::where('id = ?', [1])->value('name');

// Chunking
User::chunk(100, function($users) {
    foreach ($users as $user) {
        // Traitement par lots
    }
});

// Chunking par ID
User::chunkById(100, function($users) {
    foreach ($users as $user) {
        // Traitement par lots
    }
});

// Streaming lazy
User::lazy()->each(function($user) {
    // Traitement en flux
});
```

### Filtrage

```php
// Conditions simples
$users = User::where('active = ?', [1])
    ->where('role = ?', ['admin'])
    ->get();

// Or Where
$users = User::where('role = ?', ['admin'])
    ->orWhere('role = ?', ['moderator'])
    ->get();

// Where In
$users = User::whereIn('role', ['admin', 'moderator'])->get();

// Where Not In
$users = User::whereNotIn('role', ['guest'])->get();

// Where Null
$users = User::whereNull('deleted_at')->get();

// Where Not Null
$users = User::whereNotNull('deleted_at')->get();

// Where Exists
$users = User::whereExists(function($query) {
    $query->from('posts')
          ->where('posts.user_id = users.id');
})->get();

// Where Has (relation)
$users = User::whereHas('posts', function($query) {
    $query->where('published', true);
})->get();

// Where Doesnt Have
$users = User::whereDoesntHave('posts')->get();

// Or Where Has
$users = User::orWhereHas('posts', function($query) {
    $query->where('published', true);
})->get();
```

### Pagination

```php
// Pagination simple
$users = User::paginate(1, 10);

// Pagination sans calcul du total (plus performante sur de grandes tables)
$users = User::simplePaginate(1, 10);

// Pagination avec conditions
$users = User::where('active = ?', [1])
    ->paginate(1, 10);
```

### Agrégations

```php
// Compter
$count = User::count();
$count = User::where('active = ?', [1])->count();

// Somme
$total = Order::sum('amount');

// Moyenne
$average = Order::avg('amount');

// Minimum
$min = Product::min('price');

// Maximum
$max = Product::max('price');
```

---

## Relations

Les tables d'une base de données sont rarement isolées : un article de blog possède plusieurs commentaires, une commande est liée à l'utilisateur qui l'a passée. **Elegant ORM** rend la gestion de ces relations simple et expressive, avec un support natif des cas les plus courants :

- **BelongsTo** : un modèle appartient à un autre (l'inverse d'une relation `HasOne`/`HasMany`).
- **HasOne** : un modèle est associé à exactement un autre modèle.
- **HasMany** : un modèle est associé à plusieurs autres modèles.
- **BelongsToMany** : plusieurs modèles sont associés à plusieurs autres modèles, via une table pivot.
- **HasOneThrough** : un modèle est lié à un autre via un modèle intermédiaire, dans une relation à un seul enregistrement.
- **HasManyThrough** : variante à plusieurs enregistrements de la relation précédente — utile lorsqu'une relation implique trois modèles (par exemple, un `Country` a plusieurs `Post` via ses `User`).
- **MorphOne / MorphMany / MorphTo** : relations polymorphiques, où un modèle peut appartenir à plusieurs types de modèles différents via une seule association (par exemple, une `Photo` peut appartenir à un `User` ou à un `Article`).
- **MorphToMany** : variante many-to-many des relations polymorphiques.

> **Pour les développeurs Laravel :** ces noms de méthodes (`belongsTo`, `hasMany`, `morphToMany`...) sont directement repris d'Eloquent — vos réflexes s'appliquent presque intégralement ici.

### BelongsTo (Appartient à)

`belongsTo` définit le côté « inverse » d'une relation `HasOne` ou `HasMany` : le modèle courant porte la clé étrangère et appartient à l'autre modèle.

```php
namespace App\Models;

use Clicalmani\Core\Acme\Model as Elegant;

class Profile extends Elegant
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

```php
namespace App\Models;

use Clicalmani\Core\Acme\Model as Elegant;

class Comment extends Elegant
{
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
```

### HasMany (A plusieurs)

```php
namespace App\Models;

use Clicalmani\Core\Acme\Model as Elegant;

class Post extends Elegant
{
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
```

#### Schéma de Base de Données

```php
namespace Database\Entities;

use Clicalmani\Database\DataTypes\Integer;
use Clicalmani\Database\DataTypes\VarChar;
use Clicalmani\Database\DataTypes\Text;
use Clicalmani\Database\Factory\Entity;
use Clicalmani\Database\Factory\PrimaryKey;
use Clicalmani\Database\Factory\Property;

class PostEntity extends Entity
{
    #[Property(
        length: 10,
        unsigned: true,
        autoIncrement: true
    ), PrimaryKey]
    public Integer $id;

    #[Property(
        length: 255
    )]
    public VarChar $title;

    #[Property]
    public ?Text $content;
}
```

```php
namespace Database\Entities;

use Clicalmani\Database\DataTypes\Integer;
use Clicalmani\Database\DataTypes\VarChar;
use Clicalmani\Database\DataTypes\Text;
use Clicalmani\Database\Factory\Entity;
use Clicalmani\Database\Factory\PrimaryKey;
use Clicalmani\Database\Factory\Property;
use Clicalmani\Database\Factory\Index;

#[Index(
    name: 'fk_comments_posts1_idx',
    key: 'post_id',
    constraint: 'fk_comments_posts1',
    references: \App\Models\Post::class,
    onDelete: Index::ON_DELETE_CASCADE,
    onUpdate: Index::ON_UPDATE_CASCADE
)]
class CommentEntity extends Entity
{
    #[Property(
        length: 10,
        unsigned: true,
        autoIncrement: true
    ), PrimaryKey]
    public Integer $id;

    #[Property]
    public ?Text $content;

    #[Property(
        length: 10,
        unsigned: true
    )]
    public Integer $post_id;
}
```

#### Accès à la Relation

```php
$post = Post::find(1);
$comments = $post->comments; // Collection de Comment
```

### HasOne (A un)

```php
namespace App\Models;

use Clicalmani\Core\Acme\Model as Elegant;

class User extends Elegant
{
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }
}
```

#### Schéma de Base de Données

```php
namespace Database\Entities;

use Clicalmani\Database\DataTypes\Integer;
use Clicalmani\Database\DataTypes\VarChar;
use Clicalmani\Database\Factory\Entity;
use Clicalmani\Database\Factory\PrimaryKey;
use Clicalmani\Database\Factory\Property;
use Clicalmani\Database\Factory\Index;

#[Index(
    name: 'emailUNIQUE',
    key: 'email',
    unique: true
)]
class UserEntity extends Entity
{
    #[Property(
        length: 10,
        unsigned: true,
        autoIncrement: true
    ), PrimaryKey]
    public Integer $id;

    #[Property(
        length: 255
    )]
    public VarChar $name;

    #[Property(
        length: 191
    )]
    public VarChar $email;
}
```

```php
namespace Database\Entities;

use Clicalmani\Database\DataTypes\Integer;
use Clicalmani\Database\DataTypes\Text;
use Clicalmani\Database\Factory\Entity;
use Clicalmani\Database\Factory\Index;
use Clicalmani\Database\Factory\PrimaryKey;
use Clicalmani\Database\Factory\Property;

#[Index(
    name: 'fk_profiles_users1_idx',
    key: 'user_id',
    constraint: 'fk_profiles_users1',
    references: \App\Models\User::class
)]
class ProfileEntity extends Entity
{
    #[Property(
        length: 10,
        unsigned: true,
        autoIncrement: true
    ), PrimaryKey]
    public Integer $id;

    #[Property]
    public ?Text $bio;

    #[Property(
        length: 10,
        unsigned: true
    )]
    public Integer $user_id;
}
```

#### Accès à la Relation

```php
$user = User::find(1);
$profile = $user->profile; // Instance unique de Profile
```

### BelongsToMany (Appartient à plusieurs)

```php
namespace App\Models;

use Clicalmani\Core\Acme\Model as Elegant;

class User extends Elegant
{
    public function roles()
    {
        return $this->belongsToMany(Role::class, UserRole::class);
    }
}
```

```php
namespace App\Models;

use Clicalmani\Core\Acme\Model as Elegant;

class Role extends Elegant
{
    public function users()
    {
        return $this->belongsToMany(User::class, UserRole::class);
    }
}
```

#### Schéma de Base de Données

```php
namespace Database\Entities;

use Clicalmani\Database\DataTypes\Integer;
use Clicalmani\Database\DataTypes\VarChar;
use Clicalmani\Database\Factory\Entity;
use Clicalmani\Database\Factory\PrimaryKey;
use Clicalmani\Database\Factory\Property;

class UserEntity extends Entity
{
    #[Property(
        length: 10,
        unsigned: true,
        autoIncrement: true
    ), PrimaryKey]
    public Integer $id;

    #[Property(
        length: 255
    )]
    public VarChar $name;
}
```

```php
namespace Database\Entities;

use Clicalmani\Database\DataTypes\Integer;
use Clicalmani\Database\DataTypes\VarChar;
use Clicalmani\Database\DataTypes\Boolean;
use Clicalmani\Database\Factory\Entity;
use Clicalmani\Database\Factory\PrimaryKey;
use Clicalmani\Database\Factory\Property;
use Clicalmani\Database\Factory\Index;

#[Index(
    name: 'nameUNIQUE',
    key: 'name',
    unique: true
)]
class RoleEntity extends Entity
{
    #[Property(
        length: 10,
        unsigned: true,
        autoIncrement: true
    ), PrimaryKey]
    public Integer $id;

    #[Property(
        length: 255
    )]
    public VarChar $name;

    #[Property]
    public Boolean|false $isCore = false;
}
```

```php
namespace Database\Entities;

use Clicalmani\Database\DataTypes\Integer;
use Clicalmani\Database\Factory\Entity;
use Clicalmani\Database\Factory\PrimaryKey;
use Clicalmani\Database\Factory\Property;
use Clicalmani\Database\Factory\Index;

#[PrimaryKey(['user_id', 'role_id']), Index(
    name: 'fk_user_role_users1_idx',
    key: 'user_id',
    constraint: 'fk_user_role_users1',
    references: \App\Models\User::class
), Index(
    name: 'fk_user_role_roles1_idx',
    key: 'role_id',
    constraint: 'fk_user_role_roles1',
    references: \App\Models\Role::class
)]
class UserRoleEntity extends Entity
{
    #[Property(
        length: 10,
        unsigned: true
    )]
    public Integer $user_id;

    #[Property(
        length: 10,
        unsigned: true
    )]
    public Integer $role_id;
}
```

#### Accès à la Relation

```php
$user = User::find(1);
$roles = $user->roles; // Collection de Role, via la table pivot user_role
```

### MorphOne (A un polymorphique)

```php
namespace App\Models;

use Clicalmani\Core\Acme\Model as Elegant;

class Post extends Elegant
{
    public function tag()
    {
        return $this->morphOne(Tag::class, 'taggable');
    }
}
```

```php
namespace App\Models;

use Clicalmani\Core\Acme\Model as Elegant;

class Video extends Elegant
{
    public function tag()
    {
        return $this->morphOne(Tag::class, 'taggable');
    }
}
```

#### Schéma de Base de Données

```php
namespace Database\Entities;

use Clicalmani\Database\DataTypes\Integer;
use Clicalmani\Database\DataTypes\VarChar;
use Clicalmani\Database\Factory\Entity;
use Clicalmani\Database\Factory\PrimaryKey;
use Clicalmani\Database\Factory\Property;

class PostEntity extends Entity
{
    #[Property(
        length: 10,
        unsigned: true,
        autoIncrement: true
    ), PrimaryKey]
    public Integer $id;

    #[Property(
        length: 255
    )]
    public VarChar $title;
}
```

```php
namespace Database\Entities;

use Clicalmani\Database\DataTypes\Integer;
use Clicalmani\Database\DataTypes\VarChar;
use Clicalmani\Database\Factory\Entity;
use Clicalmani\Database\Factory\PrimaryKey;
use Clicalmani\Database\Factory\Property;

class VideoEntity extends Entity
{
    #[Property(
        length: 10,
        unsigned: true,
        autoIncrement: true
    ), PrimaryKey]
    public Integer $id;

    #[Property(
        length: 255
    )]
    public VarChar $title;

    #[Property(
        length: 255
    )]
    public VarChar $url;
}
```

```php
namespace Database\Entities;

use Clicalmani\Database\DataTypes\Integer;
use Clicalmani\Database\DataTypes\VarChar;
use Clicalmani\Database\Factory\Entity;
use Clicalmani\Database\Factory\PrimaryKey;
use Clicalmani\Database\Factory\Property;

class TagEntity extends Entity
{
    #[Property(
        length: 10,
        unsigned: true,
        autoIncrement: true
    ), PrimaryKey]
    public Integer $id;

    #[Property(
        length: 255
    )]
    public VarChar $name;

    #[Property(
        length: 10,
        unsigned: true
    )]
    public Integer $taggable_id;

    #[Property(
        length: 255
    )]
    public VarChar $taggable_type;
}
```

#### Accès à la Relation

```php
$post = Post::find(1);
$tag = $post->tag; // Le Tag associé à ce post, s'il existe
```

### MorphMany (A plusieurs polymorphiques)

```php
namespace App\Models;

use Clicalmani\Core\Acme\Model as Elegant;

class Post extends Elegant
{
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
```

```php
namespace App\Models;

use Clicalmani\Core\Acme\Model as Elegant;

class Video extends Elegant
{
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
```

#### Schéma de Base de Données

```php
namespace Database\Entities;

use Clicalmani\Database\DataTypes\Integer;
use Clicalmani\Database\DataTypes\VarChar;
use Clicalmani\Database\DataTypes\Text;
use Clicalmani\Database\Factory\Entity;
use Clicalmani\Database\Factory\PrimaryKey;
use Clicalmani\Database\Factory\Property;

class PostEntity extends Entity
{
    #[Property(
        length: 10,
        unsigned: true,
        autoIncrement: true
    ), PrimaryKey]
    public Integer $id;

    #[Property(
        length: 255
    )]
    public VarChar $title;

    #[Property]
    public Text $content;
}
```

```php
namespace Database\Entities;

use Clicalmani\Database\DataTypes\Integer;
use Clicalmani\Database\DataTypes\Text;
use Clicalmani\Database\DataTypes\VarChar;
use Clicalmani\Database\Factory\Entity;
use Clicalmani\Database\Factory\PrimaryKey;
use Clicalmani\Database\Factory\Property;

class CommentEntity extends Entity
{
    #[Property(
        length: 10,
        unsigned: true,
        autoIncrement: true
    ), PrimaryKey]
    public Integer $id;

    #[Property]
    public Text $content;

    #[Property(
        length: 10,
        unsigned: true
    )]
    public Integer $commentable_id;

    #[Property(
        length: 255
    )]
    public VarChar $commentable_type;
}
```

#### Accès à la Relation

```php
$post = Post::find(1);
$comments = $post->comments; // Tous les commentaires de ce post, quel que soit leur type d'origine
```

### MorphTo (Appartient à polymorphique)

`morphTo` définit le côté inverse d'une relation `morphOne`/`morphMany` : le modèle porte les deux colonnes polymorphiques (`_id` et `_type`) et détermine dynamiquement à quel modèle parent il appartient.

```php
namespace App\Models;

use Clicalmani\Core\Acme\Model as Elegant;

class Tag extends Elegant
{
    public function taggable()
    {
        return $this->morphTo();
    }
}
```

```php
namespace App\Models;

use Clicalmani\Core\Acme\Model as Elegant;

class Comment extends Elegant
{
    public function commentable()
    {
        return $this->morphTo();
    }
}
```

#### Accès à la Relation

```php
$tag = Tag::find(1);
$owner = $tag->taggable; // Instance de Post ou de Video, selon taggable_type
```

### MorphToMany (Appartient à plusieurs polymorphiques)

```php
namespace App\Models;

use Clicalmani\Core\Acme\Model as Elegant;

class Tag extends Elegant
{
    public function posts()
    {
        return $this->morphToMany(Post::class, 'taggable');
    }

    public function videos()
    {
        return $this->morphToMany(Video::class, 'taggable');
    }
}
```

```php
namespace App\Models;

use Clicalmani\Core\Acme\Model as Elegant;

class Post extends Elegant
{
    public function tags()
    {
        return $this->morphByMany(Tag::class, 'taggable');
    }
}
```

```php
namespace App\Models;

use Clicalmani\Core\Acme\Model as Elegant;

class Video extends Elegant
{
    public function tags()
    {
        return $this->morphByMany(Tag::class, 'taggable');
    }
}
```

#### Schéma de Base de Données

```php
namespace Database\Entities;

use Clicalmani\Database\DataTypes\Integer;
use Clicalmani\Database\DataTypes\VarChar;
use Clicalmani\Database\DataTypes\Text;
use Clicalmani\Database\Factory\Entity;
use Clicalmani\Database\Factory\PrimaryKey;
use Clicalmani\Database\Factory\Property;

class PostEntity extends Entity
{
    #[Property(
        length: 10,
        unsigned: true,
        autoIncrement: true
    ), PrimaryKey]
    public Integer $id;

    #[Property(
        length: 255
    )]
    public VarChar $title;

    #[Property]
    public Text $content;
}
```

```php
namespace Database\Entities;

use Clicalmani\Database\DataTypes\Integer;
use Clicalmani\Database\DataTypes\VarChar;
use Clicalmani\Database\Factory\Entity;
use Clicalmani\Database\Factory\PrimaryKey;
use Clicalmani\Database\Factory\Property;

class VideoEntity extends Entity
{
    #[Property(
        length: 10,
        unsigned: true,
        autoIncrement: true
    ), PrimaryKey]
    public Integer $id;

    #[Property(
        length: 255
    )]
    public VarChar $title;

    #[Property]
    public VarChar $url;
}
```

```php
namespace Database\Entities;

use Clicalmani\Database\DataTypes\Integer;
use Clicalmani\Database\DataTypes\VarChar;
use Clicalmani\Database\Factory\Entity;
use Clicalmani\Database\Factory\PrimaryKey;
use Clicalmani\Database\Factory\Property;

class TagEntity extends Entity
{
    #[Property(
        length: 10,
        unsigned: true,
        autoIncrement: true
    ), PrimaryKey]
    public Integer $id;

    #[Property(
        length: 255
    )]
    public VarChar $name;
}
```

```php
namespace Database\Entities;

use Clicalmani\Database\DataTypes\Integer;
use Clicalmani\Database\DataTypes\VarChar;
use Clicalmani\Database\Factory\Entity;
use Clicalmani\Database\Factory\PrimaryKey;
use Clicalmani\Database\Factory\Property;
use Clicalmani\Database\Factory\Index;

#[PrimaryKey(['tag_id', 'taggable_id', 'taggable_type']), Index(
    name: 'fk_taggables_tags1_idx',
    key: 'tag_id',
    constraint: 'fk_taggables_tags1',
    references: \App\Models\Tag::class
)]
class TaggableEntity extends Entity
{
    #[Property(
        length: 10,
        unsigned: true
    )]
    public Integer $tag_id;

    #[Property(
        length: 10,
        unsigned: true
    )]
    public Integer $taggable_id;

    #[Property(
        length: 255
    )]
    public VarChar $taggable_type;
}
```

#### Accès à la Relation

```php
$post = Post::find(1);
$tags = $post->tags; // Collection de Tag, partagée avec d'autres types de modèles (Video, etc.)
```

### HasOneThrough

```php
namespace App\Models;

use Clicalmani\Core\Acme\Model as Elegant;

class Supplier extends Elegant
{
    public function profile()
    {
        return $this->hasOneThrough(Profile::class, User::class);
    }
}
```

Ici, `Supplier` accède au `Profile` d'un `User` sans relation directe entre `Supplier` et `Profile` — Elegant traverse `User` comme modèle intermédiaire.

#### Schéma de Base de Données

```php
namespace Database\Entities;

use Clicalmani\Database\DataTypes\Integer;
use Clicalmani\Database\DataTypes\VarChar;
use Clicalmani\Database\Factory\Entity;
use Clicalmani\Database\Factory\PrimaryKey;
use Clicalmani\Database\Factory\Property;

class SupplierEntity extends Entity
{
    #[Property(
        length: 10,
        unsigned: true,
        autoIncrement: true
    ), PrimaryKey]
    public Integer $id;

    #[Property(
        length: 255
    )]
    public VarChar $name;
}
```

```php
namespace Database\Entities;

use Clicalmani\Database\DataTypes\Integer;
use Clicalmani\Database\DataTypes\VarChar;
use Clicalmani\Database\Factory\Entity;
use Clicalmani\Database\Factory\PrimaryKey;
use Clicalmani\Database\Factory\Property;
use Clicalmani\Database\Factory\Index;

#[Index(
    name: 'fk_users_suppliers1_idx',
    key: 'supplier_id',
    constraint: 'fk_users_suppliers1',
    references: \App\Models\Supplier::class,
    onDelete: Index::ON_DELETE_SETNULL
)]
class UserEntity extends Entity
{
    #[Property(
        length: 10,
        unsigned: true,
        autoIncrement: true
    ), PrimaryKey]
    public Integer $id;

    #[Property(
        length: 255
    )]
    public VarChar $name;

    #[Property(
        length: 10,
        unsigned: true
    )]
    public ?Integer $supplier_id;
}
```

```php
namespace Database\Entities;

use Clicalmani\Database\DataTypes\Integer;
use Clicalmani\Database\DataTypes\Text;
use Clicalmani\Database\Factory\Entity;
use Clicalmani\Database\Factory\PrimaryKey;
use Clicalmani\Database\Factory\Property;
use Clicalmani\Database\Factory\Index;

#[Index(
    name: 'fk_profiles_users1_idx',
    key: 'user_id',
    constraint: 'fk_profiles_users1',
    references: \App\Models\User::class
)]
class ProfileEntity extends Entity
{
    #[Property(
        length: 10,
        unsigned: true,
        autoIncrement: true
    ), PrimaryKey]
    public Integer $id;

    #[Property]
    public ?Text $bio;

    #[Property(
        length: 10,
        unsigned: true
    )]
    public Integer $user_id;
}
```

#### Accès à la Relation

```php
$supplier = Supplier::find(1);
$profile = $supplier->profile; // Profile de l'utilisateur associé à ce fournisseur
```

### HasManyThrough

```php
namespace App\Models;

use Clicalmani\Core\Acme\Model as Elegant;

class Country extends Elegant
{
    public function posts()
    {
        return $this->hasManyThrough(Post::class, User::class);
    }
}
```

#### Schéma de Base de Données

```php
namespace Database\Entities;

use Clicalmani\Database\DataTypes\Integer;
use Clicalmani\Database\DataTypes\VarChar;
use Clicalmani\Database\Factory\Entity;
use Clicalmani\Database\Factory\PrimaryKey;
use Clicalmani\Database\Factory\Property;
use Clicalmani\Database\Factory\Index;

#[Index(
    name: 'fk_users_countries1_idx',
    key: 'country_id',
    constraint: 'fk_users_countries1',
    references: \App\Models\Country::class
)]
class UserEntity extends Entity
{
    #[Property(
        length: 10,
        unsigned: true,
        autoIncrement: true
    ), PrimaryKey]
    public Integer $id;

    #[Property(
        length: 255
    )]
    public VarChar $name;

    #[Property(
        length: 10,
        unsigned: true
    )]
    public Integer $country_id;
}
```

```php
namespace Database\Entities;

use Clicalmani\Database\DataTypes\Integer;
use Clicalmani\Database\DataTypes\VarChar;
use Clicalmani\Database\DataTypes\Text;
use Clicalmani\Database\Factory\Entity;
use Clicalmani\Database\Factory\PrimaryKey;
use Clicalmani\Database\Factory\Property;
use Clicalmani\Database\Factory\Index;

#[Index(
    name: 'fk_posts_users1_idx',
    key: 'user_id',
    constraint: 'fk_posts_users1',
    references: \App\Models\User::class
)]
class PostEntity extends Entity
{
    #[Property(
        length: 10,
        unsigned: true,
        autoIncrement: true
    ), PrimaryKey]
    public Integer $id;

    #[Property(
        length: 255
    )]
    public VarChar $title;

    #[Property(
        length: 255
    )]
    public ?Text $content;

    #[Property(
        length: 10,
        unsigned: true
    )]
    public Integer $user_id;
}
```

```php
namespace Database\Entities;

use Clicalmani\Database\DataTypes\Integer;
use Clicalmani\Database\DataTypes\VarChar;
use Clicalmani\Database\Factory\Entity;
use Clicalmani\Database\Factory\PrimaryKey;
use Clicalmani\Database\Factory\Property;

class CountryEntity extends Entity
{
    #[Property(
        length: 10,
        unsigned: true,
        autoIncrement: true
    ), PrimaryKey]
    public Integer $id;

    #[Property(
        length: 255
    )]
    public VarChar $name;
}
```

#### Accès à la Relation

```php
$country = Country::find(1);
$posts = $country->posts; // Tous les posts des utilisateurs de ce pays
```

### Relations Polymorphiques

Les quatre relations `morphOne`, `morphMany`, `morphTo` et `morphToMany` permettent à un même modèle (`Tag`, `Comment`...) d'être associé à **plusieurs types de modèles différents** via une seule et même association, plutôt que de dupliquer une relation par type de modèle cible. En pratique :

| Cardinalité côté « propriétaire » | Relation à utiliser sur le modèle cible | Relation inverse sur le modèle polymorphique |
|---|---|---|
| Un seul enregistrement associé (ex : un post a un seul tag) | `morphOne` | `morphTo` |
| Plusieurs enregistrements associés (ex : un post a plusieurs commentaires) | `morphMany` | `morphTo` |
| Association plusieurs-à-plusieurs (ex : un tag peut s'appliquer à plusieurs posts et vidéos) | `morphToMany` | `morphByMany` |

Le modèle polymorphique (`Tag`, `Comment`) porte toujours les deux colonnes `*_id` et `*_type` (`taggable_id`/`taggable_type`, `commentable_id`/`commentable_type`) qui l'identifient et le typent ; c'est cette paire de colonnes que `morphTo()` interroge pour résoudre dynamiquement le bon modèle parent à l'exécution.

---

## Eager Loading

### Chargement de Base

```php
// Chargement simple
$users = User::with(['posts', 'profile'])->get();

// Chargement avec filtre sur la relation
$users = User::with('posts', function($query) {
    $query->where('published = ?', [true]);
})->get();
```

### Chargement Imbriqué

```php
// Relations imbriquées
$users = User::with('posts.comments')->get();

// Niveau 3
$users = User::with('posts.comments.user')->get();
```

### Lazy Eager Loading

Contrairement à `with()`, qui précharge une relation *au moment de la requête initiale*, `load()` charge une relation *a posteriori*, sur une collection ou un modèle déjà récupéré :

```php
$users = User::all();

// Plus tard dans le code, une fois qu'on sait qu'on aura besoin de 'posts'
$users->load('posts');
```

Utile lorsque la nécessité de charger une relation ne se décide qu'après la récupération initiale des modèles — évitant de précharger systématiquement une relation qui ne sera pas toujours utilisée.

### With Exists

```php
// Ajoute une colonne indiquant si la relation existe
$users = User::withExists('posts', function($query) {
    $query->where('published = ?', [true]);
})->get();
```

---

## Événements

### Définition

```php
class User extends Elegant
{
    protected function booted(): void
    {
        $this->creating(function(self $model) {
            $model->created_at = now();
            $model->updated_at = now();
        });

        $this->created(function(self $model) {
            Log::info('User created: ' . $model->id);
            $this->sendWelcomeEmail($model);
        });

        $this->updating(function(self $model) {
            $model->updated_at = now();
        });

        $this->updated(function(self $model) {
            Log::info('User updated: ' . $model->id);
        });

        $this->deleting(function(self $model) {
            Log::warning('User being deleted: ' . $model->id);
        });

        $this->deleted(function(self $model) {
            Log::info('User deleted: ' . $model->id);
        });

        $this->saving(function(self $model) {
            // Avant toute sauvegarde
        });

        $this->saved(function(self $model) {
            // Après toute sauvegarde
        });

        $this->restoring(function(self $model) {
            Log::info('Restoring user: ' . $model->id);
        });

        $this->restored(function(self $model) {
            Log::info('User restored: ' . $model->id);
        });
    }
}
```

### Cycle de Vie

| Événement | Moment | Peut annuler l'opération |
|-----------|--------|--------|
| `creating` | Avant la création | Oui |
| `created` | Après la création | — |
| `updating` | Avant la mise à jour | Oui |
| `updated` | Après la mise à jour | — |
| `deleting` | Avant la suppression | Oui |
| `deleted` | Après la suppression | — |
| `restoring` | Avant la restauration | Oui |
| `restored` | Après la restauration | — |
| `saving` | Avant toute sauvegarde | Oui |
| `saved` | Après toute sauvegarde | — |

### Observers

```php
// App/Observers/UserObserver.php
class UserObserver extends EventObserver
{
    public function creating(User $user): void
    {
        $user->created_at = now();
    }

    public function created(User $user): void
    {
        Mail::to($user->email)->send(new WelcomeEmail($user));
    }

    public function updating(User $user): void
    {
        $user->updated_at = now();
    }

    public function deleted(User $user): void
    {
        Storage::delete('avatars/' . $user->avatar);
    }
}

// Dans le modèle
protected $observers = [
    UserObserver::class
];
```

Un Observer centralise, dans une classe dédiée, la logique que vous auriez sinon dû répartir dans plusieurs closures au sein de `booted()` — préférable dès que la logique d'un événement dépasse une ou deux lignes, ou doit être testée isolément.

---

## Auto-casting

### Activation par Défaut

```php
// database/entities/UserEntity.php
class UserEntity extends Entity
{
    #[Property]
    public DateTime $created_at; // Casté en objet date

    #[Property]
    public ?DateTime $deleted_at; // Casté en objet date, ou null

    #[Property(length: 1)]
    public TinyInt|int $is_active; // Casté en booléen (1 → true)

    #[Property(length: 10)]
    public Integer|int $views; // Casté en int
}

// Dans le modèle
$user = User::find(1);
$createdAt = $user->created_at; // Instance de DateTime
$isActive = $user->is_active; // bool
```

### Désactivation

```php
// Désactiver pour tout le modèle
class User extends Elegant
{
    protected $autoCast = false;
}

// Désactiver pour un attribut spécifique
#[Property(autoCast: false)]
public DateTime $created_at;
```

### Formatter Personnalisé

```php
namespace Database\Entities;

use Clicalmani\Database\DataTypes\Integer;
use Clicalmani\Database\Factory\Entity;
use Clicalmani\Database\Factory\PrimaryKey;
use Clicalmani\Database\Factory\Property;

class OrderEntity extends Entity
{
    #[Property(
        length: 10,
        unsigned: true,
        autoIncrement: true
    ), PrimaryKey]
    public Integer $id;

    #[Property(
        formatter: 'dateFormat'
    )]
    public Date $order_date;

    // ── Méthodes ─────────────────────────

    public function dateFormat(?string $date)
    {
        if (!$date) return null;
        return Carbon::parse($date)
            ->translatedFormat('d/m/Y'); // Format français
    }
}

$date = $order->order_date; // 10/08/2024
```

---

## Soft Delete

### Activation

```php
use Clicalmani\Database\Traits\SoftDelete;

class User extends Elegant
{
    use SoftDelete;
}
```

### Utilisation

```php
// Suppression (soft delete)
$user->delete();

// Restauration
$user->restore();

// Vérification
if ($user->trashed()) {
    // L'utilisateur est soft deleted
}

// Inclure les enregistrements soft deleted
$users = User::withTrashed()->get();

// Uniquement les enregistrements soft deleted
$users = User::onlyTrashed()->get();

// Suppression définitive
$user->forceDelete();
```

---

## Jointures

### Jointures Simples

```php
// INNER JOIN
$posts = Post::innerJoin(User::class)->get();

// LEFT JOIN
$posts = Post::leftJoin(User::class)->get();

// RIGHT JOIN
$posts = Post::rightJoin(User::class)->get();

// CROSS JOIN
$posts = Post::crossJoin(User::class)->get();
```

Ces méthodes de jointure au niveau du modèle sont l'équivalent, côté ORM, des méthodes `joinInner`/`joinLeft`/`joinRight`/`joinCross` déjà présentées sur la page [Query Builder](query-builder.md#jointures) — préférez la syntaxe ORM lorsque vous travaillez déjà avec des modèles Elegant, elle résout automatiquement le nom de table à partir de la classe passée en argument.

### Jointures avec Clés Personnalisées

```php
// Clés personnalisées
$posts = Post::innerJoin(
    User::class,           // Modèle à joindre
    'author_id',           // Clé étrangère (dans Post)
    'id'                   // Clé locale (dans User)
)->get();

// Avec alias explicites
$posts = Post::innerJoin(
    User::class,
    'posts.author_id',
    'users.id'
)->get();
```

### Jointures Avancées avec Conditions

```php
// Jointure avec conditions supplémentaires
$posts = Post::join(User::class, function($join) {
    $join->inner()->on('posts.user_id = users.id')
         ->where('users.active = ?', [1])
         ->where('posts.published = ?', [true]);
})->get();

// Condition OR dans la jointure
$posts = Post::join(User::class, function($join) {
    $join->inner()->on('posts.user_id = users.id')
         ->orWhere('posts.author_id = users.id');
})->get();
```

### Jointures Multiples

```php
// Deux jointures
$posts = Post::innerJoin(User::class)
    ->innerJoin(Comment::class)
    ->where('users.role = ?', ['admin'])
    ->get();

// Trois jointures
$orders = Order::innerJoin(User::class)
    ->innerJoin(Product::class)
    ->innerJoin(Category::class)
    ->where('users.active = ?', [1])
    ->where('orders.status = ?', ['completed'])
    ->get();
```

### Jointures avec Alias

```php
// Alias personnalisés
$posts = Post::innerJoin(User::class, 'author_id', 'id')
    ->innerJoin(Profile::class, 'user_id', 'id')
    ->select('posts.*, users.name as author_name, profiles.bio')
    ->get();
```

### Jointures Polymorphiques

```php
// MorphToMany avec jointure explicite
$post = Post::find(1);
$tags = $post->tags()
    ->innerJoin(Taggable::class, 'tags.id', 'taggable.tag_id')
    ->where('taggable.taggable_type = ?', [Post::class])
    ->get();
```

### Exemples Complets

```php
// 1. Récupérer tous les posts avec leurs auteurs actifs
$posts = Post::innerJoin(User::class)
    ->where('users.active = ?', [1])
    ->orderBy('posts.created_at DESC')
    ->get();

// 2. Récupérer les commandes avec utilisateurs et produits
$orders = Order::innerJoin(User::class, 'user_id', 'id')
    ->innerJoin(Product::class, 'product_id', 'id')
    ->where('orders.status = ?', ['completed'])
    ->where('users.role = ?', ['admin'])
    ->select('orders.*, users.name, products.title')
    ->get();

// 3. Récupérer les commentaires avec leurs auteurs
$comments = Comment::innerJoin(User::class, 'user_id', 'id')
    ->where('comments.approved = ?', [true])
    ->orderBy('comments.created_at DESC')
    ->limit(0, 10)
    ->get();

// 4. Récupérer les utilisateurs avec leur nombre de posts (LEFT JOIN)
$users = User::leftJoin(Post::class)
    ->select('users.*, COUNT(posts.id) as post_count')
    ->groupBy('users.id')
    ->get();

// 5. Récupérer les produits avec leurs catégories
$products = Product::innerJoin(Category::class, 'category_id', 'id')
    ->where('categories.active = ?', [1])
    ->where('products.stock > ?', [0])
    ->orderBy('products.price ASC')
    ->get();
```

### Méthodes de Jointure Disponibles

| Méthode | Description | Exemple |
|---------|-------------|---------|
| `innerJoin()` | INNER JOIN | `Post::innerJoin(User::class)` |
| `leftJoin()` | LEFT JOIN | `Post::leftJoin(User::class)` |
| `rightJoin()` | RIGHT JOIN | `Post::rightJoin(User::class)` |
| `crossJoin()` | CROSS JOIN | `Post::crossJoin(User::class)` |

---

## Query Scopes Dynamiques

```php
// Scopes dynamiques via closure
User::when($condition, function($query) use ($search) {
    $query->where('name LIKE ?', ["%$search%"]);
})->get();

// Unless
User::unless($condition, function($query) {
    $query->where('active = ?', [true]);
})->get();
```

À distinguer des [Scopes](#scopes) nommés et réutilisables évoqués ci-dessus : `when()`/`unless()` s'appliquent au coup par coup, directement dans le code appelant, sans nécessiter de méthode dédiée sur le modèle.

---

## Chunking

```php
// Traitement par lots de 100
User::chunk(100, function($users) {
    foreach ($users as $user) {
        // Traitement
        $user->update(['processed' => true]);
    }
});

// Chunking par ID (plus performant sur de grandes tables)
User::chunkById(100, function($users) {
    foreach ($users as $user) {
        // Traitement
        $user->update(['processed' => true]);
    }
});
```

`chunkById()` pagine sur la clé primaire plutôt que sur un `OFFSET` classique — une différence qui compte sur de grandes tables : un `OFFSET` élevé oblige le moteur à parcourir puis ignorer toutes les lignes précédentes à chaque lot, tandis qu'une pagination par clé reste performante quel que soit le nombre de lots déjà traités.

---

## Transactions

```php
use Clicalmani\Core\Support\Facades\DB;
```

### Transaction Simple

```php
// Transaction simple
DB::transaction(function() use ($user) {
    $user->save();
    $user->profile()->create($data);
});

// Transaction avec valeur de retour
$result = DB::transaction(function() use ($user) {
    $user->save();
    return $user->id;
});

// Transaction avec gestion manuelle
DB::beginTransaction();
try {
    $user->save();
    $user->profile()->create($data);
    DB::commit();
} catch (\Exception $e) {
    DB::rollback();
    throw $e;
}
```

### Gestion des Deadlocks

Un interblocage (*deadlock*) survient lorsque deux transactions attendent mutuellement des ressources verrouillées par l'autre.

```php
// Méthode deadlock() avec tentatives automatiques
DB::deadlock(function() use ($user, $data) {
    // Cette opération sera réessayée automatiquement en cas d'interblocage
    $user->update($data);
    $user->profile()->create($data['profile']);
}, 5, 100); // 5 tentatives, 100ms d'attente entre chaque
```

`DB::deadlock($callback, $tentatives, $delaiMs)` prend explicitement le nombre maximal de tentatives et le délai (en millisecondes) entre chacune — comme illustré ci-dessus (5 tentatives, 100ms d'intervalle).

```php
// Avec transaction imbriquée
DB::deadlock(function() {
    return DB::transaction(function() {
        $user = User::find(1);
        $user->update(['balance' => $user->balance - 100]);

        $recipient = User::find(2);
        $recipient->update(['balance' => $recipient->balance + 100]);

        return true;
    });
}, 3, 200); // 3 tentatives, 200ms d'attente
```

#### Exemple Complet : Transfert avec Gestion de Deadlock

```php
class PaymentController extends Controller
{
    public function transfer(Request $request)
    {
        $validated = $request->validate([
            'from_user_id' => 'required|exists:users,id',
            'to_user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.01'
        ]);

        try {
            $result = DB::deadlock(function() use ($validated) {
                return DB::transaction(function() use ($validated) {
                    // 1. Débiter l'expéditeur
                    $fromUser = User::find($validated['from_user_id']);

                    if ($fromUser->balance < $validated['amount']) {
                        throw new \Exception('Solde insuffisant');
                    }

                    $fromUser->decrement('balance', $validated['amount']);

                    // 2. Créditer le destinataire
                    $toUser = User::find($validated['to_user_id']);
                    $toUser->increment('balance', $validated['amount']);

                    // 3. Enregistrer la transaction
                    Transaction::create([
                        'from_user_id' => $validated['from_user_id'],
                        'to_user_id' => $validated['to_user_id'],
                        'amount' => $validated['amount'],
                        'status' => 'completed'
                    ]);

                    return [
                        'success' => true,
                        'message' => 'Transfert effectué avec succès'
                    ];
                });
            }, 5, 100); // 5 tentatives, 100ms d'attente

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->error($e->getMessage());
        }
    }
}
```

### Isolation des Transactions

```php
use Clicalmani\Database\DB;

// Définir le niveau d'isolation
DB::isolateTransaction(
    DB::TRANSACTION_SERIALIZABLE,
    'SESSION'
);

DB::transaction(function() {
    // Traitement exécuté avec l'isolation SERIALIZABLE
    $user = User::find(1);
    $user->update(['balance' => $user->balance - 100]);
});
```

Niveaux d'isolation disponibles :

| Constante | Niveau SQL standard |
|---|---|
| `DB::TRANSACTION_DIRTY_READS` | READ UNCOMMITTED |
| `DB::TRANSACTION_NON_REPEATABLE_READS` | READ COMMITTED |
| `DB::TRANSACTION_PHANTOM_READS` | REPEATABLE READ |
| `DB::TRANSACTION_SERIALIZABLE` | SERIALIZABLE |

> **Point de vocabulaire :** ces constantes sont nommées d'après le **phénomène qu'elles éliminent** plutôt que d'après le nom SQL standard du niveau — `TRANSACTION_DIRTY_READS` ne signifie pas « active les lectures sales », mais correspond au niveau `READ UNCOMMITTED`, le seul des quatre à ne prévenir *aucun* des trois phénomènes indésirables. Gardez le tableau ci-dessus à portée de main pour éviter toute confusion au moment de choisir la bonne constante.

### Bonnes Pratiques

```php
// 1. Toujours utiliser les transactions pour les opérations critiques
DB::transaction(function() {
    // Opérations atomiques
});

// 2. Utiliser deadlock() pour les opérations à risque d'interblocage
DB::deadlock(function() {
    // Opérations avec risque de deadlock
}, 5, 100);

// 3. Vérifier l'état de la transaction avant d'en démarrer une nouvelle
if (!DB::inTransaction()) {
    DB::beginTransaction();
    try {
        // Opérations
        DB::commit();
    } catch (\Exception $e) {
        DB::rollback();
        throw $e;
    }
}

// 4. Limiter la durée des transactions
DB::transaction(function() {
    // Opérations rapides uniquement
    // Ne pas inclure d'opérations longues (appel API, traitement de fichiers, etc.)
});

// 5. Les transactions imbriquées utilisent un savepoint automatique
DB::transaction(function() {
    // Niveau 1
    DB::transaction(function() {
        // Niveau 2 — savepoint automatique
    });
});
```

---

## Référence

Elegant ORM fournit une large gamme de méthodes pour interagir avec votre base de données. Voici un récapitulatif complet des méthodes disponibles.

### Méthodes de Récupération

| Méthode | Description | Exemple |
|---------|-------------|---------|
| `all()` | Récupère tous les enregistrements | `User::all()` |
| `find($id)` | Trouve par ID | `User::find(1)` |
| `findOrFail($id)` | Trouve ou lance une exception | `User::findOrFail(1)` |
| `findMany($ids)` | Trouve plusieurs IDs | `User::findMany([1, 2, 3])` |
| `first()` | Récupère le premier enregistrement | `User::first()` |
| `firstOrFail()` | Premier ou exception | `User::firstOrFail()` |
| `firstOr($callback)` | Premier ou callback | `User::firstOr(fn() => 'default')` |
| `get()` | Exécute la requête | `User::where('active = ?', [1])->get()` |
| `select($fields)` | Sélectionne des colonnes | `User::select('id, name')->get()` |
| `pluck($field, $key)` | Récupère une liste de valeurs | `User::pluck('name', 'id')` |
| `value($field)` | Récupère une valeur unique | `User::where('id = ?', [1])->value('name')` |
| `chunk($size, $callback)` | Traite par lots | `User::chunk(100, fn($users) => ...)` |
| `chunkById($size, $callback)` | Traite par lots (via ID) | `User::chunkById(100, fn($users) => ...)` |
| `lazy()` | Stream en flux | `User::lazy()->each(fn($user) => ...)` |
| `exists()` | Vérifie l'existence | `User::where('email = ?', [$email])->exists()` |
| `doesntExist()` | Vérifie la non-existence | `User::where('email = ?', [$email])->doesntExist()` |

### Méthodes de Filtrage

| Méthode | Description | Exemple |
|---------|-------------|---------|
| `where($criteria, $options)` | Condition WHERE | `User::where('active = ?', [1])` |
| `orWhere($criteria, $options)` | Condition OR | `User::orWhere('role = ?', ['admin'])` |
| `whereIn($key, $values)` | WHERE IN | `User::whereIn('role', ['admin', 'user'])` |
| `whereExists($callback)` | WHERE EXISTS | `User::whereExists(fn($q) => $q->from('posts'))` |
| `whereNotExists($callback)` | WHERE NOT EXISTS | `User::whereNotExists(fn($q) => ...)` |
| `whereHas($relation, $callback)` | WHERE HAS | `User::whereHas('posts', fn($q) => $q->where('published', true))` |
| `whereDoesntHave($relation, $callback)` | WHERE DOESNT HAVE | `User::whereDoesntHave('posts')` |
| `orWhereHas($relation, $callback)` | OR WHERE HAS | `User::orWhereHas('posts', fn($q) => ...)` |
| `orWhereDoesntHave($relation, $callback)` | OR WHERE DOESNT HAVE | `User::orWhereDoesntHave('posts')` |
| `whereRaw($condition)` | WHERE brut | `User::whereRaw('DATE(created_at) = ?', [date('Y-m-d')])` |
| `whereNull($column)` | WHERE NULL | `User::whereNull('deleted_at')` |
| `whereNotNull($column)` | WHERE NOT NULL | `User::whereNotNull('deleted_at')` |

### Méthodes de Tri et Groupement

| Méthode | Description | Exemple |
|---------|-------------|---------|
| `orderBy($order)` | Tri | `User::orderBy('created_at DESC')` |
| `orderByRaw($order)` | Tri en SQL brut | `User::orderByRaw('FIELD(role, "admin", "user")')` |
| `groupBy($group)` | Groupement | `User::groupBy('role')` |
| `having($criteria)` | HAVING | `User::having('total > ?', [5])` |

### Méthodes de Jointure

| Méthode | Description | Exemple |
|---------|-------------|---------|
| `join($model, $callback)` | Jointure personnalisée | `User::join(Post::class, fn($j) => $j->on(...))` |
| `innerJoin($model, $fk, $lk)` | INNER JOIN | `Post::innerJoin(User::class, 'user_id', 'id')` |
| `leftJoin($model, $fk, $lk)` | LEFT JOIN | `Post::leftJoin(User::class, 'user_id', 'id')` |
| `rightJoin($model, $fk, $lk)` | RIGHT JOIN | `Post::rightJoin(User::class, 'user_id', 'id')` |
| `crossJoin($model)` | CROSS JOIN | `Post::crossJoin(User::class)` |

### Méthodes d'Insertion

| Méthode | Description | Exemple |
|---------|-------------|---------|
| `create($attributes)` | Crée un enregistrement | `User::create(['name' => 'John'])` |
| `insert($attributes)` | Insertion en masse | `User::insert([...])` |
| `insertIgnore($attributes)` | Insertion IGNORE | `User::insertIgnore([...])` |
| `insertGetId($attributes)` | Insertion avec retour de l'ID | `User::insertGetId(['name' => 'John'])` |
| `insertOrFail($attributes)` | Insertion ou exception | `User::insertOrFail([...])` |
| `save()` | Sauvegarde le modèle | `$user->save()` |

### Méthodes de Mise à Jour

| Méthode | Description | Exemple |
|---------|-------------|---------|
| `update($attributes)` | Met à jour | `User::where('id = ?', [1])->update(['name' => 'Jane'])` |
| `increment($field, $value)` | Incrémente | `User::where('id = ?', [1])->increment('views')` |
| `decrement($field, $value)` | Décrémente | `User::where('id = ?', [1])->decrement('score', 5)` |
| `fill($attributes)` | Remplit le modèle | `$user->fill(['name' => 'John'])` |
| `refresh()` | Rafraîchit le modèle | `$user->refresh()` |

### Méthodes de Suppression

| Méthode | Description | Exemple |
|---------|-------------|---------|
| `delete()` | Supprime | `$user->delete()` |
| `forceDelete()` | Supprime définitivement | `$user->forceDelete()` |
| `destroy()` | Supprime tout | `User::destroy()` |
| `restore()` | Restaure (Soft Delete) | `$user->restore()` |
| `truncate()` | Vide la table | `User::truncate()` |

### Méthodes d'Agrégation

| Méthode | Description | Exemple |
|---------|-------------|---------|
| `count($field)` | Compte | `User::count()` |
| `sum($field)` | Somme | `Order::sum('amount')` |
| `avg($field)` | Moyenne | `Order::avg('amount')` |
| `min($field)` | Minimum | `Product::min('price')` |
| `max($field)` | Maximum | `Product::max('price')` |

### Méthodes de Pagination

| Méthode | Description | Exemple |
|---------|-------------|---------|
| `paginate($page, $size)` | Pagination | `User::paginate(1, 10)` |
| `simplePaginate($page, $size)` | Pagination simple | `User::simplePaginate(1, 10)` |

### Méthodes de Relation

| Méthode | Description | Exemple |
|---------|-------------|---------|
| `with($relations)` | Eager Loading | `User::with('posts', 'profile')` |
| `load($relations)` | Lazy Eager Loading | `$users->load('posts')` |
| `withCount($relations)` | Compte les relations | `User::withCount('posts')` |
| `withExists($relations)` | Existence des relations | `User::withExists('posts')` |
| `has($relation)` | Filtre par relation | `User::has('posts')` |

### Méthodes d'Événements

| Méthode | Description | Exemple |
|---------|-------------|---------|
| `registerEvent($event, $callback)` | Enregistre un événement | `$model->registerEvent('creating', fn() => ...)` |
| `muteEvents($events)` | Coupe des événements | `$user->muteEvents(['created'])->save()` |
| `saveQuietly()` | Sauvegarde sans déclencher d'événements | `$user->saveQuietly()` |

### Méthodes Utilitaires

| Méthode | Description | Exemple |
|---------|-------------|---------|
| `withTrashed()` | Inclut les enregistrements soft deleted | `User::withTrashed()->get()` |
| `onlyTrashed()` | Uniquement les enregistrements soft deleted | `User::onlyTrashed()->get()` |
| `trashed()` | Vérifie si soft deleted | `$user->trashed()` |
| `isDirty($attribute)` | Vérifie si l'attribut a été modifié | `$user->isDirty('name')` |
| `isClean($attribute)` | Vérifie si l'attribut n'a pas été modifié | `$user->isClean('name')` |
| `getOriginal($attribute)` | Valeur originale de l'attribut | `$user->getOriginal('name')` |
| `getDirty($attribute)` | Valeurs modifiées | `$user->getDirty()` |
| `wasChanged($attributes)` | Vérifie si l'attribut a changé depuis la sauvegarde | `$user->wasChanged('name')` |
| `fill($attributes)` | Remplit le modèle | `$user->fill(['name' => 'John'])` |
| `fresh($attributes)` | Recharge le modèle depuis la base | `$user->fresh()` |
| `on($connection)` | Change de connexion | `User::on('mysql2')->find(1)` |
| `marker($value)` | Marqueur de placeholder personnalisé | `User::marker(':')->where('id = :id', ['id' => 1])` |

---

## Prochaines Étapes

Maintenant que vous maîtrisez Elegant ORM :

1. **[Entités](entities.md)** → Définir la structure des tables
2. **[Migrations](migration.md)** → Gérer les modifications de schéma
3. **[Seeders](seeding.md)** → Peupler la base de données

---

**Elegant ORM compris !** 🚀 → [Entités](entities.md)