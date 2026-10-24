# Entités

- [Entités](#entites)
- [Introduction](#introduction)
- [Définition d'une Entité](#definition-d-39-une-entite)
  - [Création](#creation)
  - [Structure](#structure)
  - [Attributs PHP 8](#attributs-php-8)
- [Types de Données](#types-de-donnees)
  - [Types Numériques](#types-numeriques)
  - [Types de Caractères](#types-de-caracteres)
  - [Types de Dates](#types-de-dates)
  - [Types JSON](#types-json)
  - [Types Spatiaux](#types-spatiaux)
  - [Options des Types](#options-des-types)
- [Propriétés et Attributs](#proprietes-et-attributs)
  - [Attribut Property](#attribut-property)
  - [Options de Property](#options-de-property)
  - [Attribut Validate](#attribut-validate)
  - [Attribut PrimaryKey](#attribut-primarykey)
  - [Attribut Index](#attribut-index)
  - [Attribut Engine](#attribut-engine)
  - [Attribut DefaultCollation](#attribut-defaultcollation)
  - [Attribut AlterOption](#attribut-alteroption)
  - [Attribut Priority](#attribut-priority)
- [Index](#index)
  - [Index Simple](#index-simple)
  - [Index Unique](#index-unique)
  - [Index avec Clé Étrangère](#index-avec-cle-etrangere)
  - [Index Multiples](#index-multiples)
  - [Conventions de Nommage](#conventions-de-nommage)
- [Clés Primaires](#cles-primaires)
  - [Clé Primaire Simple](#cle-primaire-simple)
  - [Clé Primaire Composite](#cle-primaire-composite)
- [Valeurs par Défaut](#valeurs-par-defaut)
- [Auto-casting](#auto-casting)
  - [Activation](#activation)
  - [Désactivation](#desactivation)
- [Auto-formatting](#auto-formatting)
- [Validation dans les Entités](#validation-dans-les-entites)
- [Méthodes de l'Entité](#methodes-de-l-39-entite)
- [Entité et Modèle](#entite-et-modele)
- [Prochaines Étapes](#prochaines-etapes)

---

## Introduction

Les **Entités** dans Tonka définissent la structure des tables de votre base de données. Elles sont le pendant métier des schémas SQL, et servent de base à l'ORM Elegant pour générer les tables, les index et les contraintes.

Contrairement aux modèles, les entités ne contiennent pas de logique métier. Elles se concentrent uniquement sur la définition des colonnes, des types, des index et des contraintes.

---

## Définition d'une Entité

### Création

```bash
php tonka make:entity UserEntity
```

### Structure

```php
namespace Database\Entities;

use Clicalmani\Database\DataTypes\Integer;
use Clicalmani\Database\DataTypes\VarChar;
use Clicalmani\Database\DataTypes\DateTime;
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

    #[Property(length: 255)]
    public VarChar $name;

    #[Property(length: 191)]
    public VarChar $email;

    #[Property]
    public DateTime $created_at;

    #[Property]
    public DateTime $updated_at;

    #[Property]
    public ?DateTime $deleted_at;
}
```

### Attributs PHP 8

| Attribut | Description | Applicable à |
|----------|-------------|--------------|
| `#[Property]` | Définit une colonne | Propriété |
| `#[PrimaryKey]` | Définit la clé primaire | Propriété / Classe |
| `#[Index]` | Définit un index | Classe |
| `#[Validate]` | Définit des règles de validation | Propriété |
| `#[Engine]` | Définit le moteur de table | Classe |
| `#[DefaultCollation]` | Définit la collation | Classe |
| `#[AlterOption]` | Définit une modification | Classe |

---

## Types de Données

### Types Numériques

| Type | Description | SQL | Exemple |
|------|-------------|-----|---------|
| `Integer` | Entier | INT | `public Integer $id;` |
| `BigInt` | Grand entier | BIGINT | `public BigInt $views;` |
| `TinyInt` | Petit entier | TINYINT | `public TinyInt $is_active;` |
| `SmallInt` | Entier court | SMALLINT | `public SmallInt $status;` |
| `Decimal` | Décimal | DECIMAL | `public Decimal $price;` |
| `Fixed` | Flottant à précision fixe | FIXED | `public Fixed $amount;` |
| `Float` | Flottant | FLOAT | `public Float $rating;` |

### Types de Caractères

| Type | Description | SQL | Exemple |
|------|-------------|-----|---------|
| `VarChar` | Chaîne variable | VARCHAR | `public VarChar $name;` |
| `Char` | Chaîne fixe | CHAR | `public Char $code;` |
| `Text` | Texte long | TEXT | `public Text $content;` |
| `TinyText` | Texte court | TINYTEXT | `public TinyText $summary;` |
| `MediumText` | Texte moyen | MEDIUMTEXT | `public MediumText $description;` |
| `LongText` | Texte très long | LONGTEXT | `public LongText $body;` |
| `Enum` | Énumération | ENUM | `public Enum\|string $status;` |
| `Set` | Ensemble | SET | `public Set\|string $options;` |

### Types de Dates

| Type | Description | SQL | Exemple |
|------|-------------|-----|---------|
| `Date` | Date | DATE | `public Date $birthday;` |
| `DateTime` | Date et heure | DATETIME | `public DateTime $created_at;` |
| `Time` | Heure | TIME | `public Time $opening_time;` |
| `Timestamp` | Timestamp | TIMESTAMP | `public Timestamp $last_login;` |

### Types JSON

| Type | Description | SQL | Exemple |
|------|-------------|-----|---------|
| `Json` | JSON | JSON | `public Json $metadata;` |

### Types Spatiaux

| Type | Description | SQL | Exemple |
|------|-------------|-----|---------|
| `Geometry` | Géométrie | GEOMETRY | `public Geometry $location;` |
| `Point` | Point | POINT | `public Point $coordinates;` |
| `LineString` | Ligne | LINESTRING | `public LineString $path;` |
| `Polygon` | Polygone | POLYGON | `public Polygon $area;` |

### Type Booléen

| Type | Description | SQL | Exemple |
|------|-------------|-----|---------|
| `Boolean` | Booléen | BOOLEAN | `public Boolean $is_active;` |

### Options des Types

```php
// Options communes
#[Property(
    length: 255,        // Longueur
    unsigned: true,     // Non signé
    autoIncrement: true,// Auto-incrémentation
    scale: 10, // Partie entière d'un nombre décimal
    precision: 2, // Partie décimal
    comment: 'Role',    // Commentaire
    values: ['Active', 'On-Leave', 'Resigned'],
    charset: 'utf8mb4', // Jeu de caractères
    collate: 'utf8mb4_unicode_ci', // Collation
    autoCast: true,     // Auto-casting
    formatter: 'formatDate' // Formateur
)]
```

---

## Propriétés et Attributs

### Attribut Property

```php
#[Property(
    values: ['active', 'inactive', 'blocked'],
    comment: 'User status'
)]
public Enum|string $status = 'active';
```

### Options de Property

| Option | Description | Exemple |
|--------|-------------|---------|
| `length` | Longueur | `length: 255` |
| `unsigned` | Non signé | `unsigned: true` |
| `autoIncrement` | Auto-incrémentation | `autoIncrement: true` |
| `scale` | Nombre de chiffres avant la virgule | `scale: 10` |
| `precision` | Nombre de chiffres après la virgule | `precision: 4` |
| `comment` | Commentaire | `comment: 'Role'` |
| `values` | Liste | `values: ['active', 'inactive', 'blocked']` |
| `charset` | Jeu de caractères | `charset: 'utf8mb4'` |
| `collate` | Collation | `collate: 'utf8mb4_unicode_ci'` |
| `autoCast` | Auto-casting | `autoCast: false` |
| `formatter` | Formateur | `formatter: 'formatDate'` |

### Attribut Validate

```php
#[Validate('required|email|max:255')]
public VarChar $email;

#[Validate('required|password|min:8|common:no')]
public VarChar $password;

#[Validate('required|in:M,F,O')]
public Enum|string|null $gender = 'M';
```

Cet attribut applique les mêmes règles et la même syntaxe que celles détaillées sur la page [Validation](validation.md#règles-intégrées) — au niveau de l'entité, la contrainte s'applique systématiquement, quel que soit le point d'entrée (HTTP, console, seeder).

### Attribut PrimaryKey

```php
// Simple
#[PrimaryKey]
public Integer $id;

// Composite
#[PrimaryKey(['user_id', 'role_id'])]
class UserRoleEntity extends Entity
```

### Attribut Index

```php
// Index simple
#[Index(
    name: 'users_email_idx',
    key: 'email'
)]

// Index unique
#[Index(
    name: 'users_email_unique',
    key: 'email',
    unique: true
)]

// Index avec clé étrangère
#[Index(
    name: 'fk_user_role_users1_idx',
    key: 'user_id',
    constraint: 'fk_user_role_users1',
    references: User::class
)]
```

> Un catalogue plus complet des options d'index (unicité, contrainte de clé étrangère, index multiples, conventions de nommage) est disponible dans la section [Index](#index) dédiée, plus bas sur cette page.

### Attribut Engine

```php
#[Engine('InnoDB')]
class UserEntity extends Entity
```

### Attribut DefaultCollation

```php
#[DefaultCollation(charset: 'utf8mb4', collate: 'utf8mb4_unicode_ci')]
class UserEntity extends Entity
```

### Attribut AlterOption

```php
#[AlterOption(handler: 'customAlter')]
class UserEntity extends Entity
{
    public function customAlter(string $sql): void
    {
        // Logique personnalisée
    }
}
```

### Attribut Priority

```php
#[Priority(1)]
class UserSeeder extends Seeder
```

> **Remarque :** contrairement aux autres attributs de cette section, `#[Priority]` s'applique à une classe de *seeder* plutôt qu'à une entité — il détermine l'ordre d'exécution des seeders entre eux (voir [Seeders](seeding.md)), et non un aspect du schéma de table.

---

## Index

### Index Simple

```php
#[Index(
    name: 'users_email_idx',
    key: 'email'
)]
```

### Index Unique

```php
#[Index(
    name: 'users_email_unique',
    key: 'email',
    unique: true
)]
```

### Index avec Clé Étrangère

```php
#[Index(
    name: 'fk_user_role_users1_idx',
    key: 'user_id',
    constraint: 'fk_user_role_users1',
    references: User::class,
    onDelete: Index::ON_DELETE_CASCADE,
    onUpdate: Index::ON_UPDATE_CASCADE
)]
```

### Index Multiples

```php
// Index composite (sur plusieurs colonnes)
#[Index(
    name: 'users_role_status_idx',
    key: 'role, status'
)]

// Plusieurs index déclarés côte à côte
#[Index(
    name: 'fk_user_role_users1_idx',
    key: 'user_id',
    constraint: 'fk_user_role_users1',
    references: User::class
), Index(
    name: 'fk_user_role_roles1_idx',
    key: 'role_id',
    constraint: 'fk_user_role_roles1',
    references: Role::class
)]
```

### Conventions de Nommage

| Type | Convention | Exemple |
|------|------------|---------|
| **Index FK** | `fk_{table}_{ref_table}_idx` | `fk_user_role_users1_idx` |
| **Constraint FK** | `fk_{table}_{ref_table}` | `fk_user_role_users1` |
| **Index Simple** | `{table}_{column}_idx` | `users_email_idx` |
| **Index Unique** | `{table}_{column}_unique` | `users_email_unique` |

Respecter ces conventions facilite le diagnostic des erreurs de contrainte remontées par le moteur SQL (dont le message d'erreur cite le nom de la contrainte), et évite les collisions de noms entre entités définies indépendamment.

---

## Clés Primaires

### Clé Primaire Simple

```php
#[Property(
    length: 10,
    unsigned: true,
    autoIncrement: true
), PrimaryKey]
public Integer $id;
```

### Clé Primaire Composite

```php
#[PrimaryKey(['user_id', 'role_id'])]
class UserRoleEntity extends Entity
{
    #[Property(length: 10, unsigned: true)]
    public Integer $user_id;

    #[Property(length: 10, unsigned: true)]
    public Integer $role_id;
}
```

---

## Valeurs par Défaut

```php
// Valeur par défaut pour une chaîne
public VarChar|string $role = 'user';

// Valeur par défaut pour un booléen
public TinyInt|int $is_active = 1;

// Valeur par défaut pour un nombre
public Integer|int $views = 0;

// Valeur par défaut nullable
public ?DateTime $deleted_at = null;

// Valeur par défaut pour un enum
public Enum|string $status = 'active';
```

---

## Auto-casting

### Activation

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
```

### Désactivation

```php
// Désactiver pour un attribut spécifique
#[Property(autoCast: false)]
public DateTime $created_at;

// Désactiver pour tout le modèle
class User extends Elegant
{
    protected $autoCast = false;
}
```

---

## Auto-formatting

L'auto-formatting permet de formater une valeur au moment de son affichage, sans altérer la valeur brute stockée en base.

```php
class UserEntity extends Entity
{
    public function formatDate(mixed $value): string
    {
        return is_string($value) 
            ? Carbon::parse($value)->format('d/m/Y')
            : $value;
    }

    public function formatDateTime(mixed $value): string
    {
        return is_string($value) 
            ? Carbon::parse($value)->format('d/m/Y H:i')
            : $value;
    }

    public function formatCurrency(mixed $value): string
    {
        return is_numeric($value) 
            ? number_format($value, 2, ',', ' ') . ' €'
            : $value;
    }

    #[Property(formatter: 'formatDate')]
    public DateTime $created_at;

    #[Property(formatter: 'formatDateTime')]
    public DateTime $updated_at;

    #[Property(formatter: 'formatCurrency')]
    public Decimal $salary;
}
```

> **Distinction avec l'auto-casting :** l'auto-casting convertit la valeur en un type PHP natif exploitable (`DateTime`, `bool`, `int`...) ; le formatteur va plus loin en produisant une représentation **textuelle prête à l'affichage** (une date au format français, un montant avec séparateur de milliers et devise). Les deux mécanismes se combinent naturellement : la valeur est d'abord castée, puis formatée si un `formatter` est déclaré.

---

## Validation dans les Entités

```php
use Clicalmani\Database\Factory\Validate;

class UserEntity extends Entity
{
    #[Validate('required|email|max:255')]
    public VarChar $email;

    #[Validate('required|password|hash|min:8')]
    public VarChar $password;

    #[Validate('required|in:M,F,O')]
    public Enum|string|null $gender = 'M';

    #[Validate('integer|min:18|max:120')]
    public TinyInt|int|null $age;
}
```

---

## Entité et Modèle

```php
// Entité
class UserEntity extends Entity
{
    #[Property(length: 255)]
    public VarChar $name;

    #[Property(length: 191)]
    public VarChar $email;

    // ── Propriétés personnalisées ──────────────

    public string $full_name {
        get => $this->model->first_name . ' ' . $this->model->last_name;
    }
}

// Modèle
class User extends Elegant
{
    protected string $entity = UserEntity::class;

    protected string $table = "users AS u"; // Ou avec espace  users u
    protected string|array $primaryKey = "id";

    protected $hidden = ['password'];
}
```

---

## Prochaines Étapes

Maintenant que vous maîtrisez les entités :

1. **[Migrations](migration.md)** → Gérer les modifications de schéma
2. **[Seeders](seeding.md)** → Peupler la base de données
3. **[Elegant ORM](orm.md)** → Approfondir l'ORM

---

**Entités maîtrisées !** 🚀 → [Migrations](migration.md)