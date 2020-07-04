<?php


namespace Unicate\Rigatoni\core;


interface FacadeInterface {

    public function setup();

    public function getFileMigrations();

    public function getPendingMigrations();

    public function getRepeatableMigrations();

    public function getUndoMigrations(string $version);

    public function refresh();

    public function applyMigration(MigrationObject $migration);

    public function getMigration($prefix, $version);

    public function updateMigration(MigrationObject $migration);

}