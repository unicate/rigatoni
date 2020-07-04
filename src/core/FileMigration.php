<?php


namespace Unicate\Rigatoni\core;


use Medoo\Medoo;

class FileMigration extends AbstractMigration {

    protected $db;
    protected $config;

    /**
     * FiledMigration constructor.
     * @param Medoo $db
     * @param Config $config
     */
    public function __construct(Medoo $db, Config $config) {
        $this->db = $db;
        $this->config = $config;
    }

    /**
     * Generates array with VersionedMigration items from files.
     * @return MigrationObject[]
     */
    public function getAll() {
        $migrations = array();
        // Scan files in directory
        $files = array_diff(scandir($this->config->getSQLFolderPath()), array('..', '.'));
        $file_prefixes = AbstractMigration::PREFIX_VERSIONED_MIGRATION . AbstractMigration::PREFIX_UNDO_MIGRATION . AbstractMigration::PREFIX_REPEATABLE_MIGRATION;
        $file_extension = AbstractMigration::FILE_EXTENSION;

        // Iterate over all files
        foreach ($files as $file) {
            $matcher = preg_match('/^([' . $file_prefixes . '])(.*)' . AbstractMigration::MIGRATION_SEPARATOR . '(.*)(' . $file_extension . ')/', $file, $file_parts);
            if (!$matcher) {
                continue;
            }
            $migrations[] = new MigrationObject(
                $file_parts[1], // Prefix
                $file_parts[2], // Version
                $file
            );
        }
        return $migrations;
    }

    /**
     * Synchronizes file- and db migrations.
     * @param array $fileMigrations
     * @param array $dbMigrations
     * @return array
     */
    public function sync(array $fileMigrations, array $dbMigrations): array {
        $diff = array_udiff($fileMigrations, $dbMigrations, function (MigrationObject $a, MigrationObject $b) {
            return $a->getFile() !== $b->getFile();
        });
        foreach ($diff as $migration) {
            $this->insertMigration($migration);
        }
        return $diff;
    }

    /**
     * Insert one migration into migration table.
     * @param MigrationObject $migration
     * @return bool Success
     */
    private function insertMigration(MigrationObject $migration): bool {
        $this->db->insert("migrations", [
            "id" => $migration->getId(),
            "prefix" => $migration->getPrefix(),
            "version" => $migration->getVersion(),
            "file" => $migration->getFile(),
            "hash" => $migration->getHash(),
            "status" => $migration->getStatus(),
            "errors" => $migration->getErrors(),
            "installed_on" => $migration->getInstalledOn()
        ]);
        $success = intval($this->db->error()[0]);
        return $success === 0;
    }


}