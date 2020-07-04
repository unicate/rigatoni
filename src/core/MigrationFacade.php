<?php


namespace Unicate\Rigatoni\core;


class MigrationFacade implements FacadeInterface {

    /**
     * MigrationFacade constructor.
     */
    private $setupMigration;
    private $fileMigration;
    private $genericMigration;
    private $versionedMigration;
    private $repeatableMigration;
    private $undoMigration;

    public function __construct(
        SetupMigration $setupMigration,
        FileMigration $fileMigration,
        GenericMigration $genericMigration,
        VersionedMigration $versionedMigration,
        RepeatableMigration $repeatableMigration,
        UndoMigration $undoMigration
    ) {
        $this->setupMigration = $setupMigration;
        $this->fileMigration = $fileMigration;
        $this->genericMigration = $genericMigration;
        $this->versionedMigration = $versionedMigration;
        $this->repeatableMigration = $repeatableMigration;
        $this->undoMigration = $undoMigration;
    }

    public function setup() {
        return $this->setupMigration->createTable();
    }

    public function getFileMigrations() {
        return $this->fileMigration->getAll();
    }

    public function getPendingMigrations() {
        return $this->versionedMigration->getAllPending();
    }

    public function getRepeatableMigrations() {
        return $this->repeatableMigration->getAll();
    }

    public function getUndoMigrations(string $version) {
        return $this->undoMigration->getDownToVersion($version);
    }

    public function refresh() {
        $fileMigrations = $this->fileMigration->getAll();
        $dbMigrations = $this->genericMigration->getAllMigrations();
        $this->fileMigration->sync($fileMigrations, $dbMigrations);
    }

    public function applyMigration(MigrationObject $migration) {
        return $this->genericMigration->applyMigration($migration);
    }

    public function getMigration($prefix, $version) {
        return $this->genericMigration->getMigration($prefix, $version);
    }

    public function updateMigration(MigrationObject $migration) {
        return $this->genericMigration->updateMigration($migration);
    }
}