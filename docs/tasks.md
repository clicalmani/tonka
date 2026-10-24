# Task Scheduling

- [Task Scheduling](#task-scheduling)
- [Introduction](#introduction)
- [Création d'une Tâche](#creation-d-39-une-tache)
  - [Commande](#commande)
  - [Structure](#structure)
- [Fréquences](#frequences)
  - [Cron](#cron)
  - [every()](#every-)
- [Cron Expression](#cron-expression)
  - [Syntaxe](#syntaxe)
  - [Exemples](#exemples)
- [Jitter](#jitter)
- [Workers](#workers)
  - [Démarrage](#demarrage)
  - [Arrêt](#arret)
- [Commandes Console](#commandes-console)
  - [schedule:run](#schedule-run)
  - [schedule:kill](#schedule-kill)
- [Exemple Complet](#exemple-complet)
- [Prochaines Étapes](#prochaines-etapes)

---

## Introduction

Le système de **Task Scheduling** de Tonka permet de planifier l'exécution automatique de tâches à intervalles réguliers. Il est basé sur le composant Scheduler de Symfony et utilise un worker en arrière-plan pour exécuter les tâches selon une fréquence définie.

**Fonctionnalités :**
- ✅ Planification avec expressions cron
- ✅ Intervalles récurrents (`every()`)
- ✅ Jitter pour éviter les pics de charge
- ✅ État persistant (Stateful)
- ✅ Intégration avec Messenger
- ✅ Autodécouverte des tâches

---

## Création d'une Tâche

### Commande

```bash
php tonka make:task SendDailyReportTask
```

### Structure

```php
<?php

namespace App\Tasks;

use Clicalmani\Core\Scheduler\ScheduledTaskInterface;
use App\Message\SendDailyReportMessage;
use Symfony\Component\Scheduler\RecurringMessage;

class SendDailyReportTask implements ScheduledTaskInterface
{
    /**
     * Définit la fréquence et le message à envoyer
     */
    public static function schedule(): RecurringMessage
    {
        // Création du message avec les données
        $data = [
            'recipients' => ['admin@example.com'],
            'date' => date('Y-m-d')
        ];
        
        $message = new SendDailyReportMessage($data);
        
        // Planification quotidienne à 8h
        return RecurringMessage::cron('0 8 * * *', $message);
    }
}
```

---

## Fréquences

### Cron

Utilisez une expression cron pour définir une fréquence précise.

```php
// Tous les jours à minuit
return RecurringMessage::cron('0 0 * * *', $message);

// Toutes les heures
return RecurringMessage::cron('0 * * * *', $message);

// Tous les lundis à 9h
return RecurringMessage::cron('0 9 * * 1', $message);

// Dernier jour du mois à 23h59
return RecurringMessage::cron('59 23 L * *', $message);
```

### every()

Utilisez `every()` pour des intervalles simples.

```php
// Toutes les 5 minutes
return RecurringMessage::every('5 minutes', $message);

// Toutes les heures
return RecurringMessage::every('1 hour', $message);

// Tous les jours
return RecurringMessage::every('1 day', $message);
```

---

## Cron Expression

### Syntaxe

```
┌───────────── minute (0 - 59)
│ ┌───────────── hour (0 - 23)
│ │ ┌───────────── day of month (1 - 31)
│ │ │ ┌───────────── month (1 - 12)
│ │ │ │ ┌───────────── day of week (0 - 6) (Sunday=0)
│ │ │ │ │
│ │ │ │ │
* * * * *
```

### Exemples

| Expression | Description |
|------------|-------------|
| `* * * * *` | Toutes les minutes |
| `0 * * * *` | Toutes les heures |
| `0 0 * * *` | Tous les jours à minuit |
| `0 8 * * *` | Tous les jours à 8h |
| `0 0 * * 1` | Tous les lundis à minuit |
| `0 0 1 * *` | Le 1er de chaque mois à minuit |
| `*/15 * * * *` | Toutes les 15 minutes |
| `0 9-17 * * *` | Toutes les heures de 9h à 17h |
| `0 0 * * 0` | Tous les dimanches à minuit |
| `59 23 * * *` | Tous les jours à 23h59 |

---

## Jitter

Le jitter ajoute une variation aléatoire à l'exécution pour éviter les pics de charge.

```php
// Variation aléatoire de ±60 secondes
return RecurringMessage::cron('0 0 * * *', $message)
    ->withJitter(60);

// Variation aléatoire de ±5 minutes (300 secondes)
return RecurringMessage::cron('0 8 * * *', $message)
    ->withJitter(300);
```

---

## Workers

### Démarrage

```bash
php tonka schedule:run
```

### Arrêt

```bash
php tonka schedule:kill
```

---

## Commandes Console

### schedule:run

Démarre le scheduler en arrière-plan.

```bash
php tonka schedule:run

# En arrière-plan
nohup php tonka schedule:run &
```

### schedule:kill

Arrête tous les workers du scheduler.

```bash
php tonka schedule:kill
```

---

## Exemple Complet

### 1. Message

```php
<?php

namespace App\Message;

class SendDailyReportMessage
{
    public function __construct(
        private array $data
    ) {}

    public function getData(): array
    {
        return $this->data;
    }

    public function getRecipients(): array
    {
        return $this->data['recipients'] ?? [];
    }

    public function getDate(): string
    {
        return $this->data['date'] ?? date('Y-m-d');
    }
}
```

### 2. Handler

```php
<?php

namespace App\Handlers;

use App\Message\SendDailyReportMessage;
use App\Mail\DailyReport;

class SendDailyReportHandler
{
    public function __construct(
        private MailerInterface $mailer
    ) {}

    public function __invoke(SendDailyReportMessage $message): void
    {
        foreach ($message->getRecipients() as $recipient) {
            $this->mailer->to($recipient)
                ->send(new DailyReport($message->getDate()));
        }

        logger()->info('Daily report sent', [
            'date' => $message->getDate(),
            'recipients' => count($message->getRecipients())
        ]);
    }
}
```

### 3. Tâche

```php
<?php

namespace App\Tasks;

use Clicalmani\Core\Scheduler\ScheduledTaskInterface;
use App\Message\SendDailyReportMessage;
use Symfony\Component\Scheduler\RecurringMessage;

class SendDailyReportTask implements ScheduledTaskInterface
{
    public static function schedule(): RecurringMessage
    {
        $recipients = ['admin@example.com', 'manager@example.com'];
        
        $data = [
            'recipients' => $recipients,
            'date' => date('Y-m-d')
        ];
        
        $message = new SendDailyReportMessage($data);

        // Tous les jours à 8h avec jitter de 5 minutes
        return RecurringMessage::cron('0 8 * * *', $message)
            ->withJitter(300);
    }
}
```

### 4. Configuration

```php
// config/scheduler.php
return [
    'tasks' => 'app/Tasks',
    'handlers' => 'app/Handlers',
    'statefull' => true,
];
```

### 5. Exécution

```bash
# Démarrer le scheduler
php tonka schedule:run

# Vérifier les logs
tail -f storage/logs/app.log
```

---

## Prochaines Étapes

Maintenant que vous maîtrisez le Task Scheduling :

1. **[Tests](testing.md)** → Tests automatiques
2. **[Console](console.md)** → Commandes personnalisées
3. **[Service Providers](providers.md)** → Enregistrement de services

---

**Task Scheduling compris !** 🚀 → [Tests](testing.md)