<?php
/**
 * @author https://unicate.ch
 * @copyright Copyright (c) 2020
 * @license Released under the MIT license
 */

namespace Unicate\Rigatoni\Migrations;


interface MigrationFacadeInterface {

    public function setup();

    public function getFileMigrations();

    public function getPendingMigrations();

    public function getRepeatableMigrations();

    public function getUndoMigrations(string $version);

    public function refresh();

    public function applyMigration(MigrationVO $migration);

    public function getMigration($prefix, $version);

    public function updateMigration(MigrationVO $migration);

}