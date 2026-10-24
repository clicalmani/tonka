# Migrations

- [Migrations](#migrations)
- [Introduction](#introduction)
- [Manifests XML](#manifests-xml)
  - [Structure d'un Manifest](#structure-d-39-un-manifest)
  - [Résolution des Dépendances](#resolution-des-dependances)
  - [Génération Automatique](#generation-automatique)
- [Commandes de Migration](#commandes-de-migration)
  - [migrate:fresh](#migrate-fresh)
  - [migrate:entity](#migrate-entity)
  - [db:clear](#db-clear)
- [Versionnement](#versionnement)
  - [Création d'une Version](#creation-d-39-une-version)
  - [Mise à Jour d'une Version](#mise-a-jour-d-39-une-version)
- [Alter Tables](#alter-tables)
  - [Définition d'un Alter](#definition-d-39-un-alter)
  - [Application d'un Alter](#application-d-39-un-alter)
  - [Handler Personnalisé](#handler-personnalise)
- [Rollback](#rollback)
  - [Retour à une Version Antérieure](#retour-a-une-version-anterieure)
  - [Suppression d'une Table](#suppression-d-39-une-table)
- [Exemple Complet](#exemple-complet)
- [Bonnes Pratiques](#bonnes-pratiques)
- [Prochaines Étapes](#prochaines-etapes)

---

## Introduction

Le système de migrations de Tonka repose sur un paradigme **déclaratif**, matérialisé par des **manifests XML**, plutôt que sur la succession de scripts impératifs qu'on trouve dans la plupart des frameworks PHP (Laravel, Doctrine Migrations, etc.).

Dans une migration PHP traditionnelle, l'historique de votre schéma est reconstruit en rejouant, dans l'ordre, une pile de fichiers horodatés — chacun décrivant une opération (`up()` / `down()`). Ce modèle impératif présente deux limites structurelles : (1) l'état final de la base n'est jamais décrit explicitement nulle part, il faut le déduire en simulant tout l'historique ; (2) la résolution des dépendances entre tables (clés étrangères, ordre de création) reste à la charge du développeur.

Tonka inverse cette logique : le **manifest** est un document XML qui décrit l'état *cible* du schéma — l'ensemble des entités, leurs relations et leurs éventuelles altérations — à un instant donné. Le moteur de migration se charge ensuite de traduire cette description déclarative en SQL exécutable, dans le bon ordre.

Ce système apporte :

| Capacité | Détail |
|---|---|
| ✅ **Versionnement complet** | Chaque état du schéma est capturé dans un fichier manifest nommé, indépendant des autres |
| ✅ **Résolution automatique des dépendances** | Un tri topologique sur le graphe `<dependences>` détermine l'ordre de création des tables |
| ✅ **Traçabilité** | Le manifest exécuté fait foi ; pas besoin de rejouer l'historique pour connaître l'état courant |
| ✅ **Rollback déterministe** | Revenir à une version = ré-appliquer un manifest antérieur, sans diff manuel |

---

## Manifests XML

### Structure d'un Manifest

Un manifest est un arbre XML dont la racine `<migration>` contient des déclarations d'`<entity>`, de `<seeder>` et, en présence de versionnement, des blocs `<updates>`.

```xml
<?xml version="1.0" encoding="UTF-8"?>
<migration>
  <!-- Entité simple : aucune dépendance, table créée directement -->
  <entity model="App\Models\User">
    Database\Entities\UserEntity
  </entity>

  <!-- Entité avec dépendances : Post référence User (ex. clé étrangère user_id) -->
  <entity model="App\Models\Post">
    Database\Entities\PostEntity
    <dependences>
      <entity model="App\Models\User">
        Database\Entities\UserEntity
      </entity>
    </dependences>
  </entity>

  <!-- Entité avec alter : SQL encodé en base64, appliqué après la création -->
  <entity model="App\Models\Profile">
    Database\Entities\ProfileEntity
    <alter>QkVHSU4gQUxURVIgVEFCTEUg...==</alter>
  </entity>

  <!-- Seeders : exécutés après toutes les créations/altérations de tables -->
  <seeder name="\Database\Seeders\UserSeeder"></seeder>
  <seeder name="\Database\Seeders\PostSeeder"></seeder>
</migration>
```

**Anatomie des nœuds :**

- `<entity model="...">` — attribut `model` pointe vers la classe Eloquent-like ; le corps textuel du nœud pointe vers la classe `Entity` associée (`database/entities/`), qui porte la définition physique des colonnes.
- `<dependences>` — liste d'autres `<entity>` qui **doivent** exister avant celle-ci. C'est ce nœud qui alimente le graphe de dépendances (voir section suivante).
- `<alter>` — contenu **encodé en base64** (et non en clair) pour éviter tout conflit d'échappement XML avec du SQL contenant des guillemets, des `<`/`>` ou des caractères spéciaux.
- `<seeder name="...">` — référence FQCN d'une classe de seeding, exécutée après la phase DDL (création/altération), jamais avant.

> ⚠️ **Point d'attention** : l'ordre d'apparition des `<entity>` dans le fichier XML **n'a aucune incidence** sur l'ordre d'exécution réel. Seul le graphe de dépendances déclaré via `<dependences>` fait foi.

### Résolution des Dépendances

En interne, le moteur de migration construit un **graphe orienté acyclique (DAG)** à partir des relations `<dependences>`, puis effectue un **tri topologique** pour déterminer l'ordre de création physique des tables.

Concrètement, pour le manifest ci-dessus :

```
User ──▶ Post ──▶ Profile
```

`User` est créée en premier (aucune dépendance), puis `Post` (dépend de `User`), et enfin les `<alter>` sont appliqués une fois toutes les tables physiquement en place — un `ALTER TABLE` ne peut viser qu'une table déjà existante.

Si le graphe contient un cycle (par exemple `A` dépend de `B` qui dépend de `A`), la génération continue mais en émettant un avertissement — Cela permet d'éviter de faire des appels cycliques à travers les propriétés personnalisées.

### Génération Automatique

Le manifest n'est en pratique presque jamais écrit à la main : il est **généré par introspection** de vos modèles.

```bash
php tonka migrate:fresh --manifest=manifest
```

Cette commande orchestre quatre étapes :

1. **Scan** — parcours de `app/Models` pour lister les classes de modèle éligibles.
2. **Résolution d'entité** — pour chaque modèle, récupération de l'`Entity` associée (convention de nommage ou attribut explicite selon votre configuration).
3. **Analyse des dépendances** — inspection des relations déclarées sur l'`Entity` (clés étrangères, attributs de type `#[Property]` référençant une autre table) pour construire automatiquement les nœuds `<dependences>`.
4. **Sérialisation** — écriture du fichier `database/manifests/manifest.xml`, prêt à être exécuté ou committé en contrôle de version.

Le fichier généré est un artefact **versionnable** : deux développeurs peuvent le diffuser via Git, et le manifest fait office de contrat explicite sur l'état attendu du schéma — un `git diff` sur `manifest.xml` documente littéralement l'évolution de votre base.

---

## Commandes de Migration

### migrate:fresh

Réinitialise complètement la base de données à partir d'un manifest : **drop** de toutes les tables connues, puis reconstruction intégrale dans l'ordre topologique.

```bash
php tonka migrate:fresh --manifest=manifest --seed --create-routines
```

**Options :**

| Option | Description |
|--------|-------------|
| `--manifest` | Nom du manifest à utiliser (sans l'extension `.xml`). Par défaut manifest. |
| `--seed` | Exécute les seeders déclarés, une fois le schéma DDL entièrement en place |
| `--create-routines` | Crée les routines associées (fonctions, procédures stockées, vues SQL) |
| `--dump` | Exécute puis génère un fichier `.sql` équivalent, pour audit ou exécution manuelle différée |

> 💥 **Destructif par nature.** `migrate:fresh` supprime les tables existantes avant de les recréer. À réserver aux environnements de développement/CI ; en production, préférez `migrate:entity --alter` pour des évolutions incrémentales.

### migrate:entity

Cible une entité unique plutôt que l'intégralité du manifest — utile pour itérer rapidement sur une seule table sans toucher au reste du schéma.

```bash
php tonka migrate:entity User --alter --dump=dumpfile
```

**Options :**

| Option | Description |
|--------|-------------|
| `--alter` | Applique la méthode `alter()` de l'entité plutôt qu'un `CREATE TABLE` — la table doit déjà exister |
| `--dump` | Génère un fichier SQL |
| `--manifest` | Restreint la résolution de dépendances au manifest spécifié |

Sans `--alter`, la commande tente un `CREATE TABLE` classique ; avec `--alter`, elle délègue à la logique décrite dans la section [Alter Tables](#alter-tables).

### db:clear

Supprime l'ensemble des tables décrites par un manifest, sans les recréer — l'inverse d'un `migrate:fresh` sans la phase de reconstruction.

```bash
php tonka db:clear manifest
```

Utile pour repartir d'une base strictement vide avant une phase de debug, ou pour des tests d'intégration qui gèrent eux-mêmes le seeding.

---

## Versionnement

### Création d'une Version

Chaque manifest est un instantané nommé et indépendant. Créer une version, c'est simplement migrer contre un fichier manifest portant ce nom :

```bash
php tonka migrate:fresh --manifest=v1.0.0
```

**Exemple de manifest versionné :**

```xml
<!-- database/manifests/v1.0.0.xml -->
<migration version="1.0.0">
  <entity model="App\Models\User">Database\Entities\UserEntity</entity>
  <entity model="App\Models\Post">Database\Entities\PostEntity</entity>
</migration>
```

L'attribut `version` sur la racine `<migration>` est purement documentaire côté schéma XML, mais il est exploité par la CLI pour l'affichage et la journalisation (quel manifest a été appliqué, et quand).

### Mise à Jour d'une Version

Une nouvelle version peut introduire trois types de changements, tous exprimables dans le même fichier :

```xml
<!-- database/manifests/v1.1.0.xml -->
<migration version="1.1.0">
  <!-- 1. Nouvelle entité : sera CREATE TABLE -->
  <entity model="App\Models\Comment">
    Database\Entities\CommentEntity
  </entity>
  
  <!-- 2. Alter d'une entité existante : sera ALTER TABLE, pas de perte de données -->
  <entity model="App\Models\User">
    Database\Entities\UserEntity
    <alter>ALTER TABLE users ADD COLUMN phone VARCHAR(20)</alter>
  </entity>
  
  <!-- 3. Mise à jour des données : ré-exécution ciblée sans toucher au DDL -->
  <updates>
    <entity model="App\Models\Post">
      Database\Entities\PostEntity
    </entity>
  </updates>
</migration>
```

Le bloc `<updates>` est sémantiquement distinct d'un `<entity>` racine : il ne déclenche ni `CREATE` ni `ALTER`, mais signale au moteur qu'une opération de mise à jour de données (typiquement un seeder de correction, ou une transformation ponctuelle) doit être rejouée pour cette entité dans le cadre de cette version.

> 🔍 **Sous le capot** : contrairement à un `CREATE TABLE`, un `ALTER TABLE` répété n'est pas idempotent par défaut. Le moteur Tonka journalise les alters déjà appliqués (par hash du contenu base64) pour éviter une double exécution lors d'un `migrate:fresh` répété sur la même version.

---

## Alter Tables

### Définition d'un Alter

Une entité déclare sa capacité d'altération via l'attribut `#[AlterOption]` sur une méthode `alter()`, qui exécute le `alter` :

```php
// database/entities/UserEntity.php
use Clicalmani\Database\Factory\AlterOption;

#[AlterOption]
class UserEntity extends Entity
{
    // Méthode alter()
    public function alter(AlterOption $alter): string
    {
        $alter->addColumn('phone')->varchar(20)->after('email')->end(); // Délimiteur (,)
        $alter->addColumn('address')->varchar(255)->after('phone');
        return $alter->render();
    }
}
```

Le fragment retourné est automatiquement préfixé par `ALTER TABLE <table> ` par le moteur — vous ne déclarez que la clause d'altération elle-même (`ADD COLUMN`, `MODIFY`, `DROP COLUMN`, `RENAME COLUMN`, etc.), ce qui rend la méthode indépendante du nom physique de la table.

### Application d'un Alter

```bash
php tonka migrate:entity User --alter
```

Cette commande localise l'`Entity` de `User`, invoque `alter()`, préfixe le SQL généré, puis l'exécute (ou le dump, si `--dump` est fourni) contre la table `users` existante.

### Handler Personnalisé

Lorsque l'altération de schéma doit s'accompagner d'une opération sur les **données** (backfill, migration de valeurs, normalisation), l'attribut `#[AlterOption]` accepte un paramètre `handler` qui redirige l'exécution vers une méthode dédiée, recevant le SQL déjà résolu :

```php
#[AlterOption(handler: 'customAlter')]
class UserEntity extends Entity
{
    public function customAlter(string $sql): void
    {
        // Le SQL de l'ALTER est déjà prêt : à vous de l'exécuter
        DB::statement($sql);
        
        // Opérations supplémentaires : backfill après coup
        DB::table('users')->update(['phone' => '0000000000']);
    }
}
```

C'est le point d'extension à privilégier chaque fois qu'un changement de schéma implique une **contrainte de cohérence des données** — par exemple, ajouter une colonne `NOT NULL` sans valeur par défaut exige de peupler la colonne *avant* de resserrer la contrainte, ce qu'un simple `alter()` déclaratif ne peut pas exprimer.

---

## Rollback

### Retour à une Version Antérieure

Le rollback n'est pas un mécanisme de "undo" au sens strict (pas de `down()` symétrique à rejouer) : il consiste à **ré-appliquer un manifest antérieur**, qui redevient la source de vérité du schéma.

```bash
php tonka migrate:fresh --manifest=v1.0.0
```

> ⚠️ Comme `migrate:fresh` reconstruit intégralement les tables, un rollback vers `v1.0.0` **supprime les données** introduites par les colonnes ajoutées en `v1.1.0` (ex. `phone`). Pour un rollback non destructif, préférez rédiger un `<alter>` inverse (`DROP COLUMN phone`) et l'appliquer via `migrate:entity --alter`, plutôt qu'un `migrate:fresh`.

### Suppression d'une Table

```bash
php tonka db:drop User
```

Supprime uniquement la table associée à l'entité `User`, sans toucher au reste du schéma — utile pour retirer une entité devenue obsolète sans repasser par un manifest complet.

### Suppression en Masse

```bash
php tonka db:clear manifest
```

Équivalent à un `db:drop` appliqué séquentiellement à toutes les entités du manifest, dans l'ordre **inverse** du tri topologique (les tables dépendantes sont supprimées avant celles dont elles dépendent, pour respecter les contraintes de clé étrangère).

---

## Exemple Complet

### 1. Création du Modèle/Entité

```bash
php tonka make:entity User
```

### 2. Définition de l'Entité

```php
// database/entities/UserEntity.php
#[Index(
    name: 'users_email_unique',
    key: 'email',
    unique: true
)]
class UserEntity extends Entity
{
    #[Property(length: 10, unsigned: true, autoIncrement: true), PrimaryKey]
    public Integer $id;

    #[Property(length: 255)]
    public VarChar $name;

    #[Property(length: 191)]
    public VarChar $email;

    #[Property(length: 255)]
    public VarChar $password;

    #[Property(length: 50)]
    public VarChar|string $role = 'user';

    #[Property(length: 1)]
    public TinyInt|true $is_active = true;

    #[Property]
    public DateTime $created_at;

    #[Property]
    public DateTime $updated_at;

    #[Property]
    public ?DateTime $deleted_at;
}
```

Quelques détails d'implémentation qui valent la peine d'être soulignés :

- **`email` en `VARCHAR(191)`, pas `255`** : c'est une contrainte historique de MySQL/MariaDB sous encodage `utf8mb4` — un index unique sur une colonne `VARCHAR(255)` en `utf8mb4` dépasse la limite de 767 octets pour une clé InnoDB. `191` reste le plafond sûr le plus large.
- **`?DateTime $deleted_at`** : le type nullable (`?`) signale au générateur de colonne l'absence de contrainte `NOT NULL` — c'est le pattern idiomatique pour du *soft delete*.
- **`VarChar|string $role = 'user'`** : la valeur par défaut PHP (`'user'`) est reprise telle quelle comme `DEFAULT` SQL lors de la génération du `CREATE TABLE`.

### 3. Génération du Manifest

```bash
php tonka migrate:fresh --manifest=manifest
```

### 4. Migration

```bash
php tonka migrate:fresh --manifest=manifest --seed
```

### 5. Ajout d'une Colonne

```php
// Modifier l'entité
#[Property(length: 20)]
public ?VarChar $phone;

// Générer l'alter
#[AlterOption]
public function alter(AlterOption $alter): string
{
    return $alter->addColumn('phone')->varchar(20)->after('email')->render();
}
```

### 6. Application de l'Alter

```bash
php tonka migrate:entity User --alter
```

---

## Bonnes Pratiques

- **Committez vos manifests.** Un `manifest.xml` versionné dans Git documente l'état du schéma au même titre que le code — traitez-le comme un artefact de premier ordre, pas comme un fichier généré jetable.
- **Ne mélangez pas `--seed` et environnements sensibles.** Les seeders sont conçus pour peupler des données de développement/démo ; un `migrate:fresh --seed` en production écrasera silencieusement les données existantes.
- **Préférez `migrate:entity --alter` à `migrate:fresh` en production.** Le second est destructif par construction ; le premier permet des évolutions incrémentales, table par table.
- **Utilisez `--dump` avant tout `ALTER` risqué.** Générer le SQL sans l'exécuter permet une revue manuelle — particulièrement utile pour les altérations impliquant un backfill via `handler`.
- **Gardez vos `alter()` atomiques.** Un `alter()` qui ne retourne qu'une seule clause reste facile à auditer et à inverser ; préférez plusieurs entités/versions à un alter monolithique qui cumule plusieurs changements sans rapport.

---

## Prochaines Étapes

Maintenant que vous maîtrisez les migrations :

1. **[Seeders](seeding.md)** → Peupler la base de données
2. **[Elegant ORM](orm.md)** → Approfondir l'ORM
3. **[Base de Données](database.md)** → Revoir les concepts

---

**Migrations comprises !** 🚀 → [Seeders](seeding.md)