[logo]: logo.png "Rigatoni"
![alt text][logo]
# Rigatoni

> A simple dish, made of fresh SQL migrations.


## What is it?
- Rigatoni manages SQL migrations.
- It uses plain old SQL files. No need to learn a new framework.
- It's heavily inspired by tools like Fly way or Phinx.
- It should be easy and intuitive to use.
- It can be integrated into your project as composer package.
- Use it via commandline interface (CLI) or write your own integration.

## Rules
- Migrations are written in plain old SQL.
- There are only 3 types of migrations.
    - Versioned-Migrations: Only executed once and the filename needs to start with V and followed by the version.
    - Repeatable migrations: Executed every time the migration runs. The filename needs to start with R and is not versioned.
    - Undo-Migrations. The filename needs to start with U and followed by the version. It must be called with a specific version.
- New Migrations are always in state PENDING.
- Each statement of a migration will be executed separately. 
    - If one statement fails, the whole migration will be marked as FAILED. All errors are listed in the migrations-table.
    - Sucessfully executed migrations are marked with SUCCESS.
- Versioned migrations are executed by version in ascending order. Repeatable migrations are executed afterwards.
- If you want to revert a migration, call the Undo-Migration. 
    - All Undo-Migrations with version >= the passed version will be executed.
    - The reverted migration will be marked as PENDING again. 
    - The Undo-Migration is marked with SUCCESS.
- If you delete a migration entry from the migrations-table, but the migration-file still exists, it will be re-inserted as PENDING.


Path are relative to Project root. It is assumed, that that is where the composer.json file is located.


## Getting Started

Installation Use Git or Composer:

```
git clone https://github.com/unicate/xxx.git my-project-name
composer install
```

```
composer create-project unicate/xxx my-project-name
```
Init - Creates a config file in your project root.

```
./rigatoni init
```

Setup - Migrations-table will be created. (existing will be dropped).

```
./rigatoni setup
```
Migrate - Versioned and repeatable migrations.

```
./rigatoni migrate
```

Undo migration

```
./rigatoni migrate undo -v 004
```

## Disclaimer & License

This project is released under the [MIT](https://raw.githubusercontent.com/unicate/licenses/master/MIT/MIT-Licence.txt) licence.

Thanks to The-Noun-Project for the logo: https://thenounproject.com/term/rigatoni/2447057/

## Finally            
> Now go and build something and **make people happy**!


