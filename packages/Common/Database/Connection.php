<?php

namespace Common\Database;

use PDO;
use PDOException;

class Connection
{
    /**
     * @var null
     */
    protected static $instance = null;

    /**
     * @var PDO
     */
    protected $pdo;
    /**
     * @var int
     */
    protected $fetchMode = PDO::FETCH_CLASS;

    /**
     * Connection constructor.
     */
    public function __construct()
    {
        $pdo  = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';', DB_USER, DB_PASS);
        if(!$pdo){
            throw new PDOException('MySQL connection error.');
        }
        $this->pdo = $pdo;
    }

    /**
     * @return Connection|null
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * @return PDO
     */
    public function getPdo()
    {
        return $this->pdo;
    }

    /**
     * @return int
     */
    public function fetchMode()
    {
        return $this->fetchMode;
    }
}