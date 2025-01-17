## Introduction

Deploying a Tonka application is straightforward and hassle-free. One of the key reasons for this simplicity is that Tonka does not use any internal caching mechanisms. This means you can deploy your application without worrying about cache invalidation or related issues, ensuring a smooth and efficient deployment process.

## Server Requirement
Tonka Framework has minimal requirements. You just need to ensure that the server meets the following criteria:

- **PHP Version**: The server must have PHP version 8.1 or higher.
- **PHP Extensions**: The following extensions must be enabled:
    - `pdo`
    - `mbstring`
    - `json`
    - `openssl`
    - `curl`
    - `xml`

## Directory Permissions

Tonka will need to write to the storage directory, so ensure that the web server process owner has permission to write to this directory.

## Optimization

With Tonka, there is nothing to optimize because there is no cache. The only thing you may optimize is the Composer autoloader by executing the following Composer command:

```bash
composer install --no-dev -o
```

!> You may likely clear the session directory by running the `session:clear` console command.
