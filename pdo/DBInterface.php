<?php

namespace Tandem\DB;

use Monolog\Logger;

interface DBInterface
{
    public static function getInstance($config, Logger $log = null): DBInterface;

    public function connect($config);

    public function disconnect();

    public function reconnect();

    public function getConn();

    public function setDefaultSchema($schemaName);

    public function fetchRow($statement, array $params = [], array $types = []);

    public function update($tableExpression, array $data, array $identifier, array $types = []);

    public function insert($tableExpression, array $data, array $types = [], string $returning = '');

    public function fetchColumn($statement, array $params = [], $column = 0, array $types = []);

    public function fetchAll($sql, array $params = [], array $types = []);

    public function quote($str);

    public function execute($query, array $params = [], array $types = [], bool $returning = false);

    public function startTransaction(): bool;

    public function commit(): bool;

    public function rollBack(): bool;


}