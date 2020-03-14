<?php

namespace Common\Database\ActiveRecord;

class Collection
{
    /**
     * @var array
     */
    public $collection;
    /**
     * @var int
     */
    public $index;

    /**
     * Collection constructor.
     *
     * @param array $collection
     */
    public function __construct($collection = [])
    {
        $this->collection = $collection;
        $this->index = -1;
    }

    /**
     * @param $item
     * @param null $key
     */
    public function add($item, $key = null)
    {
        is_null($key)
            ? $this->collection[++$this->index] = $item
            : $this->collection[$key] = $item;
    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->collection;
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    public function get($key)
    {
        return $this->collection[$key];
    }
}