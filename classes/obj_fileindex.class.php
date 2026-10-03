<?php

class obj_fileindex extends axelhahn\pdo_db_base{

    /**
     * hash for a table
     * create database column, draw edit form
     * @var array 
     */
    protected array $_aProperties = [
        'idx'        => ['create' => 'varchar(32)',],
        'type'       => ['create' => 'varchar(8)',],
        'path'       => ['create' => 'varchar(1024)',],
        'file'       => ['create' => 'varchar(128)',],
        'modified'   => ['create' => 'int',],
        'size'       => ['create' => 'int',],
    ];

    public function __construct(object $oDB)
    {
        parent::__construct(__CLASS__, $oDB);
    }
}