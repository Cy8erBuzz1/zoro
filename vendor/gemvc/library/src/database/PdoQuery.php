<?php

namespace GemLibrary\Database;

class PdoQuery extends QueryExecuter
{

    /**
     * @if null , use default connection in config.php
     * pass $connection name to parent and create PDO Connection to Execute Query
     */
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * @param string $insertQuery Sql insert query
     * @param array<string,mixed> $arrayBindKeyValue
     * @return false|int
     * $query example: 'INSERT INTO users (name,email,password) VALUES (:name,:email,:password)'
     * arrayBindKeyValue Example [':name' => 'some new name' , ':email' => 'some@me.com , :password =>'si§d8x']
     * success : return last insertd id
     * you can call affectedRows() to get how many rows inserted
     * error: $this->getError();
     */
    public function insertQuery(string $insertQuery, array $arrayBindKeyValue = []): int|false
    {
        if ($this->isConnected()) {
            if ($this->executeQuery($insertQuery, $arrayBindKeyValue)) {
                return (int) $this->lastInsertId();
            }
        }
        return false;
    }

    /**
     * @param array<string,mixed> $arrayBindKeyValue
     * @return false|int
     * $query example: 'UPDATE users SET name = :name , isActive = :isActive WHERE id = :id'
     * arrayBindKeyValue Example [':name' => 'some new name' , ':isActive' => true , :id => 32 ]
     * in success return positive number affected rows and in error false
     */
    public function updateQuery(string $updateQuery, array $arrayBindKeyValue = []): int|null|false
    {
        if ($this->isConnected()) {
            if ($this->executeQuery($updateQuery, $arrayBindKeyValue)) {
                return $this->affectedRows();
            }
        }
        return false;
    }

    /**
     * @param array<string,mixed> $arrayBindKeyValue
     * @query example: 'DELETE users SET name = :name , isActive = :isActive WHERE id = :id'
     * @arrayBindKeyValue example [':id' => 32 ]
     * @success return positive number affected rows and in error false
     */
    public function deleteQuery(string $deleteQuery, array $arrayBindKeyValue = []): int|null|false
    {
        if ($this->isConnected()) {
            if ($this->executeQuery($deleteQuery, $arrayBindKeyValue)) {
                return $this->affectedRows();
            }
        }
        return false;
    }

    /**
     * @param array<mixed> $arrayBind
     * @success set this->affectedRows
     * @error set this->error and return false
     */
    private function executeQuery(string $query, array $arrayBind): bool
    {
        if ($this->isConnected()) {
            $this->query($query);
            foreach ($arrayBind as $key => $value) {
                $this->bind($key, $value);
            }
            return $this->execute();
        }
        return false;
    }

    /**
     * @param array<mixed> $arrayBindKeyValue
     * @return false|array<mixed>
     * @$query example: 'SELECT * FROM users WHERE email = :email'
     * @arrayBindKeyValue Example [':email' => 'some@me.com']
     */
    public function selectQueryObjets(string $selectQuery, array $arrayBindKeyValue = []): array|false
    {
        $result = false;
        if ($this->isConnected()) {
            if ($this->executeQuery($selectQuery, $arrayBindKeyValue)) {
                return $this->fetchAllObjects();
            }
        }
        return $result;
    }

     /**
     * @param array<mixed> $arrayBindKeyValue
     * @return false|array<mixed>
     * @$query example: 'SELECT * FROM users WHERE email = :email'
     * @arrayBindKeyValue Example [':email' => 'some@me.com']
     */
    public function selectQuery(string $selectQuery, array $arrayBindKeyValue = []): array|false
    {
        if (!$this->isConnected()) {
            return false;
        }
        if ($this->executeQuery($selectQuery, $arrayBindKeyValue)) {
            return $this->fetchAll();
        }
        return false;
    }

    /**
     * @param string $selectCountQuery
     * @param array<mixed> $arrayBindKeyValue
     * @return int|false
     * @$query example: 'SELECT COUNT(*) FROM users WHERE name LIKE :name'
     * @arrayBindKeyValue Example [':name' => 'someone']
     */
    public function selectCountQuery(string $selectCountQuery, array $arrayBindKeyValue = []): int|false
    {
        if (!$this->isConnected()) {
            return false;
        }
        if ($this->executeQuery($selectCountQuery, $arrayBindKeyValue)) {
            $result = $this->fetchColumn();
            if ($result !== false && is_numeric($result)) {
                return (int) $result;
            }
        }
        return false;
    }
}
