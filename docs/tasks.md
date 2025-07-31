- [Introduction](#introduction)
- [Getting Started](#getting-started)
- [Consuming Scheduled Tasks](#consuming-scheduled-tasks)
- [Killing Scheduled Tasks](#killing-scheduled-tasks)

# Introduction

**Tonka** provides built-in support for task scheduling, enabling developers to automate repetitive operations such as database backups, email notifications, and report generation. By leveraging **Tonka**'s scheduler, you can define tasks to run at specific intervals using a simple and expressive syntax.

## Key Features

- **Cron-like Scheduling:** Schedule tasks using familiar cron expressions.
- **Command Integration:** Run custom PHP commands or scripts.
- **Error Handling:** Built-in logging and error reporting for scheduled tasks.

## Getting Started

To schedule a task in **Tonka**, follow these steps:

### 1. Define a Message

Create a message class representing the task data:

```php
// app/Scheduler/Message

class BackupDatabase
{
    private $database;

    public function __construct(object $database)
    {
        $this->database = $database;
    }
}
```

### 2. Create a Message Handler

Implement a handler class with an `__invoke()` method:

```php
// app/Scheduler/Handler

use App\Scheduler\Message\BackupDatabase;

class BackupDatabaseHandler
{
    public function __invoke(BackupDatabase $message)
    {
        // Logic to back up the database
    }
}
```

### 3. Implement a Scheduler

Define a scheduler class with a `getSchedule()` method that returns a `Schedule` instance:

```php
// app/Scheduler

use Clicalmani\Task\Messenger\RecurringMessage;
use Clicalmani\Foundation\Support\Facade\DB;
use Clicalmani\Task\Scheduler\Schedule;

class BackupDatabase
{
    public function getSchedule(): Schedule
    {
        return (new Schedule)->with(
            RecurringMessage::cron(
                '0 2 * * *', // Runs daily at 2 AM
                new BackupDatabase(DB::getInstance()),
                \DateTime::createFromImmutable(new \DateTimeImmutable('11:23', new \DateTimeZone('Africa/Porto-Novo')))
            )
        );
    }
}
```

With these components, **Tonka** will automatically execute your handler at the scheduled interval.

## Consuming Scheduled Tasks

To execute scheduled tasks, use the `schedule:consume` command provided by **Tonka**. This command runs the scheduler and processes any due tasks:

```bash
php tonka schedule:consume
```

This setup ensures your scheduled tasks are consistently processed without manual intervention.

## Killing Scheduled Tasks

To stop scheduled tasks, you can terminate the scheduled tasks by running `schedule:stop` command.

```bash
php tonka schedule:stop
```
