# Seeders

- [Introduction](#introduction)
- [Factories](#factories)
  - [Création d'une Factory](#creation-d-39-une-factory)
  - [Structure d'une Factory](#structure-d-39-une-factory)
  - [États (States)](#etats-states-)
- [Faker](#faker)
  - [Méthodes Disponibles](#methodes-disponibles)
  - [Utilisation dans une Factory](#utilisation-dans-une-factory)
- [Séquences](#sequences)
  - [Définition](#definition)
  - [Utilisation](#utilisation)
- [Priorités](#priorites)
- [Seeders](#seeders)
  - [Création d'un Seeder](#creation-d-39-un-seeder)
  - [Structure d'un Seeder](#structure-d-39-un-seeder)
- [Commandes de Seeding](#commandes-de-seeding)
  - [db:seed](#db-seed)
  - [migrate:fresh avec --seed](#migrate-fresh-avec-seed)
- [Exemple Complet](#exemple-complet)
- [Prochaines Étapes](#prochaines-etapes)

---

## Introduction

Les **Seeders** permettent de peupler votre base de données avec des données de test ou des données initiales. Tonka fournit un système complet de seeding avec :

- ✅ **Factories** : génération de données structurées
- ✅ **Faker** : données réalistes (noms, emails, etc.)
- ✅ **Séquences** : génération de données dynamiques
- ✅ **Priorités** : ordre d'exécution des seeders

---

## Factories

### Création d'une Factory

```bash
php tonka make:factory UserFactory --model=User
```

### Structure d'une Factory

```php
<?php

namespace Database\Factories;

use Clicalmani\Database\Factory\Factory;
use App\Models\User;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker()->name(),
            'email' => $this->faker()->email(),
            'password' => password('password123'),
            'role' => 'user',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ];
    }
}
```

### États (States)

```php
class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker()->name(),
            'email' => $this->faker()->email(),
            'password' => password('password123'),
            'role' => 'user',
            'is_active' => true,
        ];
    }

    // État admin
    public function admin(): self
    {
        return $this->state(function(array $attr) {
            return [
                ...$attr,
                'role' => 'admin',
                'email' => 'admin@example.com'
            ];
        });
    }

    // État inactif
    public function inactive(): self
    {
        return $this->state(function(array $attr) {
            return [
                ...$attr,
                'is_active' => false
            ];
        });
    }
}
```

Un état modifie un sous-ensemble des attributs par défaut de la factory (`$this->state(...)` fusionne les valeurs retournées avec l'état courant), plutôt que de redéfinir l'ensemble du jeu de données — pratique pour composer plusieurs variantes (`admin()`, `inactive()`, `admin()->inactive()`...) sans dupliquer `definition()`.

---

## Faker

### Méthodes Disponibles

```php
// Noms
$this->faker()->name();           // John Doe
$this->faker()->singleName();     // John
$this->faker()->firstName();      // John
$this->faker()->lastName();       // Doe

// Contacts
$this->faker()->email();          // john.doe@example.com
$this->faker()->phone();          // +229 01 64 34 56 78
$this->faker()->address();        // 110 Southmoor Road
                                  // Oxford OX2 6RB
                                  // UK

// Texte
$this->faker()->paragraph();      // Texte aléatoire
$this->faker()->sentence();       // Phrase aléatoire
$this->faker()->word();           // Mot aléatoire
$this->faker()->text(5);          // Texte de 5 paragraphes
$this->faker()->string();         // Chaîne de caractères aléatoire
$this->faker()->alpha();          // Caractères alphabétiques a-z
$this->faker()->alphaNum();       // Caractères alphanumériques

// Nombres
$this->faker()->num();            // Nombre de taille fixe, ex : 9876
$this->faker()->number();         // Nombre compris entre un min et un max
$this->faker()->integer(1, 100); // 42
$this->faker()->float(2, 0, 100); // 42.50

// Dates
$this->faker()->date();           // 2024-01-15
$this->faker()->dateTime();       // 2024-01-15 14:30:00
$this->faker()->time();           // 19:20
$this->faker()->month();          // 02
$this->faker()->day();            // 31
$this->faker()->year();           // 2026
$this->faker()->hour();           // 11
$this->faker()->timestamp();      // 12544540035
$this->faker()->timezone();       // Africa/Porto-Novo

// Lieux
$this->faker()->country();        // Bénin
$this->faker()->city();           // Cotonou
$this->faker()->lat();            // Latitude
$this->faker()->lon();            // Longitude

// Unicité
$this->faker()->unique()->safeEmail;      // Adresse e-mail unique
$this->faker()->unique()->safeName;       // Nom unique
$this->faker()->unique()->safeUserName;   // Nom d'utilisateur unique
```

### Utilisation dans une Factory

```php
class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker()->name(),
            'email' => $this->faker()->unique()->safeEmail,
            'password' => password('password123'),
            'age' => $this->faker()->integer(18, 65),
            'role' => new Sequence('admin', 'user', 'moderator'),
            'created_at' => $this->faker()->dateTime(),
        ];
    }
}
```

---

## Séquences

### Définition

Les séquences permettent de générer des valeurs différentes à chaque itération, en alternant sur une liste de valeurs fournie.

```php
use Clicalmani\Database\Factory\Sequence;

// Séquence de valeurs fixes
new Sequence('P', 'R', 'N')  // 'P', 'R', 'N', 'P', 'R', 'N', ...

// Séquence de données générées à l'avance
new Sequence(
    json_encode([['value' => faker()->email()]]),
    json_encode([['value' => faker()->email()]]),
    json_encode([['value' => faker()->email()]])
)

// Séquence avec callback, évaluée à chaque itération
new Sequence(fn() => faker()->email())
```

### Utilisation

```php
class SettingFactory extends Factory
{
    public function recruitment(): self
    {
        return $this->state(function() {
            $keys = [
                'staffing_workflow',
                'contract_workflow',
                'templates'
            ];

            $values = [
                serialize(['workflow1']),
                serialize(['workflow2']),
                serialize(['templates'])
            ];

            return [
                'key_name' => new Sequence(...$keys),
                'key_value' => new Sequence(...$values),
                'key_type' => new Sequence('recruitment'),
                'company_id' => new Sequence(1)
            ];
        });
    }
}
```

Ici, `key_name` et `key_value` alternent en parallèle sur leurs listes respectives à chaque enregistrement généré, associant `staffing_workflow` à `workflow1`, `contract_workflow` à `workflow2`, etc. — utile pour générer un jeu de paramétrage cohérent plutôt que des combinaisons aléatoires.

---

## Priorités

Les seeders peuvent être ordonnés avec des priorités :

```php
use Clicalmani\Database\Factory\Priority;

#[Priority(1)] // Exécuté en premier
class CompanySeeder extends Seeder
{
    public function run(): void
    {
        Company::seed()->count(5)->make();
    }
}

#[Priority(2)] // Exécuté en deuxième
class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::seed()->count(10)->make();
    }
}

#[Priority(3)] // Exécuté en troisième
class PostSeeder extends Seeder
{
    public function run(): void
    {
        Post::seed()->count(20)->make();
    }
}
```

Cette priorité est indispensable dès que des seeders dépendent les uns des autres via une clé étrangère : ici, `CompanySeeder` doit nécessairement s'exécuter avant `UserSeeder` si les utilisateurs générés référencent une entreprise existante.

---

## Seeders

### Création d'un Seeder

```bash
php tonka make:seeder UserSeeder
```

### Structure d'un Seeder

```php
<?php

namespace Database\Seeders;

use Clicalmani\Database\Seeders\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Création avec la Factory
        User::seed()->count(10)->make();

        // Création avec un état spécifique
        User::seed()->count(5)->admin()->make();

        // Création manuelle
        User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => password('admin123'),
            'role' => 'admin'
        ]);
    }
}
```

---

## Commandes de Seeding

### db:seed

```bash
# Exécuter un seeder spécifique
php tonka db:seed --seeder=UserSeeder

# Exécuter les seeders déclarés dans le manifest
php tonka db:seed --file=manifest
```

### migrate:fresh avec --seed

```bash
# Réinitialise le schéma puis exécute les seeders
php tonka migrate:fresh --manifest=manifest --seed
```

!> ⚠️ **Rappel** (déjà signalé sur la page [Déploiement](deployment.md#verification-finale)) : `migrate:fresh` réinitialise intégralement le schéma de la base de données. Réservez son usage à un environnement de développement ou de test.

---

## Exemple Complet

### 1. Factory

```php
namespace Database\Factories;

use Clicalmani\Database\Factory\Factory;
use App\Models\User;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker()->name(),
            'email' => $this->faker()->email(),
            'password' => password('password123'),
            'role' => 'user',
            'is_active' => true,
        ];
    }

    public function admin(): self
    {
        return $this->state(function() {
            return [
                'role' => 'admin',
                'email' => 'admin@example.com'
            ];
        });
    }
}
```

### 2. Seeder

```php
namespace Database\Seeders;

use Clicalmani\Database\Seeders\Seeder;
use App\Models\User;
use Clicalmani\Database\Factory\Priority;

#[Priority(2)]
class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 10 utilisateurs aléatoires
        User::seed()->count(10)->make();

        // 3 admins
        User::seed()->count(3)->admin()->make();
    }
}
```

Pour utiliser `seed()` avec factory, le modèle doit déclarer le trait `HasFactory` :

```php
namespace App\Models;

use Clicalmani\Core\Acme\Model as Elegant;;
use Clicalmani\Database\Traits\HasFactory;

/**
 * @method static \Database\Factories\UserFactory seed()
 */
class User extends Elegant
{
    use HasFactory;

    protected string $table = 'users';
}
```

### 3. Exécution

```bash
php tonka migrate:fresh --manifest=manifest --seed
```

---

## Prochaines Étapes

Maintenant que vous maîtrisez les seeders :

1. **[Authentification](authentication.md)** → Gérer l'authentification
2. **[Autorisation](authorization.md)** → Gérer les permissions
3. **[Événements](events.md)** → Système d'événements

---

**Seeders compris !** 🚀 → [Authentification](authentication.md)