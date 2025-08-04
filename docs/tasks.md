- [Introduction](#introduction)
- [Getting Started](#getting-started)
- [Consuming Scheduled Tasks](#consuming-scheduled-tasks)
- [Killing Scheduled Tasks](#killing-scheduled-tasks)

# Introduction

**Tonka** provides built-in support for task scheduling, enabling developers to automate repetitive operations such as database backups, email notifications, and report generation. By leveraging **Tonka**'s scheduler, you can define tasks to run at specific intervals using a simple and expressive syntax.

## Prerequisite
Ensure the [`clicalmani/task`](https://packagist.org/packages/clicalmani/task) package is installed in your project before proceeding. You can add it using Composer:

```bash
composer require clicalmani/task
```

## Getting Started

To schedule a task in **Tonka**, follow these steps:

### 1. Define a Message

Create a message class representing the task data:

```php
<?php
namespace App\Scheduler\Message;

class BackupDatabase implements \Clicalmani\Task\Messenger\MessageInterface
{
    public function __construct(private int $id)
    {
        // ...
    }

    public function getId() : int
    {
        return $this->id;
    }
}
```

### 2. Create a Message Handler

Implement a handler class with an `__invoke()` method:

```php
<?php
namespace App\Scheduler\Handler;

use Clicalmani\Task\Messenger\MessageInterface;

class BackupDatabaseHandler implements \Clicalmani\Task\Handler\TaskHandlerInterface
{
    public function __invoke(MessageInterface $message) : void
    {
        // ...
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
                new BackupDatabase(DB::getInstance())
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

To kill scheduled tasks, you can terminate the scheduled tasks by running `schedule:kill` command.

```bash
php tonka schedule:kill
```
