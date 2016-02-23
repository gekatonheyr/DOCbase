<?php

class DB{

    protected $connection;

    public function __construct($host, $user, $password, $db_name){
        $host = Config::get('db.host');
        $user = Config::get('db.user');
        $password = Config::get('db.password');
        $db_name = Config::get('db.db_name');
        $this->connection = new mysqli($host, $user, $password, $db_name);

        if( mysqli_connect_error() ){
            throw new Exception('Could not connect to DB');
        }

        $this->connection->query('set names utf8');
    }

    public function query($sql){
        if ( !$this->connection ){
            return false;
        }

        $result = $this->connection->query($sql);

        if ( mysqli_error($this->connection) ){
            throw new Exception(mysqli_error($this->connection));
        }

        if ( is_bool($result) ){
            return $result;
        }

        $data = array();
        while( $row = mysqli_fetch_assoc($result) ){
            $data[] = $row;
        }
        return $data;
    }

    public function escape($str){
        return mysqli_escape_string($this->connection, $str);
    }

}