<?php
class Database
{

    private $database;

    function __construct()
    {
        $this->database = $this->getConnection();
    }

    function __destruct()
    {
        $this->database->close();
    }

    function exec($query)
    {
        $this->database->exec($query);
    }

    function query($query)
    {
        $result = $this->database->query($query);
        return $result;
    }

    function querySingle($query)
    {
        $result = $this->database->querySingle($query, true);
        return $result;
    }

    function prepare($query)
    {
        return $this->database->prepare($query);
    }

    function lastInsertRowID()
    {
        return $this->database->lastInsertRowID();
    }

    function getArray($query)
    {
        $array = [];
        while (($el = $query->fetchArray())) {
            $array[] = $el;
        }

        return $array;
    }

    function escapeString($string)
    {
        return $this->database->escapeString($string);
    }

    private function getConnection()
    {
        $conn = new SQLite3('../database/2doo.db');
        return $conn;
    }
}
