<?php

namespace Common\Database\ActiveRecord;

use Common\Database\Connection;

abstract class Model
{
    /**
     * @var Connection|null
     */
    protected $connection;
    /**
     * @var
     */
    protected $table;
    /**
     * @var string
     */
    protected $primary_key = 'id';
    /**
     * @var array
     */
    protected $with = [];
    /**
     * @var bool
     */
    protected $exists = false;
    /**
     * @var array
     */
    protected $fillable = [];
    /**
     * Visible columns. Defaults to all db columns.
     *
     * @var array
     */
    protected $visible;
    /**
     * @var array
     */
    protected $columns = [];

    /**
     * Model constructor.
     */
    public function __construct()
    {
        $this->connection = Connection::getInstance();
        if (!isset($this->table)) {
            static::setTable(strtolower(explode('\\', static::class)[2]) . 's');
        }
        $this->setTableColumns();
    }

    /**
     * @return mixed
     */
    public static function all()
    {
        $modelInstance = new static();
        return $modelInstance->getAll();
    }

    /**
     * @param $primary_key
     *
     * @return mixed
     */
    public static function find($primary_key)
    {
        $modelInstance = new static();
        return $modelInstance->getByPrimaryKey($primary_key);
    }

    /**
     * @param array $parameters
     *
     * @return mixed
     */
    public static function create(array $parameters)
    {
        return (new static())->setAttributes($parameters)->save();
    }

    /**
     * @param array $relations
     *
     * @return static
     */
    protected static function with(array $relations)
    {
        return new static();
    }

    /**
     * @param string $table
     *
     * @return $this
     */
    public function setTable(string $table)
    {
        $this->table = $table;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Set model's columns and visible attributes to correspond to database fields.
     *
     * @return void
     */
    public function setTableColumns()
    {
        $sql = "SELECT column_name 
                  FROM INFORMATION_SCHEMA.COLUMNS 
                  WHERE table_schema = ?
                  AND table_name = ?;";
        try {
            $stmt = $this->connection->getPdo()->prepare($sql);
            $stmt->bindValue(1, DB_NAME);
            $stmt->bindValue(2, $this->getTable());
            $stmt->execute();
            $result = [];
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $result[] = $row['column_name'];
            }
            $this->columns = $result;
            $this->visible = $result;
        } catch (\PDOException $e) {
            trigger_error('Could not connect to MySQL database. ' . $e->getMessage(), E_USER_ERROR);
        }
    }

    /**
     * Get all rows.
     *
     * @return array
     */
    protected function getAll()
    {
        $query = "SELECT * FROM $this->table;";
        $stmt = $this->connection->getPdo()->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll($this->connection->fetchMode(), get_called_class());
    }

    /**
     * Find row by primary key.
     *
     * @param $id
     *
     * @return array
     */
    protected function getByPrimaryKey($id)
    {
        $query = "SELECT * FROM $this->table WHERE $this->primary_key = ?;";
        $stmt = $this->connection->getPdo()->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        return $stmt->fetchAll($this->connection->fetchMode(), get_called_class());
    }

    /**
     * @return array|string
     */
    protected function insert()
    {
        $query = "INSERT INTO $this->table ";

        $keys_string = '(';
        $values_string = ' VALUES (';

        foreach ($this->columns as $column) {
            $keys_string .= "$column,";
            $values_string .= '?,';
        }

        $keys_string = rtrim($keys_string, ',') . ')';
        $values_string = rtrim($values_string, ',') . ')';
        $query .= $keys_string . $values_string . ';';

        $stmt = $this->connection->getPdo()->prepare($query);
        $i = 1;
        foreach ($this->columns as $column) {
            error_log($column);
            $stmt->bindParam($i, $this->{$column});
            $i++;
        }
        try {
            $this->connection->getPdo()->beginTransaction();
            $stmt->execute();
            $this->connection->getPdo()->commit();
            return $this->connection->getPdo()->lastInsertId();
        } catch (\PDOException $e) {
            $this->connection->getPdo()->rollBack();
            error_log('Error: ' . $e->getMessage());
        }
        $this->exists = true;
    }

    /**
     * @param $params
     *
     * @return $this
     */
    protected function setAttributes($params)
    {
        array_walk($params, function ($value, $key) {
            $this->{$key} = $value;
        });
        return $this;
    }

    /**
     * @param $name
     *
     * @return null
     */
    public function __get($name)
    {
//        return $this->{$name};
        if (array_key_exists($name, $this->visible)) {
            return $this->{$name};
        }
        return null;
    }

    /**
     * Inserts or updates existing record.
     *
     * @return $this
     */
    public function save()
    {
        !$this->exists ? $this->insert() : $this->update();
        return $this;
    }

    /**
     *
     */
    protected function update()
    {
        //TODO: implement logic
        return;
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function setAttribute($key, $value)
    {
        $this->{$key} = $value;
    }

    /**
     * Dynamically set attribute
     *
     * @param $key
     * @param $value
     */
    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }
}