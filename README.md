[logo]: img/logo.png "Rigatoni"
[migratescreen]: img/migrate-screen.png "Migrate screen"
[classes]: img/class-diagram.png "Class diagram" | width=100
![alt text][logo]
# Rigatoni

> A simple dish - made of fresh SQL migrations.

## What is it?
- Rigatoni manages SQL migrations.
- It uses plain old SQL files. No need to learn a new framework.
- It's heavily inspired by tools like [Flyway](https://flywaydb.org/) or [Phinx](https://phinx.org/).
- It should be easy and intuitive to use.
- Use it via commandline interface (CLI) or write your own integration.
- It can be used as stand-alone application to manage your DB migrations.
- It can be integrated into your project as composer package.

## Rules
- Migrations are written in plain old SQL.
- There are only 3 types of migrations.
    - Versioned-Migrations: Only executed once and the filename needs to start with V and followed by the version.
    - Repeatable migrations: Executed every time the migration runs. The filename needs to start with R and is not versioned.
    - Undo-Migrations. The filename needs to start with U and followed by the version. It must be called with a specific version.
- Every Versioned-Migration should have a corresponding Undo-Migration.
- New Migrations are always in state PENDING.
- Each SQL statement of a migration will be executed separately. 
    - If one statement fails, the whole migration will be marked as FAILED. All errors are listed in the migrations-table.
    - Successful executed migrations will be marked with SUCCESS.
- Versioned migrations will be executed in ascending order by version. Repeatable migrations are executed afterwards.
- If you want to revert a migration, call the Undo-Migration. 
    - All Undo-Migrations with version >= the passed version will be executed.
    - The reverted migration will be marked as UNDONE again. 
    - The Undo-Migration is marked with SUCCESS.
- If you delete a migration entry from the migrations-table, but the migration-file still exists, it will be re-inserted as PENDING.
- Paths are relative to project root. It is assumed, that is where we find the composer.json file.

## Commands
- init
    - Creates a new config file in project root directory.
- check
    - Checks the configuration, the DB connection and if the folder for the SQL migrations exists.
- setup
    - Creates a fresh table for the migration information.
- migrate
    - Executes the pending migrations.
- undo
    - Reverts executed migrations.

![alt text][migratescreen]

## Installation

Installation Use Git or Composer:

```
git clone https://github.com/unicate/rigatoni.git my-project-name
composer install --no-dev
```
Use the option --no-dev if you just want to run migrations. 
(Other dependencies are only for testing.)
```
composer create-project unicate/rigatoni my-project-name --no-dev
```
## Commands

Init - Creates a config file in your project root.

```
./rigatoni init
```

Check - Does some config and connection checking.

```
./rigatoni check
```
Info - Shows all entries from migration table.

```
./rigatoni info
```

Setup - Migrations-table will be created. (existing will be dropped).

```
./rigatoni setup
```
Migrate - Executes versioned and repeatable migrations.

```
./rigatoni migrate
```

Undo migration

```
./rigatoni undo 004
```
## Own Integration
In case you would like to write your own integration to use Rigatoni in 
your project just have a look at the MigrationFacade class and the methods
provided.

![alt text][classes]

## Disclaimer & License

This project is released under the [MIT](https://raw.githubusercontent.com/unicate/licenses/master/MIT/MIT-Licence.txt) licence.

Thanks to The-Noun-Project for the logo: https://thenounproject.com/term/rigatoni/2447057/

## Finally            
> Now go and build something and **make people happy**!


