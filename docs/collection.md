- [Introduction](#introduction)
- [Prise en main](#prise-en-main)
- [Principes de conception](#principes-de-conception)
- [Structures de données disponibles](#structures-de-donnees-disponibles)
    - [Collection de tableaux](#collection-de-tableaux)
    - [Collection associative (Map)](#collection-associative-map-)
    - [Collections personnalisées](#collections-personnalisees)
- [Opérations courantes](#operations-courantes)
    - [Filtrage](#filtrage)
    - [Tri](#tri)
    - [Transformation](#transformation)
    - [Agrégation](#agregation)
    - [Recherche](#recherche)
    - [Chaînage](#chainage)
- [Exemples d'utilisation](#exemples-d-39-utilisation)
- [Référence des méthodes](#reference-des-methodes)

## Introduction

Le module de collections du framework **Tonka** fournit une couche d'abstraction orientée objet au-dessus des structures de données natives de PHP. Il expose une API fluide et cohérente pour manipuler des jeux de données (filtrage, tri, transformation, agrégation) sans recourir à des boucles impératives ni à l'enchaînement de fonctions `array_*` peu lisibles.

Le module prend en charge les tableaux indexés, les structures clé-valeur et les types de données personnalisés, ce qui permet d'écrire un code plus déclaratif, plus concis et plus facile à maintenir.

## Prise en main

Une collection s'instancie de deux manières : via la fonction utilitaire `collection()` ou directement à partir de la classe `Clicalmani\Core\Collection\Collection`.

#### Via la fonction utilitaire

```php
$collection = collection([1, 2, 3, 4]);
```

#### Via l'instanciation de la classe

```php
use Clicalmani\Core\Collection\Collection;

$collection = new Collection([1, 2, 3, 4]);
```

Les deux approches sont équivalentes et donnent accès à l'ensemble des méthodes décrites dans cette documentation.

#### Exécuter des opérations

Les collections exposent des méthodes de filtrage, de tri et de transformation directement chaînables :

```php
User::all()->filter(fn(User $user) => $user->isAdmin());
User::all()->sortBy('name');
```

## Principes de conception

Le module repose sur trois principes directeurs :

- **Mutabilité :** par défaut, les collections sont mutables. La plupart des opérations modifient l'instance courante au lieu d'en créer une nouvelle.
  <!-- À vérifier : le tableau de référence indique que filter() et map() « retournent une nouvelle collection ». Harmoniser les deux affirmations. -->
- **Chaînabilité :** chaque méthode de transformation retourne une collection, ce qui autorise le chaînage d'appels pour un code expressif et concis.
- **Flexibilité de typage :** une collection peut contenir des tableaux, des objets ou tout type de donnée personnalisé.

La compréhension de ces principes est essentielle pour exploiter pleinement le module, en particulier le comportement lié à la mutabilité lors de l'enchaînement de plusieurs opérations.

## Structures de données disponibles

Le module propose plusieurs structures adaptées à différents cas d'usage. Toutes partagent une API cohérente, ce qui permet de passer de l'une à l'autre sans réécrire la logique métier.

### Collection de tableaux

Encapsulation orientée objet d'un tableau PHP, enrichie de méthodes avancées de manipulation, de recherche et de transformation.

```php
use Clicalmani\Core\Collection\Collection;

$arrayCollection = new Collection([1, 2, 3, 4]);
```

### Collection associative (Map)

Structure clé-valeur comparable à un tableau associatif, optimisée pour les accès par clé et dotée d'opérations avancées.

```php
use Clicalmani\Core\Collection\Map;

$mapCollection = new Map(['name' => 'Alice', 'age' => 30]);
$mapCollection->get('age'); // 30
$mapCollection->set('age', 37);
$mapCollection->get('age'); // 37
```

### Collections personnalisées

Il est possible d'étendre la classe de base `Collection` afin de définir des structures spécialisées, propres au domaine métier de l'application.

```php
use Clicalmani\Core\Collection\Collection;

class UserCollection extends Collection
{
    // Ajoutez ici vos méthodes spécifiques ou surchargez le comportement existant
}
```

## Opérations courantes

Les opérations ci-dessous couvrent la majorité des besoins de manipulation de données.

### Filtrage

La méthode `filter` conserve uniquement les éléments qui satisfont la condition définie par la fonction de rappel (*callback*) :

```php
$adults = $users->filter(fn($user) => $user['age'] >= 18);
```

### Tri

Les éléments peuvent être triés selon une clé ou une logique personnalisée, en ordre croissant (`sortBy`) ou décroissant (`sortByDesc`) :

```php
$sorted = $users->sortBy('name');
$sortedDesc = $users->sortByDesc('created_at');
```

### Transformation

La méthode `map` applique une fonction à chaque élément et construit le résultat à partir des valeurs retournées :

```php
$names = $users->map(fn($user) => $user['name']);
```

### Agrégation

Les méthodes `sum`, `count` et `reduce` permettent de calculer une valeur unique à partir de l'ensemble des éléments :

```php
$total = $orders->sum('amount');
$count = $users->count();
```

### Recherche

Les méthodes `find`, `first` et `contains` permettent de localiser un ou plusieurs éléments :

```php
$firstAdmin = $users->first(fn($user) => $user['role'] === 'admin');
$hasAlice = $users->contains('Alice');
```

### Chaînage

Ces opérations se combinent pour former des pipelines de traitement lisibles, où chaque étape décrit une intention précise :

```php
$names = $users
    ->filter(fn($user) => $user['active'])
    ->sortBy('name')
    ->map(fn($user) => $user['name']);
```

## Exemples d'utilisation

Les exemples suivants illustrent des cas d'usage concrets du module.

### Exemple 1 : filtrer et transformer une liste d'utilisateurs

Sélection des utilisateurs actifs et majeurs, puis extraction de leur nom :

```php
$users = collection([
    ['name' => 'Alice', 'age' => 25, 'active' => true],
    ['name' => 'Bob', 'age' => 17, 'active' => false],
    ['name' => 'Charlie', 'age' => 30, 'active' => true],
]);

$activeAdults = $users
    ->filter(fn($user) => $user['active'] && $user['age'] >= 18)
    ->map(fn($user) => $user['name']);

// $activeAdults contient ['Alice', 'Charlie']
```

### Exemple 2 : trier et agréger des commandes

Classement des commandes par montant décroissant, puis calcul du montant total :

```php
$orders = collection([
    ['id' => 1, 'amount' => 50],
    ['id' => 2, 'amount' => 100],
    ['id' => 3, 'amount' => 75],
]);

$biggestOrder = $orders
    ->sortByDesc('amount')
    ->first();

// $biggestOrder correspond à ['id' => 2, 'amount' => 100]

$totalAmount = $orders->sum('amount');

// $totalAmount vaut 225
```

### Exemple 3 : manipuler une Map

Vérification de l'existence d'une clé dans une collection associative :

```php
use Clicalmani\Core\Collection\Map;

$settings = new Map([
    'theme' => 'dark',
    'notifications' => true,
]);

$hasTheme = $settings->has('theme'); // true
```

Ces exemples montrent comment les collections réduisent la complexité du traitement de données et améliorent la lisibilité du code.

## Référence des méthodes

| Méthode                      | Description                                                                                   |
|------------------------------|-----------------------------------------------------------------------------------------------|
| `all()`                      | Retourne l'ensemble des éléments sous forme de tableau.                                       |
| `filter(callable)`           | Filtre les éléments à l'aide d'un callback et retourne une nouvelle collection.               |
| `map(callable)`              | Applique un callback à chaque élément et retourne une nouvelle collection.                    |
| `reduce(callable, $initial)` | Réduit la collection à une valeur unique, à partir d'une valeur initiale.                     |
| `sortBy($key)`               | Trie les éléments selon une clé ou un callback (ordre croissant).                             |
| `sortByDesc($key)`           | Trie les éléments selon une clé ou un callback (ordre décroissant).                           |
| `first(callable?)`           | Retourne le premier élément satisfaisant la condition, ou le premier élément par défaut.      |
| `find($key, $default?)`      | Recherche un élément par clé ; retourne la valeur par défaut si aucun élément n'est trouvé.   |
| `contains($key, $value?)`    | Indique si la collection contient la clé ou la valeur spécifiée.                              |
| `sum($key?)`                 | Retourne la somme des valeurs d'une clé donnée, ou de tous les éléments.                      |
| `count()`                    | Retourne le nombre d'éléments de la collection.                                               |
| `isEmpty()`                  | Indique si la collection est vide.                                                            |
| `toArray()`                  | Convertit la collection en tableau PHP natif.                                                 |