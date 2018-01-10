<?php

namespace Tandem\DB;

use Monolog\Logger;

class PdoDB implements DBInterface
{
    /**
     * @var \PDO
     */
    private $conn;
    /**
     * @var array
     */
    private $connConfig;
    /**
     * @var Logger
     */
    private $logger;


    public static function getInstance($config, Logger $logger = null): DBInterface
    {
        $inst = new self($logger);
        $inst->connect($config);

        return $inst;
    }

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function connect($config)
    {
        if (isset($this->conn)) {
            return;
        }

        $this->connConfig = $config;

        try {
            $this->conn = new \PDO(
                $config['dsn'] ?? null,
                $config['username'] ?? null,
                $config['password'] ?? null,
                $config['attributes'] ?? []
            );
        } catch (\PDOException $e) {
            $this->log(Logger::ERROR, 'Connect to database! Error: ' . $e->getMessage());
            die();
        }

        $this->conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->conn->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

        if (isset($config['charset'])) {
            $this->conn->exec('SET NAMES ' . $this->conn->quote($config['charset']));
        }
        $this->log(Logger::INFO, "Connect to DB '" . $this->connConfig['dsn'] . "' user=" . ($this->connConfig['username'] ?? ''));
    }

    public function disconnect()
    {
        unset($this->conn);
        $this->log(Logger::INFO, "Disconnect from DB '" . $this->connConfig['dsn'] . "' user=" . $this->connConfig['username'] ?? '');
    }

    public function reconnect()
    {
        $this->disconnect();

        if (!empty($this->connConfig)) {
            $this->connect($this->connConfig);
        }
    }

    /**
     * @return \PDO
     */
    public function getConn(): \PDO
    {
        return $this->conn;
    }

    public function setDefaultSchema($schemaName)
    {
        if (!isset($this->conn)) {
            return;
        }
        $this->conn->exec("SET search_path TO {$schemaName};");
        //$this->log(Logger::INFO, "Set default schema name to {$schemaName}");
    }

    private function log($level, $msg)
    {
        if (isset($this->logger)) {
            $this->logger->log($level, $msg);
        }
    }

    public function executeQuery($query, array $params = [], array $types = [])
    {
//        try {
        if ($params) {
            //list($query, $params, $types) = SQLParserUtils::expandListParameters($query, $params, $types);

            $stmt = $this->conn->prepare($query);
            if ($types) {
                $this->bindTypedValues($stmt, $params, $types);
                $stmt->execute();
            } else {
                $stmt->execute($params);
            }
        } else {
            $stmt = $this->conn->query($query);
        }
//        } catch (\Exception $ex) {
        //throw DBALException::driverExceptionDuringQuery($this->_driver, $ex, $query, $this->resolveParams($params, $types));

//        }

        return $stmt;
    }

    private function bindTypedValues(\PDOStatement $stmt, array $params, array $types)
    {
        // Check whether parameters are positional or named. Mixing is not allowed, just like in PDO.
        if (is_int(key($params))) {
            // Positional parameters
            $typeOffset = array_key_exists(0, $types) ? -1 : 0;
            $bindIndex = 1;
            foreach ($params as $value) {
                $typeIndex = $bindIndex + $typeOffset;
                if (isset($types[$typeIndex])) {
                    $type = $types[$typeIndex];
                    //list($value, $bindingType) = $this->getBindingInfo($value, $type);
                    $bindingType = $type;
                    $stmt->bindValue($bindIndex, $value, $bindingType);
                } else {
                    $stmt->bindValue($bindIndex, $value);
                }
                ++$bindIndex;
            }
        } else {
            // Named parameters
            foreach ($params as $name => $value) {
                if (isset($types[$name])) {
                    $type = $types[$name];
                    //list($value, $bindingType) = $this->getBindingInfo($value, $type);
                    $bindingType = $type;
                    $stmt->bindValue($name, $value, $bindingType);
                } else {
                    $stmt->bindValue($name, $value);
                }
            }
        }
    }

    public function fetchAll($sql, array $params = [], array $types = [])
    {
        try {
            return $this->executeQuery($sql, $params, $types)->fetchAll();
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage() . "Query: {$sql}" . (empty($params) ? "" : " Params: " . json_encode($params)));
        }
    }

    public function fetchColumn($statement, array $params = [], $column = 0, array $types = [])
    {
        return $this->executeQuery($statement, $params, $types)->fetchColumn($column);
    }

    public function insert($tableExpression, array $data, array $types = [], string $returning = '')
    {
        if (empty($data)) {
            return $this->executeUpdate("INSERT INTO {$tableExpression}");
        }

        $columnList = [];
        $paramPlaceholders = [];
        $paramValues = [];

        foreach ($data as $columnName => $value) {
            $columnList[] = $columnName;
            $paramPlaceholders[] = '?';
            $paramValues[] = $value;
        }

        return $this->executeUpdate(
            'INSERT INTO ' . $tableExpression . ' (' . implode(', ', $columnList) . ')' .
            ' VALUES (' . implode(', ', $paramPlaceholders) . ')' . (empty($returning) ? '' : " RETURNING {$returning}"),
            $paramValues,
            $types,
            !empty($returning)
        );
    }

    public function executeUpdate($query, array $params = [], array $types = [], bool $returning = false)
    {
        try {
            if ($params || $returning) {
                //list($query, $params, $types) = SQLParserUtils::expandListParameters($query, $params, $types);

                $stmt = $this->conn->prepare($query);
                if ($types) {
                    $this->bindTypedValues($stmt, $params, $types);
                    $stmt->execute();
                } else {
                    $stmt->execute($params);
                }

                if (empty($returning)) {
                    $result = $stmt->rowCount();
                } else {
                    $result = $stmt->fetch();
                }

            } else {
                $result = $this->conn->exec($query);
            }
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage() . "Query: {$query}" . (empty($params) ? "" : " Params: " . json_encode($params)));
            //throw DBALException::driverExceptionDuringQuery($this->_driver, $ex, $query, $this->resolveParams($params, $types));
        }

        return $result;
    }

    public function execute($query, array $params = [], array $types = [], bool $returning = false)
    {
        return $this->executeUpdate($query, $params, $types, $returning);
    }

    public function update($tableExpression, array $data, array $identifier, array $types = array())
    {
        //$columnList = array();
        $set = array();
        $criteria = array();
        $paramValues = array();

        foreach ($data as $columnName => $value) {
            //$columnList[] = $columnName;
            if (is_string($columnName)) {
                $set[] = (mb_strpos($columnName, '?') === false) ? ($columnName . ' = ?') : $columnName;
                $paramValues[] = $value;
            } else {
                $set[] = $value;
            }
        }

        foreach ($identifier as $columnName => $value) {
            //$columnList[] = $columnName;
            $criteria[] = $columnName . ' = ?';
            $paramValues[] = $value;
        }

//        if (is_string(key($types))) {
//            $types = $this->extractTypeValues($columnList, $types);
//        }

        $sql = 'UPDATE ' . $tableExpression . ' SET ' . implode(', ', $set);
        if (!empty($criteria)) {
            $sql .= ' WHERE ' . implode(' AND ', $criteria);
        }

        return $this->executeUpdate($sql, $paramValues, $types);
    }

    /**
     * Prepares and executes an SQL query and returns the first row of the result
     * as an associative array.
     *
     * @param string $statement The SQL query.
     * @param array $params The query parameters.
     * @param array $types The query parameter types.
     *
     * @return array
     */
    public function fetchRow($statement, array $params = [], array $types = [])
    {
        return $this->executeQuery($statement, $params, $types)->fetch();
    }

    public function quote($str)
    {
        return $this->getConn()->quote($str);
    }

    public function startTransaction(): bool
    {
        return $this->getConn()->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->getConn()->commit();
    }

    public function rollBack(): bool
    {
        return $this->getConn()->rollBack();
    }










}