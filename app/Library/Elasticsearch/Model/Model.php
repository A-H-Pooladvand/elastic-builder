<?php

namespace App\Library\Elasticsearch\Model;

use App\Library\Elasticsearch\Elasticsearch;

/**
 * Class Model
 *
 * @mixin \App\Library\Elasticsearch\Elasticsearch
 * @package App\Library\Elasticsearch\Model
 */
abstract class Model
{
    protected $index;

    protected $connection;

    public function __call($method, $parameters)
    {
        return (new Elasticsearch(new static))->$method(...$parameters);
    }

    public static function __callStatic($method, $parameters)
    {
        return (new static)->$method(...$parameters);
    }

    public function getIndex(): string
    {
        return $this->index;
    }

    private function setConnection(string $connection = null): void
    {
        $this->connection = $connection;
    }

    protected function getConnection(): string
    {
        if (null === $this->connection) {
            $this->setConnection(env('E_CONNECTION', 'elasticsearch'));

            return $this->connection;
        }

        return $this->connection;
    }

    public function getHost(): string
    {
        $host = config("database.connections.{$this->getConnection()}.host");
        $port = config("database.connections.{$this->getConnection()}.port");

        return $host.':'.$port;
    }
}
