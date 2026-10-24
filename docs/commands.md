# Commandes Console

- [Commandes Console](#commandes-console)
- [Introduction](#introduction-18)
- [Commandes de Développement](#commandes-de-développement)
- [Commandes de Génération](#commandes-de-génération)
- [Commandes de Base de Données](#commandes-de-base-de-données)
- [Commandes de Test](#commandes-de-test)
- [Commandes Messenger](#commandes-messenger)
- [Commandes Scheduler](#commandes-scheduler)
- [Commandes de Session](#commandes-de-session)
- [Commandes de Routines](#commandes-de-routines)
- [Commandes Spark](#commandes-spark)
- [Commandes DriftQL](#commandes-driftql)
- [Commandes Broadcasting](#commandes-broadcasting)
- [Prochaines Étapes](#prochaines-étapes-14)

---

## Introduction

Tonka fournit un large éventail de commandes console pour faciliter le développement, la génération de code, la gestion de la base de données et bien plus encore.

**Structure :**
```bash
php tonka [commande] [options] [arguments]
```

---

## Commandes de Développement

| Commande | Description | Exemple |
|----------|-------------|---------|
| `dev` | Démarrer le serveur de développement | `php tonka dev --port=8080 --host=0.0.0.0` |
| `key:generate` | Générer la clé APP_KEY | `php tonka key:generate` |
| `storage:link` | Créer les liens symboliques | `php tonka storage:link` |
| `session:clear` | Vider les sessions | `php tonka session:clear` |

---

## Commandes de Génération

| Commande | Description | Exemple |
|----------|-------------|---------|
| `make:controller` | Créer un contrôleur | `php tonka make:controller UserController --resource` |
| `make:model` | Créer un modèle | `php tonka make:model User` |
| `make:entity` | Créer une entité | `php tonka make:entity UserEntity` |
| `make:request` | Créer un Form Request | `php tonka make:request UserRequest` |
| `make:middleware` | Créer un middleware | `php tonka make:middleware AuthMiddleware` |
| `make:command` | Créer une commande | `php tonka make:command SendReportCommand` |
| `make:mail` | Créer un mail | `php tonka make:mail WelcomeMail` |
| `make:event` | Créer un événement | `php tonka make:event UserRegistered` |
| `make:listener` | Créer un listener | `php tonka make:listener SendWelcomeEmailListener` |
| `make:task` | Créer une tâche | `php tonka make:task SendDailyReportTask` |
| `make:seeder` | Créer un seeder | `php tonka make:seeder UserSeeder` |
| `make:factory` | Créer une factory | `php tonka make:factory UserFactory` |
| `make:test` | Créer un test | `php tonka make:test UserControllerTest` |
| `make:policy` | Créer une politique | `php tonka make:policy UserPolicy` |
| `make:provider` | Créer un Service Provider | `php tonka make:provider AppServiceProvider` |
| `make:migration` | Créer une migration | `php tonka make:migration create_users_table` |

---

## Commandes de Base de Données

| Commande | Description | Exemple |
|----------|-------------|---------|
| `migrate:fresh` | Réinitialiser la base de données | `php tonka migrate:fresh --manifest=manifest --seed` |
| `migrate:entity` | Migrer une entité spécifique | `php tonka migrate:entity User --alter --dump=dumpfile` |
| `db:clear` | Vider la base de données | `php tonka db:clear manifest` |
| `db:drop` | Supprimer une table | `php tonka db:drop User` |
| `db:seed` | Exécuter les seeders | `php tonka db:seed --seeder=UserSeeder` |
| `db:entity` | Créer une entité de base de données | `php tonka db:entity UserEntity` |
| `migrate:functions` | Migrer les fonctions | `php tonka migrate:functions` |
| `migrate:procedures` | Migrer les procédures | `php tonka migrate:procedures` |
| `migrate:views` | Migrer les vues | `php tonka migrate:views` |
| `session:migrate` | Migrer la table des sessions | `php tonka session:migrate` |

---

## Commandes de Test

| Commande | Description | Exemple |
|----------|-------------|---------|
| `test` | Exécuter tous les tests | `php tonka test` |
| `test --controller` | Exécuter un test spécifique | `php tonka test --controller=User` |

---

## Commandes Messenger

| Commande | Description | Exemple |
|----------|-------------|---------|
| `messenger:consume` | Démarrer un worker | `php tonka messenger:consume --transport=elegant` |
| `messenger:kill` | Arrêter les workers | `php tonka messenger:kill` |
| `messenger:failed:show` | Afficher les messages en échec | `php tonka messenger:failed:show` |
| `messenger:failed:retry` | Relancer les messages en échec | `php tonka messenger:failed:retry 123` |

---

## Commandes Scheduler

| Commande | Description | Exemple |
|----------|-------------|---------|
| `schedule:run` | Démarrer le scheduler | `php tonka schedule:run` |
| `schedule:kill` | Arrêter le scheduler | `php tonka schedule:kill` |

---

## Commandes de Session

| Commande | Description | Exemple |
|----------|-------------|---------|
| `session:clear` | Vider les sessions | `php tonka session:clear` |

---

## Commandes de Routines

| Commande | Description | Exemple |
|----------|-------------|---------|
| `migrate:functions` | Migrer les fonctions | `php tonka migrate:functions` |
| `migrate:procedures` | Migrer les procédures | `php tonka migrate:procedures` |
| `migrate:views` | Migrer les vues | `php tonka migrate:views` |

---

## Commandes Spark

| Commande | Description | Exemple |
|----------|-------------|---------|
| `spark:make_contract` | Créer un contrat Spark | `php tonka spark:make_contract UserPolicy` |

---

## Commandes DriftQL

| Commande | Description | Exemple |
|----------|-------------|---------|
| `driftql:config` | Créer la configuration DriftQL | `php tonka driftql:config` |
| `driftql:model` | Créer un modèle DriftQL | `php tonka driftql:model User` |
| `driftql:create_entities` | Créer les entités DriftQL | `php tonka driftql:create_entities` |
| `driftql:make_contract` | Créer un contrat DriftQL | `php tonka driftql:make_contract UserPolicy` |

---

## Commandes Broadcasting

| Commande | Description | Exemple |
|----------|-------------|---------|
| `broadcast:event` | Créer un événement broadcastable | `php tonka broadcast:event OrderShipped` |

---

## Prochaines Étapes

Félicitations ! Vous avez parcouru toute la documentation de Tonka.

### 📚 Récapitulatif

| Section | Contenu |
|---------|---------|
| **Démarrer** | Introduction, Installation, Structure, Architecture |
| **Fondamentaux** | Routing, Contrôleurs, Requêtes, Réponses, Middlewares, Vues, Validation, Sessions, Mail |
| **Base de Données** | Configuration, Query Builder, Elegant ORM, Entités, Migrations, Seeders |
| **Sécurité** | Authentification, Autorisation, CSRF, Chiffrement |
| **Avancé** | Événements, Messenger, Broadcasting, Task Scheduling, Tests, Console, Service Providers |
| **Frontend** | Spark, DriftQL, Vite, Broadcaster Client |
| **Référence** | Helpers, Facades, Commandes Console |

---

**Documentation terminée !** 🚀

```php
echo "Tonka Framework - Documentation Complete";
```

Merci d'avoir exploré Tonka ! 🎉