# Database

- [Introduction](#introduction)
- [Bases de Données Supportées](#bases-de-donnees-supportees)
- [Configuration](#configuration)
    - [Configuration SQLite](#configuration-sqlite)
    - [Configuration Microsoft SQL Server](#configuration-microsoft-sql-server)
    - [Configuration via URLs](#configuration-via-urls)
- [Exécution de Requêtes SQL](#execution-de-requetes-sql)
    - [Exécuter une Requête de Sélection](#executer-une-requete-de-selection)
        - [Avec le Query Builder](#avec-le-query-builder)
        - [Avec du SQL Brut](#avec-du-sql-brut)
        - [Récupérer une Seule Ligne](#recuperer-une-seule-ligne)
        - [Utiliser des Bindings Nommés](#utiliser-des-bindings-nommes)
            - [Bindings Nommés avec le Query Builder](#bindings-nommes-avec-le-query-builder)
            - [Bindings Nommés avec du SQL Brut](#bindings-nommes-avec-du-sql-brut)
    - [Exécuter une Instruction Générale](#executer-une-instruction-generale)
    - [Exécuter une Instruction Non Préparée](#executer-une-instruction-non-preparee)
- [Gestion des Transactions](#gestion-des-transactions)
- [Utilisation de Plusieurs Connexions à la Base de Données](#utilisation-de-plusieurs-connexions-a-la-base-de-donnees)
    - [Configurer Plusieurs Connexions](#configurer-plusieurs-connexions)
    - [Utiliser Plusieurs Connexions](#utiliser-plusieurs-connexions)
        - [Avec le Query Builder](#avec-le-query-builder-connexions)
        - [Avec l'ORM Elegant de Tonka](#avec-lorm-elegant-de-tonka)
    - [Changer de Connexion à l'Exécution](#changer-de-connexion-a-lexecution)
- [Écouter les Événements de Requête](#ecouter-les-evenements-de-requete)
    - [Enregistrer un Listener de Requête](#enregistrer-un-listener-de-requete)
    - [Exemples d'Utilisation](#exemples-dutilisation-listener)
- [Transactions](#transactions)
    - [Utiliser les Transactions](#utiliser-les-transactions)
    - [Utiliser la Méthode `transaction`](#utiliser-la-methode-transaction)
    - [Points de Sauvegarde (Save Points)](#points-de-sauvegarde-save-points)
    - [Définir le Niveau d'Isolation](#definir-le-niveau-disolation)
    - [Gérer les Interblocages (Deadlocks)](#gerer-les-interblocages-deadlocks)

## Introduction

**Tonka** Database est une solution de base de données puissante et évolutive, conçue pour traiter de gros volumes de données avec fluidité. Elle offre des fonctionnalités robustes de gestion des données, de haute disponibilité et de sécurité, ce qui en fait un choix pertinent pour les applications d'entreprise.

Presque toute application web moderne interagit avec une base de données. **Tonka** simplifie considérablement cette interaction, quelle que soit la base de données utilisée, via trois approches complémentaires : le SQL brut, un query builder fluide, et l'**ORM Elegant**. Tonka offre actuellement un support de premier niveau pour cinq bases de données :

## Bases de Données Supportées

1. **MySQL** : système de gestion de base de données relationnelle open-source, largement répandu.
2. **PostgreSQL** : base de données relationnelle open-source avancée, de niveau entreprise.
3. **SQLite** : moteur SQL autonome, embarqué et fiable, dans le domaine public.
4. **SQL Server** : système de gestion de base de données relationnelle développé par Microsoft.
5. **Oracle** : système de gestion de base de données multi-modèle, produit et commercialisé par Oracle Corporation.

Chacune de ces bases de données est entièrement supportée et s'intègre facilement à Tonka Database, offrant flexibilité et scalabilité à vos applications.

## Configuration

Pour configurer Tonka Database dans votre application, procédez comme suit :

1. **Installer le driver de base de données** : assurez-vous que le driver correspondant à la base de données choisie est installé — généralement via votre gestionnaire de paquets.

2. **Variables d'environnement** : gérez vos identifiants de connexion de façon sécurisée via des variables d'environnement. Créez un fichier `.env` à la racine de votre projet et ajoutez les variables nécessaires :
    ```env
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=your_database
    DB_USERNAME=your_username
    DB_PASSWORD=your_password
    ```

### Configuration SQLite

Avec SQLite, la configuration diffère légèrement : vous devez préciser le chemin vers votre fichier de base de données SQLite dans le fichier `.env` :

```env
DB_CONNECTION=sqlite
DB_DATABASE=/chemin/absolu/vers/votre_base.sqlite
```

Vous pouvez également utiliser un chemin relatif :

```env
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
```

Assurez-vous que le répertoire contenant le fichier SQLite dispose des droits d'écriture pour votre application.

Par défaut, les contraintes de clé étrangère sont activées pour les connexions SQLite. Pour les désactiver, définissez la variable d'environnement `DB_FOREIGN_KEYS` à `false` dans votre `.env` :

```env
DB_FOREIGN_KEYS=false
```

### Configuration Microsoft SQL Server

Pour configurer Tonka Database avec Microsoft SQL Server, procédez comme suit :

1. **Installer le driver SQL Server** : assurez-vous que le driver PDO SQL Server est installé. Consultez la documentation officielle : [Microsoft SQL Server PDO Driver](https://learn.microsoft.com/fr-fr/sql/connect/php/pdo-query?view=sql-server-ver16).

2. **Variables d'environnement** : ajoutez les variables nécessaires à votre fichier `.env` :
    ```env
    DB_CONNECTION=sqlsrv
    DB_HOST=127.0.0.1
    DB_PORT=1433
    DB_DATABASE=your_database
    DB_USERNAME=your_username
    DB_PASSWORD=your_password
    ```

3. **Configuration additionnelle** : pour préciser des options supplémentaires (jeu de caractères, collation...), ajoutez-les dans votre fichier de configuration :
    ```php
    'sqlsrv' => [
        'driver' => 'sqlsrv',
        'host' => env('DB_HOST', 'localhost'),
        'port' => env('DB_PORT', '1433'),
        'database' => env('DB_DATABASE', 'forge'),
        'username' => env('DB_USERNAME', 'forge'),
        'password' => env('DB_PASSWORD', ''),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_520_ci',
        'prefix' => ''
    ],
    ```

Assurez-vous que votre instance SQL Server est configurée pour accepter les connexions entrantes de votre application, et que les règles de pare-feu nécessaires sont en place.

### Configuration via URLs

**Tonka** Database prend également en charge la configuration de vos connexions à la base de données à l'aide d'URL. Cela permet de simplifier le processus de configuration en encapsulant l'ensemble des détails de connexion dans une seule chaîne de caractères. Pour utiliser une URL dans la configuration de votre base de données, définissez la variable d'environnement `DB_URL` dans votre fichier `.env` :

```env
DB_URL=mysql://nom_utilisateur:mot_de_passe@127.0.0.1:3306/votre_base_de_donnees

```

Voici des exemples pour les autres bases de données prises en charge :

**PostgreSQL :**

```env
DB_URL=pgsql://nom_utilisateur:mot_de_passe@127.0.0.1:5432/votre_base_de_donnees

```

**SQLite :**

```env
DB_URL=sqlite:///chemin/absolu/vers/votre_base_de_donnees.sqlite

```

**SQL Server :**

```env
DB_URL=sqlsrv://nom_utilisateur:mot_de_passe@127.0.0.1:1433/votre_base_de_donnees

```

**Oracle :**

```env
DB_URL=oracle://nom_utilisateur:mot_de_passe@127.0.0.1:1521/votre_base_de_donnees

```

```php
// config/database.php
'connections' => [
    'pgsql' => [
        'url' => env('DB_URL')
    ]
]
```

Assurez-vous que l'URL est correctement formatée et inclut l'ensemble des détails de connexion nécessaires. Cette méthode est particulièrement utile pour gérer différents environnements (développement, recette, production) en modifiant simplement la valeur de `DB_URL`.

---

## Exécution de Requêtes SQL

Une fois votre connexion configurée, vous pouvez exécuter des requêtes via la façade `DB`. Celle-ci expose une méthode pour chaque type d'opération : `select`, `update`, `insert`, `delete`, et `statement`.

### Exécuter une Requête de Sélection

#### Avec le Query Builder

Le [query builder](query_builder.md) offre une interface fluide et pratique pour construire vos requêtes SQL :

```php
$users = DB::table('users')->get('name, email');
```

Ce qui génère et exécute la requête SQL suivante :

```sql
SELECT name, email FROM users;
```

Cette méthode retourne un tableau de résultats, chaque résultat étant une instance de l'objet PHP [stdClass](https://www.php.net/manual/fr/class.stdclass.php).

#### Avec du SQL Brut

Pour les cas où le query builder ne suffit pas (requête complexe, fonctionnalité spécifique au moteur), vous pouvez exécuter du SQL brut directement via `DB::select()` :

```php
$users = DB::select('SELECT name, email FROM users WHERE active = ?', [1]);
```

#### Récupérer une Seule Ligne

Pour ne récupérer qu'une seule ligne, utilisez la méthode `first()` avec le query builder :

```php
$user = DB::table('users')->get('name, email')->first();
```

Ou avec du SQL brut :

```php
$user = DB::selectOne('SELECT name, email FROM users LIMIT 1');
```


### Vérifier l'Existence d'une Ligne

Pour vérifier si au moins une ligne correspond aux critères :

```php
$exists = DB::table('users')->where('email = ?', ['alice@x.com'])->exists();
// → true ou false
```
---

## Requêtes SELECT

### Sélection Simple

```php
// Toutes les colonnes
$users = DB::table('users')->get();

// Colonnes spécifiques 
$users = DB::table('users')->get('name, email');

// Alias de colonnes
$users = DB::table('users')->get('name AS full_name, email');
```

### Distinct

Pour éliminer les doublons :

```php
$cities = DB::table('users')->distinct()->get('city');
```

Ce qui génère :

```sql
SELECT DISTINCT city FROM `users`;
```

### Jointures

Tonka prend en charge les jointures `INNER`, `LEFT`, `RIGHT`, `FULL` et `CROSS`, chacune mappée sur une méthode dédiée :

```php
$orders = DB::table('orders')
    ->joinInner('users', 'orders.user_id', 'users.id')
    ->get('orders.id, users.name, orders.total');
```

Ce qui génère :

```sql
SELECT orders.id, users.name, orders.total
FROM `orders`
INNER JOIN `users` ON orders.user_id = users.id;
```

Les jointures multiples s'enchaînent dans l'ordre de leur composition :

```php
$orders = DB::table('orders')
    ->joinInner('users', 'orders.user_id', 'users.id')
    ->joinLeft('products', 'orders.product_id', 'products.id')
    ->get();
```

### Agrégats

| Fonction | Description | Exemple |
| --- | --- | --- |
| `count()` | Compter le nombre d'occurrences | `DB::table('users')->count()` |
| `sum()` | Calculer la somme | `DB::table('orders')->sum('quantity')` |
| `avg()` | Calculer la moyenne | `DB::table('products')->avg('stock')` |
| `min()`, `max()` | Minimum et maximum | `DB::table('orders')->min('quantity')`, `DB::table('orders')->max('quantity')` | 

Vous pouvez également utiliser les fonctions d'agrégation directement dans la requête  :

```php
$stats = DB::table('orders')->get('COUNT(*) AS total, SUM(total) AS revenue')->first();

echo $stats->total;
echo $stats->revenue;
```

### Group By / Having

`groupBy()` constitue les partitions, `having()` les filtre après agrégation :

```php
$stats = DB::table('orders')
    ->get('user_id, COUNT(*) AS order_count, SUM(total) AS revenue')
    ->groupBy('user_id')
    ->having('order_count > 5')
    ->orderBy('revenue', 'DESC')
    ->get();
```

Ce qui génère :

```sql
SELECT user_id, COUNT(*) AS order_count, SUM(total) AS revenue
FROM `orders`
GROUP BY user_id
HAVING order_count > 5
ORDER BY revenue DESC;
```

> **Note** : contrairement à MySQL en mode strict, Tonka autorise `HAVING` **sans** `GROUP BY` — un cas utile pour filtrer sur un agrégat calculé à l'échelle de la table entière.

### Order By

```php
// Tri ascendant
$users = DB::table('users')->orderBy('name', 'ASC')->get();

// Tri descendant
$users = DB::table('users')->orderBy('created_at', 'DESC')->get();

// Tri multi-colonnes : le i-ème sens s'applique à la i-ème colonne
$users = DB::table('users')->orderBy(['status', 'created_at'], ['ASC',  'DESC'])->get();
```

### Limites et Pagination

`limit(count)` borne la cardinalité du jeu retourné :

```php
// 10 premiers résultats
$users = DB::table('users')->limit(10)->get();

// Ignorer 20 lignes, en récupérer 10 (pagination par décalage)
$users = DB::table('users')->skip(20)->limit(10)->get();
```

Ce qui émet :

```sql
SELECT * FROM `users` LIMIT 20, 10;
```

> **Note de performance** : la pagination par décalage (`skip`) voit son coût croître avec la position, car le moteur scanne et jette les lignes sautées. Au-delà de quelques milliers d'offset, préférez la pagination par curseur (`WHERE id > :last ORDER BY id LIMIT :n`).

---


## Requêtes INSERT

La façade met à disposition un ensemble de méthodes d'insertion selon la sémantique de retour attendue : `insert()` pour un envoi nu, `getId()` / `getIds()` / `getIdOrFail()` lorsque l'auto-incrément est requis en sortie, et le modificateur `ignore()` pour tolérer les collisions.

### Insertion Simple

```php
DB::table('users')->insert([
    'name'  => 'John Doe',
    'email' => 'john.doe@example.com',
])->query();
```

Ce qui exécute :

```sql
INSERT INTO `users` (`name`, `email`) VALUES (:name_0, :email_0);
```

Les valeurs transitent exclusivement par des bindings nommés — jamais concaténées dans le texte SQL.

### Insertion Multiple

Pour insérer plusieurs lignes en une seule instruction, passez un tableau de tableaux associatifs :

```php
DB::table('users')->insert([
    ['name' => 'Alice', 'email' => 'alice@example.com'],
    ['name' => 'Bob',   'email' => 'bob@example.com'],
    ['name' => 'Carol', 'email' => 'carol@example.com'],
])->query();
```

Ce qui émet un unique `INSERT` multi-lignes :

```sql
INSERT INTO `users` (`name`, `email`)
VALUES (:name_0, :email_0), (:name_1, :email_1), (:name_2, :email_2);
```

> **Performance** : un `INSERT` multi-lignes est **radicalement plus rapide** que N insertions unitaires — un seul aller-retour réseau et une seule phase de validation/commit au lieu de N. À privilégier systématiquement pour tout chargement en masse.

### Récupérer l'ID Généré

Sur une colonne auto-incrémentée, `getId()` renvoie l'identifiant généré par le dernier `INSERT` :

```php
$id = DB::table('users')->insert([
    'name'  => 'John Doe',
    'email' => 'john.doe@example.com',
])->getId();

echo $id; // 42
```

Sur une insertion multi-lignes, `getId()` ne retourne que le **premier** identifiant ; `getIds()` renvoie la séquence complète :

```php
$ids = DB::table('users')->insert([
    ['name' => 'Alice'],
    ['name' => 'Bob'],
    ['name' => 'Carol'],
])->getIds();

// $ids = [1, 2, 3]
```

Lorsque l'identifiant est une précondition d'exécution (chaîne de dépendances, relation à rattacher immédiatement), `getIdOrFail()` lève une exception au lieu de retourner `null` :

```php
try {
    $id = DB::table('users')->insert([...])->getIdOrFail();
} catch (\RuntimeException $e) {
    // Aucun ID généré : INSERT ignoré (ignore()), trigger, ou table sans AUTO_INCREMENT
}
```

### Insert IGNORE

`ignore()` préfixe l'instruction d'`INSERT IGNORE` pour absorber silencieusement les collisions sur contrainte unique ou primaire :

```php
DB::table('users')->insert([
    ['email' => 'alice@example.com', 'name' => 'Alice'],
])->ignore()->query();
```

Ce qui émet :

```sql
INSERT IGNORE INTO `users` (`email`, `name`) VALUES (:email_0, :name_0);
```

En cas de collision, la ligne est ignorée **sans lever d'erreur** — l'exécution se poursuit normalement.

---

## Requêtes UPDATE

### Mise à Jour Simple

`update()` consomme un tableau associatif `colonne => valeur`, la clause `where()` restreignant le périmètre de l'écriture. Les expressions natives se transmettent via l'objet `Raw` :

```php
DB::table('users')
    ->where('id = ?', [42])
    ->update(['name' => 'John Smith', 'updated_at' => new Raw('NOW()')]);
```

Ce qui exécute :

```sql
UPDATE `users` SET `name` = :name, `updated_at` = NOW() WHERE TRUE AND id = 42;
```

La méthode retourne le nombre de lignes affectées, accessible via `getAffectedRows()` :

```php
$affected = DB::table('users')->where('id = ?', [42])->update([...]);
echo $affected; // ex: 1
```

### Incrémenter / Décrémenter

Pour faire varier une colonne numérique, `increment()` et `decrement()` génèrent une mise à jour arithmétique évaluée par le moteur, sans phase de lecture préalable côté applicatif :

```php
DB::table('user_stats')
    ->where('user_id = ?', [1])
    ->increment('visit_count', 1);

DB::table('products')
    ->where('id = ?', [42])
    ->decrement('stock', 5);
```

Ce qui émet :

```sql
UPDATE `user_stats` SET `visit_count` = `visit_count` + 1 WHERE user_id = 1;
UPDATE `products` SET `stock` = `stock` - 5 WHERE id = 42;
```

Ces helpers sont **atomiques** : la lecture et l'écriture de la valeur s'effectuent dans la même instruction SQL, ce qui élimine toute condition de course (read-modify-write) entre transactions concurrentes.

---

## Requêtes DELETE

### Suppression Simple

`delete()` supprime les lignes satisfaisant la clause `where()` :

```php
DB::table('users')
    ->where('id = ?', [42])
    ->delete();
```

Ce qui exécute :

```sql
DELETE FROM `users` WHERE TRUE AND id = 42;
```

Comme pour `update()`, un `delete()` sans `where()` supprime toutes les lignes. Utilisez-le avec précaution.

Pour limiter le nombre de lignes supprimées :

```php
DB::table('logs')
    ->where('created_at < ?', ['2024-01-01'])
    ->orderBy('created_at', 'ASC')
    ->limit(0, 1000)
    ->delete();
```

### Truncate

`truncate()` vide la table intégralement et réinitialise son compteur `AUTO_INCREMENT` :

```php
DB::table('logs')->truncate();
```

Ce qui exécute :

```sql
TRUNCATE TABLE `logs`;
```

> **⚠️ Attention** :
> - `TRUNCATE` est une opération **DDL** : elle provoque un commit implicite et ne peut donc être **annulée par rollback**.
> - Elle échoue si la table est référencée par une clé étrangère (sauf si `FOREIGN_KEY_CHECKS=0`).
> - Elle ne déclenche **pas** les triggers `ON DELETE`.
> - Pour vider sans réinitialiser `AUTO_INCREMENT`, utilisez `DELETE`.

---

## Upsert (Insert ou Update)

L'upsert (contraction de **update + insert**) permet d'insérer une ligne, ou de la mettre à jour si une collision survient sur une clé primaire ou unique. C'est une opération atomique native de `MySQL` (`ON DUPLICATE KEY UPDATE`), bien plus performante qu'un `SELECT` suivi d'un `INSERT` ou `UPDATE`.

### Syntaxe de Base

`onDuplicateKeyUpdate()` reçoit la liste des colonnes à réécrire en cas de collision ; chacune reçoit la valeur nouvellement fournie :

```php
DB::table('products')->insert([
    ['sku' => 'ABC-123', 'title' => 'Clavier', 'price' => 29.99],
])->onDuplicateKeyUpdate(['title', 'price'])->query();
```

Ce qui émet :

```sql
INSERT INTO `products` (`sku`, `title`, `price`)
VALUES (:sku_0, :title_0, :price_0)
ON DUPLICATE KEY UPDATE
    `title` = VALUES(`title`),
    `price` = VALUES(`price`);
```

Si le `sku` existe déjà, `title` et `price` sont mis à jour ; sinon une nouvelle ligne est insérée.

### Expressions Brutes

Pour appliquer une **expression SQL** à une colonne en collision (compteur, fonction) plutôt qu'une simple recopie, passez la valeur sous forme de chaîne :

```php
DB::table('user_stats')->insert([
    ['user_id' => 1, 'visit_count' => 1, 'last_visit' => null],
])->onDuplicateKeyUpdate([
    'visit_count' => 'visit_count + 1',
    'last_visit'  => 'NOW()',
])->query();
```

Ce qui émet :

```sql
INSERT INTO `user_stats` (`user_id`, `visit_count`, `last_visit`)
VALUES (:user_id_0, :visit_count_0, :last_visit_0)
ON DUPLICATE KEY UPDATE
    `visit_count` = visit_count + 1,
    `last_visit` = NOW();
```

> **Sécurité** : les expressions transmises à `onDuplicateKeyUpdate()` sont injectées **telles quelles** dans le SQL. N'y placez jamais une entrée utilisateur brute — passez les valeurs par des bindings et réservez ce canal aux littéraux SQL de confiance.

### Alias MySQL 8.0.19+

Depuis MySQL 8.0.19, la fonction `VALUES(col)` est **dépréciée** au profit d'un **alias** apposé sur la clause `VALUES`, qui référence la ligne naissante par nom :

```php
DB::table('products')->insert([
    ['sku' => 'ABC-123', 'title' => 'Clavier', 'price' => 29.99],
])->onDuplicateKeyUpdate(['title', 'price'])
  ->usingAlias('new_data')
  ->query();
```

Ce qui émet (MySQL 8.0.19+) :

```sql
INSERT INTO `products` (`sku`, `title`, `price`)
VALUES (:sku_0, :title_0, :price_0) AS `new_data`
ON DUPLICATE KEY UPDATE
    `title` = `new_data`.`title`,
    `price` = `new_data`.`price`;
```

Tonka **négocie la syntaxe à la compilation** en sondant la version du serveur :

- **MySQL 8.0.19+** : génère `AS alias` et référence l'alias.
- **MySQL 5.7 / MariaDB** : ignore l'alias et retombe sur `VALUES(col)`.

`usingAlias()` est donc sûr à appeler de façon inconditionnelle : le même code reste valide quelle que soit la version cible.

### Différences avec REPLACE

`REPLACE INTO` est l'**alternative destructive** à l'upsert. Sa sémantique diffère radicalement :

| Aspect | `ON DUPLICATE KEY UPDATE` | `REPLACE INTO` |
|---|---|---|
| Sémantique | `UPDATE` in-place sur collision | `DELETE` puis `INSERT` sur collision |
| Colonnes non mentionnées | Conservées | Réinitialisées à leur valeur par défaut |
| Triggers `ON DELETE` | Non déclenchés | **Déclenchés** |
| `AUTO_INCREMENT` | Inchangé | Consomme un identifiant (trous de séquence) |
| Clés étrangères `ON DELETE CASCADE` | Non affectées | **Peuvent propager une suppression en cascade** |
| Recommandation | ✅ **À privilégier** | ⚠️ Cas très spécifiques uniquement |

**Contre-exemple** : `REPLACE` sur une ligne `id = 1` existante avec `email = 'old@x.com'` réinitialise l'email à `NULL` (valeur par défaut), la colonne n'ayant pas été projetée. Avec `upsert`, l'email est **préservé**.

---

## REPLACE

`REPLACE INTO` insère une ligne ou, en cas de collision sur une clé primaire/unique, **supprime la ligne existante avant réinsertion** — un cycle `DELETE` + `INSERT` évalué par le moteur :

```php
DB::table('users')->replace([
    ['id' => 1, 'name' => 'Alice', 'email' => 'alice@x.com'],
])->query();
```

Ce qui émet :

```sql
REPLACE INTO `users` (`id`, `name`, `email`)
VALUES (:id_0, :name_0, :email_0);
```

> **⚠️ Différences avec l'UPSERT** :
> - `REPLACE` est **destructeur** : toute colonne absente de la projection revient à sa valeur par défaut.
> - Les triggers `ON DELETE` se déclenchent, avec les effets de bord qui en découlent.
> - `AUTO_INCREMENT` progresse à chaque cycle, créant des trous de séquence.
> - Le modificateur `IGNORE` n'est **pas** supporté.
>
> Pour une sémantique **non destructive**, préférez l'[Upsert](#upsert-insert-ou-update).

---

#### Utiliser des Bindings Nommés

Les bindings (liaisons de paramètres) permettent de transmettre en toute sécurité des valeurs à vos requêtes SQL, empêchant les injections SQL.

##### Bindings Nommés avec le Query Builder

```php
$users = DB::table('users')
    ->where('name = ?', ['John'])
    ->get();
```

Ce qui génère et exécute la requête SQL suivante :

```sql
SELECT * FROM users WHERE name = ?;
```

Le placeholder `?` est remplacé de façon sécurisée par la valeur `'John'` au moment de l'exécution, sans jamais concaténer directement la valeur dans la chaîne SQL.

##### Bindings Nommés avec du SQL Brut

```php
$users = DB::select('SELECT * FROM users WHERE name = ?', ['John']);
```

Le principe est identique en SQL brut : ne jamais interpoler une valeur utilisateur directement dans la chaîne de requête, mais toujours passer par un placeholder lié.

### Exécuter une Instruction Générale

Pour toute instruction ne retournant pas de jeu de résultats (DDL, configuration de session...), utilisez `DB::statement()` :

```php
DB::statement('SET FOREIGN_KEY_CHECKS = 0');
```

### Exécuter une Instruction Non Préparée

Dans certains cas, vous devrez peut-être exécuter une requête non préparée (*unprepared statement*). Les requêtes non préparées sont exécutées directement sans liaison de paramètres (*parameter binding*) ni échappement, ce qui peut s'avérer utile pour certains types de requêtes. Cependant, soyez prudent lorsque vous utilisez des requêtes non préparées, car elles peuvent exposer votre application à des attaques par injection SQL si elles ne sont pas utilisées correctement.

Pour exécuter une requête non préparée, vous pouvez utiliser la méthode `DB::unprepared`. Voici un exemple :

```php
DB::unprepared('UPDATE users SET email = "unprepared@example.com" WHERE id = 1');

```

Cette méthode exécute la requête SQL fournie directement sur la base de données, sans aucune liaison de paramètres ni échappement.

N'utilisez les requêtes non préparées que lorsque cela est absolument nécessaire et veillez à ce que les données d'entrée soient sûres et assainies pour prévenir les vulnérabilités aux injections SQL.

---

## Gestion des Transactions

Tonka permet de regrouper plusieurs instructions au sein d'une même transaction, que vous pouvez valider ou annuler selon le résultat de vos opérations — voir la section [Transactions](#transactions) plus loin dans ce document pour un traitement complet (points de sauvegarde, niveaux d'isolation, gestion des interblocages).

Aperçu rapide avec la forme explicite :

```php
DB::transaction();

try {
    DB::table('users')->insert([['name' => 'Jane Doe', 'email' => 'jane.doe@example.com']]);
    DB::table('profiles')->insert([['user_id' => DB::lastInsertId(), 'bio' => 'Software Developer']]);
    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
    throw $e;
}
```

Ou avec la forme à callback, qui valide automatiquement la transaction si le callback retourne `true`, et l'annule s'il retourne `false` :

```php
DB::transaction(function () {
    try {
        DB::table('users')->insert(['name' => 'Jane Doe', 'email' => 'jane.doe@example.com']);
        DB::table('profiles')->insert(['user_id' => DB::lastInsertId(), 'bio' => 'Software Developer']);
        return true;
    } catch (\PDOException $e) {
        return false;
    }
});
```

---

## Utilisation de Plusieurs Connexions à la Base de Données

Tonka Database permet de configurer et d'utiliser plusieurs connexions au sein d'une même application — utile pour interagir avec des bases de données distinctes, ou pour séparer les opérations de lecture et d'écriture.

### Configurer Plusieurs Connexions

Les connexions supplémentaires se déclarent dans votre fichier de configuration de base de données :

```php
// config/database.php
return [

    'default' => env('DB_CONNECTION', 'mysql'),

    'connections' => [

        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'host' => env('DB_HOST_PGSQL', '127.0.0.1'),
            'port' => env('DB_PORT_PGSQL', '5432'),
            'database' => env('DB_DATABASE_PGSQL', 'forge'),
            'username' => env('DB_USERNAME_PGSQL', 'forge'),
            'password' => env('DB_PASSWORD_PGSQL', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],

    ],

];
```

Dans cet exemple, deux connexions sont définies : `mysql` et `pgsql`. Vous pouvez en ajouter autant que nécessaire, chacune avec sa propre configuration.

### Utiliser Plusieurs Connexions

Une fois plusieurs connexions configurées, précisez le nom de la connexion souhaitée au moment de l'utiliser.

#### Avec le Query Builder
<div id="avec-le-query-builder-connexions"></div>

```php
$users = DB::connection('pgsql')->table('users')->get();
```

Ici, la connexion `pgsql` est utilisée pour interroger la table `users`.

Vous pouvez accéder à l'instance PDO brute sous-jacente d'une connexion via la méthode `getPdo()` — utile pour des opérations bas niveau non couvertes par le query builder ou l'ORM Elegant :

```php
$pdo = DB::connection()->getPdo();
```

Ou pour une connexion spécifique :

```php
$pdo = DB::connection('pgsql')->getPdo();
```

Une fois l'instance PDO récupérée, vous pouvez l'utiliser directement :

```php
$statement = $pdo->prepare('SELECT * FROM users WHERE id = :id');
$statement->execute(['id' => 1]);
$user = $statement->fetch();
```

#### Avec l'ORM Elegant de Tonka
<div id="avec-lorm-elegant-de-tonka"></div>

Pour associer un modèle Elegant à une connexion spécifique, définissez la propriété `$connection` :

```php
class User extends Elegant
{
    protected $connection = 'pgsql';
}
```

Désormais, toutes les requêtes du modèle `User` utiliseront la connexion `pgsql`.

### Changer de Connexion à l'Exécution
<div id="changer-de-connexion-a-lexecution"></div>

Vous pouvez également changer de connexion dynamiquement, via `setConnection()` :

```php
$user = new User;
$user->setConnection('pgsql')->find(1);
```

Utile pour modifier la connexion d'une requête ou d'une opération ponctuelle, sans affecter le comportement par défaut du modèle.

---

## Écouter les Événements de Requête

Tonka permet de s'abonner à chaque requête exécutée par l'application, via `DB::listen()` — un point d'extension utile pour la journalisation, le profilage, ou la détection de requêtes lentes.

### Enregistrer un Listener de Requête

```php
use Clicalmani\Foundation\Support\Facades\DB;
use Clicalmani\Foundation\Support\Facades\Log;

DB::listen(function ($query) {
    // $query->sql contient la requête SQL exécutée
    // $query->bindings contient les valeurs liées à la requête
    // $query->profile contient le profil d'exécution (durée, etc.)
    Log::debug($query->sql, $query->bindings);
});
```

### Exemples d'Utilisation
<div id="exemples-dutilisation-listener"></div>

**Exemple 1 : journaliser les requêtes lentes**

```php
DB::listen(function ($query) {
    foreach ($query->profile as $record) {
        if ($record['Duration'] > 100) { // Seuil en millisecondes
            Log::warning('Requête lente détectée', [
                'sql' => $query->sql,
                'duration' => $record['Duration'] . 'ms'
            ]);
        }
    }
});
```

**Exemple 2 : Surveiller le Temps de Requête Cumulé**

```php
use Clicalmani\Foundation\Support\Facades\DB;
use Clicalmani\Foundation\Support\Facades\Log;

class QueryTimeTracker
{
    protected $totalQueryTime = 0;

    public function register()
    {
        DB::listen(function ($query) {
            foreach ($query->profile as $record) {
                $this->totalQueryTime += $record['Duration'];
            }
            Log::info('Temps de requête cumulé : ' . $this->totalQueryTime . 'ms');
        });
    }
}
```

Enregistrez ce listener dans un Service Provider, par exemple `AppServiceProvider` :

```php
use Clicalmani\Foundation\Providers\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $tracker = new QueryTimeTracker();
        $tracker->register();
    }
}
```

### Capturer des Requêtes Ponctuellement

Pour capturer toutes les requêtes d'un bloc de code :

```php
$queries = [];
DB::captureQueries(
    function () {
        DB::table('users')->insert([['name' => 'Alice']])->query();
        DB::table('users')->select()->where('id = 1')->query();
    },
    function (array $captured) use (&$queries) {
        $queries = $captured;
    }
);

// $queries contient les deux requêtes avec SQL, bindings et durées
```

### Journalisation des Requêtes

En complément des listeners, Tonka offre un log circulaire des requêtes exécutées.

#### Activer le Log

```php
DB::enableQueryLog(limit: 1000); // conserve les 1000 dernières requêtes
```

#### Consulter le Log

```php
$queries = DB::getQueryLog();

foreach ($queries as $entry) {
    printf("[%0.2fms] %s\n", $entry['time_ms'], $entry['sql']);
    // Bindings : $entry['bindings']
}
```

#### Vider le Log

```php
DB::flushQueryLog();
```

#### Désactiver le Log

```php
DB::disableQueryLog();
```

> Performance : le log est désactivé par défaut. Ne l'activez qu'en développement ou pour du debug ponctuel.

---

## Transactions

Tonka Database offre un support complet des transactions, permettant d'exécuter une série d'opérations comme une seule unité de travail : soit toutes les opérations réussissent, soit aucune n'est appliquée — garantissant l'intégrité de vos données.

### Utiliser les Transactions

Pour démarrer une transaction, utilisez `DB::beginTransaction()` ou `DB::transaction()`. Une fois la transaction démarrée, exécutez vos opérations ; validez-les avec `DB::commit()` en cas de succès, ou annulez-les avec `DB::rollBack()` en cas d'échec.

```php
DB::beginTransaction();

try {
    DB::table('users')->insert([
        ['name' => 'John Doe', 'email' => 'john.doe@example.com'],
        // Autres lignes...
    ]);
    DB::table('profiles')->insert([
        ['user_id' => DB::lastInsertId(), 'bio' => 'Software Developer'],
        // Autres lignes...
    ]);
    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
    throw $e;
}
```

Dans cet exemple, les deux insertions s'exécutent au sein d'une même transaction. En cas d'échec de l'une d'elles, la transaction est annulée dans son intégralité et aucune modification n'est appliquée à la base de données.

### Utiliser la Méthode `transaction`
<div id="utiliser-la-methode-transaction"></div>

`beginTransaction()` comme `transaction()` acceptent une fonction de rappel (callback). La transaction est annulée si le callback retourne une valeur *falsy*, ou si une exception est levée pendant son exécution.

```php
DB::transaction(function () {
    DB::table('users')->insert(['name' => 'Jane Doe', 'email' => 'jane.doe@example.com']);
    DB::table('profiles')->insert(['user_id' => DB::lastInsertId(), 'bio' => 'Software Developer']);
    return true;
});
```

Cette forme est généralement préférable à l'écriture explicite `beginTransaction()`/`commit()`/`rollBack()` : la gestion des exceptions et l'annulation en cas d'échec sont prises en charge automatiquement, réduisant le risque d'oublier un `rollBack()` dans un chemin d'erreur.

### Points de Sauvegarde (Save Points)
<div id="points-de-sauvegarde-save-points"></div>

Un point de sauvegarde permet de créer un point intermédiaire au sein d'une transaction, vers lequel revenir sans annuler la transaction dans son intégralité — utile pour isoler une portion de code au sein d'une transaction plus large.

```php
// 3 Niveaux on rollback le milieu
DB::transaction(function () use ($db) {
    User::create(['name' => 'A', 'email' => 'a@example.com', 'age' => 20, 'active' => 1]);

    try {
        DB::transaction(function () use ($db) {
            User::create(['name' => 'B', 'email' => 'b@example.com', 'age' => 21, 'active' => 1]);

            DB::transaction(function () {
                User::create(['name' => 'C', 'email' => 'c@example.com', 'age' => 22, 'active' => 1]);
                throw new \RuntimeException('C boom');
            });
        });
    } catch (\RuntimeException $e) {
        // Swallow
    }

    User::create(['name' => 'D', 'email' => 'd@example.com', 'age' => 23, 'active' => 1]);
});
```

Lorsque plusieurs transactions sont imbriquées, Tonka crée des points de sauvegardes automatiques. En cas d'échec, seulles les instances réussies reçoivent le commit automatiquement.

Dans cet exemple, les utilisateurs A, B et D sont crées tandisque C échoue à cause de l'exception levée. Cette exception entraine un rollback automatique.

### Définir le Niveau d'Isolation
<div id="definir-le-niveau-disolation"></div>
Le niveau d'isolation d'une transaction détermine la visibilité des modifications effectuées par d'autres transactions concurrentes, et permet de prévenir certains phénomènes indésirables : lectures sales (*dirty reads*), lectures non répétables, lectures fantômes.

L'isolation se configure via deux **enums** (`IsolationLevel` et `IsolationScope`) :

```php
use Clicalmani\Database\Enums\IsolationLevel;
use Clicalmani\Database\Enums\IsolationScope;

// Prochaine transaction uniquement
DB::isolateTransaction(IsolationLevel::SERIALIZABLE);

DB::beginTransaction();
// ... opérations en SERIALIZABLE ...
DB::commit();

// Changement persistant pour la session
DB::isolateTransaction(
    IsolationLevel::READ_COMMITTED,
    IsolationScope::SESSION
);
```

Niveaux d'isolation disponibles (enum `IsolationLevel`) :

| Niveau | Dirty reads | Non-repeatable reads | Phantom reads |
|---|---|---|---|
| `READ_UNCOMMITTED` | ✅ Autorisées | ✅ Autorisées | ✅ Autorisées |
| `READ_COMMITTED` | ❌ | ✅ Autorisées | ✅ Autorisées |
| `REPEATABLE_READ` | ❌ | ❌ | ✅ Autorisées |
| `SERIALIZABLE` | ❌ | ❌ | ❌ |

Portées disponibles (enum `IsolationScope`) :

| Valeur | Effet |
|---|---|
| `IsolationScope::NEXT_TRANSACTION` | Portée sur la **prochaine** transaction (défaut) |
| `IsolationScope::SESSION` | Portée **persistante** pour la session courante |
| `IsolationScope::GLOBAL` | Portée **globale** au serveur (à manier avec précaution) |

> **⚠️ Important** : une isolation **sans** `GLOBAL` ou `SESSION` ne porte que sur la prochaine transaction. De plus, il doit être appelé **avant** `beginTransaction()`/`transaction()`, sinon une `RuntimeException` est levée.

### Gérer les Interblocages (Deadlocks)
<div id="gerer-les-interblocages-deadlocks"></div>

Un interblocage (*deadlock*) survient lorsque deux transactions ou plus attendent mutuellement la libération d'un verrou détenu par l'autre, aboutissant à une impasse. Utilisez `DB::deadlock()` pour envelopper une opération sensible à ce risque :

```php
DB::deadlock(function () {
    DB::table('users')->insert(['name' => 'John Doe', 'email' => 'john.doe@example.com']);
    DB::table('profiles')->insert(['user_id' => DB::lastInsertId(), 'bio' => 'Software Developer']);
});
```

### Helper `transactionWith()`

Pour combiner isolation et transaction :

```php
$result = DB::transactionWith(IsolationLevel::SERIALIZABLE, function () {
    // ... opérations critiques ...
    return $value;
});
```

Le niveau est appliqué, la transaction démarrée, le callback exécuté. Commit automatique en cas de succès, rollback en cas d'exception.

---

## Prochaines Étapes

Acquises les bases de la couche données, prolongez vers :

1. **[Les entités](entities.md)** → la modélisation du domaine.
2. **[Elegant ORM](helpers.md)** → le mapping objet-relationnel complet.
3. **[Migration](migration.md)** → le versionnement du schéma.

---

**Couche base de données maîtrisée.** 🚀 Poursuivez vers [Les entités](entities.md).