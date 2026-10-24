# Console

- [Console](#console)
- [Introduction](#introduction)
- [Commandes Built-in](#commandes-built-in)
  - [Commandes de Développement](#commandes-de-developpement)
  - [Commandes de Génération](#commandes-de-generation)
  - [Commandes de Base de Données](#commandes-de-base-de-donnees)
  - [Commandes de Test](#commandes-de-test)
  - [Commandes Messenger](#commandes-messenger)
  - [Commandes Scheduler](#commandes-scheduler)
  - [Commandes de Queue](#commandes-de-queue)
- [Création de Commandes](#creation-de-commandes)
  - [Commande de Génération](#commande-de-generation)
  - [Structure](#structure)
- [Arguments et Options](#arguments-et-options)
- [Helpers Console](#helpers-console)
- [Prochaines Étapes](#prochaines-etapes)

---

## Introduction

La **Console** de Tonka fournit une interface en ligne de commande pour interagir avec votre application. Elle offre un large éventail de commandes pour le développement, la génération de code, la gestion de la base de données, et plus encore.

---

## Commandes Built-in

### Commandes de Développement

| Commande | Description |
|----------|-------------|
| `php tonka dev` | Démarrer le serveur de développement |
| `php tonka key:generate` | Générer la clé APP_KEY |
| `php tonka storage:link` | Créer les liens symboliques |
| `php tonka session:clear` | Vider les sessions |

### Commandes de Génération

| Commande | Description |
|----------|-------------|
| `php tonka make:command` | Créer une commande console personnalisée |
| `php tonka make:controller` | Créer un contrôleur |
| `php tonka make:model` | Créer un modèle |
| `php tonka make:entity` | Créer une entité |
| `php tonka make:request` | Créer un Form Request |
| `php tonka make:middleware` | Créer un middleware |
| `php tonka make:mail` | Créer un mail |
| `php tonka make:event` | Créer un événement |
| `php tonka make:event-listener` | Créer un listener |
| `php tonka make:task` | Créer une tâche |
| `php tonka make:seeder` | Créer un seeder |
| `php tonka make:factory` | Créer une factory |
| `php tonka make:test` | Créer un test |
| `php tonka make:policy` | Créer une politique |
| `php tonka make:provider` | Créer un Service Provider |
| `php tonka make:validator` | Créer un valideur |
| `php tonka make:notification` | Créer une notification |

### Commandes de Base de Données

| Commande | Description |
|----------|-------------|
| `php tonka migrate:fresh` | Réinitialiser la base de données |
| `php tonka migrate:entity` | Migrer une entité spécifique |
| `php tonka db:clear` | Vider la base de données |
| `php tonka db:drop` | Supprimer une table |
| `php tonka db:seed` | Exécuter les seeders |

### Commandes de Test

| Commande | Description |
|----------|-------------|
| `php test/console run` | Exécuter tous les tests |
| `php test/console run --filter UserTest` | Exécuter un test spécifique |

### Commandes Messenger

Pilotent le système de files d'attente Symfony Messenger documenté sur la page [Messenger](messenger.md).

| Commande | Description |
|----------|-------------|
| `php tonka messenger:consume` | Démarrer un worker |
| `php tonka messenger:kill` | Arrêter les workers |
| `php tonka messenger:failed:show` | Afficher les messages en échec |
| `php tonka messenger:failed:retry` | Relancer les messages en échec |

### Commandes Scheduler

| Commande | Description |
|----------|-------------|
| `php tonka schedule:run` | Démarrer le scheduler |
| `php tonka schedule:kill` | Arrêter le scheduler |

### Commandes de Queue

Pilotent le sous-système de queue documenté sur la page [Les Queues](queue.md).

| Commande | Description |
|----------|-------------|
| `php tonka migrate:entity Job` | Migrer la table `jobs` |
| `php tonka queue:work` | Démarrer le worker |
| `php tonka queue:kill` | Arrêter le worker |
| `php tonka queue:restart` | Redémarrer le worker |
| `php tonka queue:failed:show` | Liste des tâches en échec |
| `php tonka queue:failed:retry` | Rejouer les tâches échouées |

---

## Création de Commandes

### Commande de Génération

```bash
php tonka make:command SendReportCommand
```

### Structure

```php
namespace App\Console\Commands;

use Clicalmani\Console\Commands\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'report:send',
    description: 'This command sends a report to the specified recipients',
    hidden: false
)]
class SendReportCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Sending report...');
        
        // Logique de la commande
        $this->sendReport();
        
        $output->writeln('Report sent successfully!');
        
        return Command::SUCCESS;
    }

    private function sendReport(): void
    {
        // Implémentation
    }
}
```

---

## Arguments et Options

### Arguments

Les arguments sont des paramètres obligatoires ou optionnels.

```php
protected function configure(): void
{
    $this->addArgument('user_id', InputArgument::REQUIRED, 'User ID');
    $this->addArgument('format', InputArgument::OPTIONAL, 'Report format', 'pdf');
}

protected function execute(InputInterface $input, OutputInterface $output): int
{
    $userId = $input->getArgument('user_id');
    $format = $input->getArgument('format');
    
    // Utilisation
}
```

**Utilisation :**
```bash
php tonka report:send 123
php tonka report:send 123 csv
```

### Options

Les options sont des paramètres optionnels préfixés par `--`.

```php
protected function configure(): void
{
    $this->addOption('force', null, InputOption::VALUE_NONE, 'Force execution');
    $this->addOption('format', 'f', InputOption::VALUE_REQUIRED, 'Output format', 'pdf');
    $this->addOption('verbose', 'v', InputOption::VALUE_NONE, 'Verbose output');
}

protected function execute(InputInterface $input, OutputInterface $output): int
{
    $force = $input->getOption('force');
    $format = $input->getOption('format');
    $verbose = $input->getOption('verbose');
    
    // Utilisation
}
```

**Utilisation :**
```bash
php tonka report:send --force --format=csv
php tonka report:send -f csv
```

---

## Helpers Console

### Output

```php
// Message simple
$output->writeln('Hello World');

// Message coloré
$output->writeln('<info>Success!</info>');
$output->writeln('<error>Error!</error>');
$output->writeln('<comment>Comment</comment>');
$output->writeln('<question>Question</question>');

// Tableau
$table = new Table($output);
$table->setHeaders(['ID', 'Name', 'Email']);
$table->setRows([
    [1, 'John', 'john@example.com'],
    [2, 'Jane', 'jane@example.com'],
]);
$table->render();

// Progression
$progress = new ProgressBar($output, 100);
$progress->start();
for ($i = 0; $i < 100; $i++) {
    usleep(10000);
    $progress->advance();
}
$progress->finish();
```

### Input

```php
// Récupération d'un argument
$userId = $input->getArgument('user_id');

// Récupération d'une option
$format = $input->getOption('format');

// Interaction (question)
$helper = $this->getHelper('question');
$question = new Question('Enter user ID: ');
$userId = $helper->ask($input, $output, $question);

// Question avec confirmation
$question = new ConfirmationQuestion('Continue? (y/N) ', false);
if ($helper->ask($input, $output, $question)) {
    // Continuer
}
```

---

## Exemple Complet

```php
namespace App\Console\Commands;

use Clicalmani\Console\Commands\Command;
use App\Models\User;
use App\Mail\ReportMail;
use Clicalmani\Core\Support\Facades\Mail;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\ProgressBar;

#[AsCommand(
    name: 'report:send',
    description: 'This command sends a report to the specified recipients',
    hidden: false
)]
class SendReportCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('report:send')
             ->setDescription('Send a report to users')
             ->addArgument('user_id', InputArgument::OPTIONAL, 'Specific user ID')
             ->addOption('format', 'f', InputOption::VALUE_REQUIRED, 'Report format', 'pdf')
             ->addOption('force', null, InputOption::VALUE_NONE, 'Force sending')
             ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Dry run');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $userId = $input->getArgument('user_id');
        $format = $input->getOption('format');
        $force = $input->getOption('force');
        $dryRun = $input->getOption('dry-run');

        // Récupération des utilisateurs
        if ($userId) {
            $users = [User::find($userId)];
        } else {
            $users = User::all();
        }

        $output->writeln('<info>Report generation started</info>');

        // Affichage des utilisateurs
        $table = new Table($output);
        $table->setHeaders(['ID', 'Name', 'Email']);
        foreach ($users as $user) {
            $table->addRow([$user->id, $user->name, $user->email]);
        }
        $table->render();

        if ($dryRun) {
            $output->writeln('<comment>Dry run - no emails sent</comment>');
            return Command::SUCCESS;
        }

        // Progression
        $progress = new ProgressBar($output, count($users));
        $progress->start();

        foreach ($users as $user) {
            try {
                Mail::to($user->email)->send(new ReportMail($format));
                $progress->advance();
            } catch (\Exception $e) {
                $output->writeln("\n<error>Failed to send to {$user->email}</error>");
                if (!$force) {
                    return Command::FAILURE;
                }
            }
        }

        $progress->finish();
        $output->writeln("\n<info>Report sent successfully!</info>");

        return Command::SUCCESS;
    }
}
```

**Utilisation :**
```bash
# Envoyer à tous les utilisateurs
php tonka report:send

# Envoyer à un utilisateur spécifique
php tonka report:send 123

# Envoyer en format CSV
php tonka report:send --format=csv

# Test (dry run)
php tonka report:send --dry-run

# Forcer en cas d'erreur
php tonka report:send --force
```
---

## Prochaines Étapes

Maintenant que vous maîtrisez la console :

1. **[Service Providers](providers.md)** → Enregistrement de services
2. **[Helpers](helpers.md)** → Helpers disponibles
3. **[Facades](facades.md)** → Facades principales

---

**Console comprise !** 🚀 → [Service Providers](providers.md)