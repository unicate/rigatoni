<?php
/**
 * @author https://unicate.ch
 * @copyright Copyright (c) 2020
 * @license Released under the MIT license
 */

namespace Unicate\Rigatoni\Migration;


class MigrationVO {
    private $id;
    private $prefix;
    private $version;
    private $file;
    private $hash;
    private $status;
    private $errors;
    private $installedOn;

    public function __construct($prefix, $version, $file) {
        $this->id = $version . crc32($file);
        $this->prefix = $prefix;
        $this->version = $version;
        $this->file = $file;
        $this->status = AbstractMigration::MIGRATION_STATUS_PENDING;
        $this->errors = '';
        $this->installedOn = null;
    }

    public function getId() {
        return $this->id;
    }

    public function getVersion() {
        return $this->version;
    }


    public function getPrefix() {
        return $this->prefix;
    }


    public function getFile() {
        return $this->file;
    }


    public function getHash() {
        return $this->hash;
    }


    public function setHash($hash): void {
        $this->hash = $hash;
    }


    public function getStatus() {
        return $this->status;
    }


    public function setStatus($status): void {
        $this->status = $status;
    }


    public function getErrors() {
        return $this->errors;
    }


    public function setErrors($errors): void {
        $this->errors = $errors;
    }


    public function getInstalledOn() {
        return $this->installedOn;
    }


    public function setInstalledOn($installedOn): void {
        $this->installedOn = $installedOn;
    }

    public function equals(MigrationVO $vo) : bool {
        return $this->getId() === $vo->getId();
    }

    public function empty() : bool {
        return empty($this->getFile());
    }

}