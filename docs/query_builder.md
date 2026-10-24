# Query Builder

- [Query Builder](#query-builder)
- [Introduction](#introduction)
- [Exécution de Requêtes](#execution-de-requetes)
  - [Récupération de Toutes les Lignes](#recuperation-de-toutes-les-lignes)
  - [Récupération d'une Ligne](#recuperation-d-39-une-ligne)
  - [Récupération d'une Liste de Valeurs](#recuperation-d-39-une-liste-de-valeurs)
  - [Traitement par Lots (Chunking)](#traitement-par-lots-chunking-)
  - [Streaming Paresseux (Lazy)](#streaming-paresseux-lazy-)
  - [Agrégations](#agregations)
  - [Déterminer si des Enregistrements Existent](#determiner-si-des-enregistrements-existent)
- [Clause Select](#clause-select)
  - [Spécifier les Colonnes](#specifier-les-colonnes)
  - [Jointures](#jointures)
    - [Jointure Implicite (Comma-Join)](#jointure-implicite-comma-join-)
    - [Left Join / Right Join](#left-join-right-join)
    - [Inner Join](#inner-join)
    - [Cross Join](#cross-join)
    - [Jointures Avancées](#jointures-avancees)
      - [Jointure Personnalisée (Custom Join)](#jointure-personnalisee-custom-join-)
      - [Jointure par Sous-Requête](#jointure-par-sous-requete)
- [Clause Where](#clause-where)
  - [Where Simple](#where-simple)
  - [Or Where](#or-where)
  - [Where In](#where-in)
  - [Where Exists](#where-exists)
- [Tri, Regroupement, Limite](#tri-regroupement-limite)
  - [orderBy](#orderby)
  - [groupBy](#groupby)
  - [having](#having)
  - [Limite et Offset](#limite-et-offset)
    - [limit](#limit)
    - [top](#top)
    - [paginate](#paginate)
- [Clauses Conditionnelles](#clauses-conditionnelles)
  - [when](#when)
  - [unless](#unless)
- [Instructions d'Insertion](#instructions-dinsertion)
- [Instructions de Mise à Jour](#instructions-de-mise-a-jour)
- [Incrémentation / Décrémentation](#incrementation-decrementation)
- [Instructions de Suppression](#instructions-de-suppression)
- [Verrouillage / Déverrouillage](#verrouillage-deverrouillage)
- [Prochaines Étapes](#prochaines-etapes)

---

## Introduction

Le Query Builder de Tonka fournit une interface fluide et pratique pour construire et exécuter des requêtes SQL. Il s'appuie sur PDO et protège nativement contre les injections SQL grâce aux requêtes préparées.

Il s'utilise via la façade `DB` :

```php
use Clicalmani\Core\Support\Facades\DB;
```

> **Pour les développeurs Laravel/Symfony :** l'API (`DB::table()->where()->get()`) est directement inspirée du Query Builder de Laravel — la syntaxe des conditions diffère toutefois : Tonka utilise des fragments SQL bruts avec placeholders positionnels (`'active = ?', [1]`) plutôt que la forme `->where('active', 1)` de Laravel ou la construction par expressions de Doctrine QueryBuilder (`->andWhere($qb->expr()->eq(...))`).

---

## Exécution de Requêtes

### Récupération de Toutes les Lignes

```php
$users = DB::table('users')->get();
```

### Récupération d'une Ligne

```php
// Première ligne
$user = DB::table('users')->first();

// Ligne unique avec condition
$user = DB::table('users')
    ->where('id = ?', [1])
    ->first();
```

### Récupération d'une Liste de Valeurs

```php
// Une seule colonne
$names = DB::table('users')->pluck('name');

// Avec une colonne comme clé du tableau résultat
$emails = DB::table('users')->pluck('email', 'id');

// Une seule valeur scalaire
$name = DB::table('users')->value('name');
```

### Traitement par Lots (Chunking)

```php
// Traitement par lots de 100 lignes
DB::table('users')->chunk(100, function($users) {
    foreach ($users as $user) {
        // Traitement
    }
});
```

Le chunking évite de charger l'intégralité d'une table volumineuse en mémoire d'un seul coup : les lignes sont récupérées et traitées par paquets successifs, à la manière d'une pagination interne transparente pour l'appelant.

### Streaming Paresseux (Lazy)

```php
// Traitement en flux, ligne par ligne, sans charger l'ensemble du résultat en mémoire
foreach (DB::table('users')->lazy() as $user) {
    // Traitement d'une ligne à la fois
}
```

Contrairement au chunking, qui charge les lignes par paquets fixes, `lazy()` retourne un itérateur qui ne matérialise chaque ligne qu'au moment où la boucle la consomme — la mémoire consommée reste quasi constante, indépendamment du nombre total de lignes.

### Agrégations

```php
$count = DB::table('users')->count();
$sum = DB::table('orders')->sum('amount');
$avg = DB::table('products')->avg('price');
$min = DB::table('products')->min('price');
$max = DB::table('products')->max('price');
```

### Déterminer si des Enregistrements Existent

```php
if (DB::table('users')->exists()) {
    // ...
}

if (DB::table('users')->doesntExist()) {
    // ...
}
```

`exists()`/`doesntExist()` exécutent une requête optimisée pour vérifier une présence sans récupérer les données elles-mêmes — à préférer systématiquement à `count() > 0`, qui force le moteur à dénombrer l'ensemble des lignes correspondantes.

---

## Clause Select

### Spécifier les Colonnes

```php
// Toutes les colonnes
$users = DB::table('users')->get();

// Colonnes spécifiques
$users = DB::table('users')
    ->get('id, name, email');

// Avec alias de colonne
$users = DB::table('users')
    ->get('id, name, email as user_email');

// Avec alias de table
$users = DB::table('users u')
    ->get('u.id, u.name');
```

### Jointures

#### Jointure Implicite (Comma-Join)

```php
$posts = DB::table('posts AS p, users AS u')
    ->where('p.user_id = u.id')
    ->get();
```

#### Left Join / Right Join

```php
// Left Join
$posts = DB::table('posts p')
    ->joinLeft('users u', 'p.user_id', 'u.id')
    ->get();

// Right Join
$posts = DB::table('posts p')
    ->joinRight('users u', 'p.user_id', 'u.id')
    ->get();
```

#### Inner Join

```php
$posts = DB::table('posts p')
    ->joinInner('users u', 'p.user_id', 'u.id')
    ->get();
```

#### Cross Join

```php
$posts = DB::table('posts p')
    ->joinCross('users u')
    ->get();
```

#### Jointures Avancées

##### Jointure Personnalisée (Custom Join)

```php
DB::table('posts p')
    ->join('users u', function($join) {
        $join->on('p.user_id = u.id')
             ->where('u.active = ?', [1]);
    })
    ->get();
```

Cette forme permet de combiner la condition de jointure (`on`) avec des conditions supplémentaires (`where`) directement dans la clause `JOIN` plutôt que dans le `WHERE` global de la requête — utile, par exemple, pour ne joindre que les utilisateurs actifs sans exclure les posts dont l'auteur est inactif du résultat final (jointure externe filtrée).

##### Jointure par Sous-Requête

```php
DB::table('posts AS p')->subWhere('users AS u', 'user_id', function($query) {
        $query->set('fields', 'id')->where('id = ?', [1]);
    })->get();
```

`set('fields', 'id')` restreint les colonnes sélectionnées par la sous-requête à `id` — un usage courant pour une sous-requête n'ayant besoin de retourner qu'une seule colonne de comparaison.

---

## Clause Where

### Where Simple

```php
$users = DB::table('users')
    ->where('active = ?', [1])
    ->get();

$users = DB::table('users')
    ->where('active = ? AND role = ?', [1, 'admin'])
    ->get();
```

### Or Where

```php
$users = DB::table('users')
    ->where('role = ?', ['admin'])
    ->orWhere('role = ?', ['moderator'])
    ->get();
```

### Where In

```php
$users = DB::table('users')
    ->whereIn('id', [1, 2, 3])
    ->get();

$users = DB::table('users')
    ->whereIn('role', ['admin', 'moderator'])
    ->get();
```

### Where Exists

```php
// Exists
DB::table('posts AS p')->whereExists(function($query) {
        $query->from('users AS u')
            ->set('fields', '1')
            ->where('id = ?', [1]);
    })->where('status = ?', ['published'])
    ->get();

// Not Exists
DB::table('posts AS p')->whereNotExists(function($query) {
        $query->from('users AS u')
            ->set('fields', '1')
            ->where('id = ?', [1]);
    })->where('status = ?', ['published'])
    ->get();

// Forme alternative, orientée relation : whereHas
DB::table('posts AS p')->whereHas('users AS u', function($query) {
        $query->where('id = ?', [1]);
    })->where('status = ?', ['published'])
    ->get();

// Son inverse : whereDoesntHave
DB::table('posts AS p')->whereDoesntHave('users AS u', function($query) {
        $query->where('id = ?', [1]);
    })->where('status = ?', ['published'])
    ->get();
```

`whereExists`/`whereNotExists` expriment directement une sous-requête `EXISTS` SQL ; `whereHas`/`whereDoesntHave` offrent une syntaxe plus déclarative pour le même résultat, orientée relation entre tables plutôt que sous-requête explicite.

---

## Tri, Regroupement, Limite

### orderBy

```php
$users = DB::table('users')
    ->orderBy('created_at DESC')
    ->get();

$users = DB::table('users')
    ->orderBy('name ASC')
    ->orderBy('created_at DESC')
    ->get();
```

### groupBy

```php
$users = DB::table('users')
    ->groupBy('role')
    ->get('role, COUNT(*) as total');
```

### having

```php
$users = DB::table('users')
    ->groupBy('role')
    ->having('total > ?', [5])
    ->get('role, COUNT(*) as total');
```

### Limite et Offset

#### limit

```php
$users = DB::table('users')
    ->skip(0)
    ->limit(10)
    ->get();
```

#### top

```php
$users = DB::table('users')
    ->top(10) // Les 10 premiers résultats
    ->get();
```

#### paginate

```php
$paginator = DB::table('users')
    ->paginate(1, 10); // Page 1, 10 résultats par page
$users = $paginator->result();
$paginator->hasMorePages(); 
```

---

## Clauses Conditionnelles

### when

```php
$users = DB::table('users')
    ->when($search, function($query) use ($search) {
        $query->where('name LIKE ? OR email LIKE ?', [
            "%$search%",
            "%$search%"
        ]);
    })
    ->get();
```

`when()` n'applique la clause imbriquée que si la première valeur passée est *truthy* — un moyen élégant d'ajouter conditionnellement un filtre sans multiplier les blocs `if` autour de la construction de la requête.

### unless

```php
$users = DB::table('users')
    ->unless($hideInactive, function($query) {
        $query->where('active = ?', [1]);
    })
    ->get();
```

`unless()` est l'inverse exact de `when()` : la clause imbriquée s'applique lorsque la condition est *falsy*.

---

## Instructions d'Insertion

```php
// Insertion simple
DB::table('users')->insert([
    'name' => 'John Doe',
    'email' => 'john@example.com'
]);

// Insertion avec retour de l'identifiant généré
$id = DB::table('users')->insertGetId([
    'name' => 'John Doe',
    'email' => 'john@example.com'
]);

// Insertion multiple
DB::table('users')->insert([
    ['name' => 'John', 'email' => 'john@example.com'],
    ['name' => 'Jane', 'email' => 'jane@example.com']
]);

// Insertion avec ignorance des doublons (MySQL — INSERT IGNORE)
DB::table('users')->insertIgnore([
    'name' => 'John',
    'email' => 'john@example.com'
]);
```

---

## Instructions de Mise à Jour

```php
// Mise à jour simple
DB::table('users')
    ->where('id = ?', [1])
    ->update(['name' => 'Jane Doe']);

// Incrémentation d'une colonne
DB::table('users')
    ->where('id = ?', [1])
    ->increment('views');

// Décrémentation d'une colonne
DB::table('users')
    ->where('id = ?', [1])
    ->decrement('score', 5);
```

---

## Incrémentation / Décrémentation

```php
// Incrémentation de 1 (par défaut)
DB::table('users')->increment('views');

// Incrémentation de 5
DB::table('users')->increment('views', 5);

// Décrémentation de 1 (par défaut)
DB::table('users')->decrement('score');

// Décrémentation de 10
DB::table('users')->decrement('score', 10);
```

`increment()`/`decrement()` génèrent une instruction SQL atomique (`UPDATE ... SET col = col + n`) plutôt qu'une lecture puis une écriture séparées — ce qui élimine toute condition de course (*race condition*) en cas d'accès concurrent à la même ligne.

---

## Instructions de Suppression

```php
// Suppression d'un enregistrement précis
DB::table('users')
    ->where('id = ?', [1])
    ->first()
    ->delete();

// Suppression de toutes les lignes correspondant à la condition
DB::table('users')
    ->where('active = ?', [0])
    ->forceDelete();

// Vider entièrement la table
DB::table('users')->truncate();
```

---

## Verrouillage / Déverrouillage

```php
// Verrouillage en écriture
DB::table('users')->lock('WRITE');

// Verrouillage en lecture
DB::table('users')->lock('READ');

// Déverrouillage
DB::table('users')->unlock();
```

`lock('WRITE')` empêche toute autre connexion de lire ou d'écrire sur la table le temps du verrou ; `lock('READ')` autorise les lectures concurrentes mais bloque les écritures. Pensez systématiquement à appeler `unlock()` une fois le traitement terminé — idéalement dans un bloc `finally`, pour garantir sa libération même en cas d'exception.

---

## Prochaines Étapes

Maintenant que vous maîtrisez le Query Builder :

1. **[Elegant ORM](orm.md)** → Travailler avec les modèles
2. **[Entités](entities.md)** → Définir la structure des tables
3. **[Migrations](migration.md)** → Gérer les modifications de schéma

---

**Query Builder compris !** 🚀 → [Elegant ORM](orm.md)