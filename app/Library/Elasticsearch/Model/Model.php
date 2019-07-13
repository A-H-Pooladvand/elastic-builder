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

    /**
     * Index getter.
     *
     * @return string
     */
    public function getIndex(): string
    {
        return $this->index;
    }

    /**
     * Connection setter.
     *
     * @param  string|null  $connection
     */
    private function setConnection(string $connection = null): void
    {
        $this->connection = $connection;
    }

    /**
     * Connection getter.
     *
     * @return string
     */
    protected function getConnection(): string
    {
        if (null === $this->connection) {
            $this->setConnection(env('E_CONNECTION', 'elasticsearch'));

            return $this->connection;
        }

        return $this->connection;
    }

    /**
     * Determines host and port of elasticsearch.
     *
     * @return string
     */
    public function getHost(): string
    {
        $host = config("database.connections.{$this->getConnection()}.host");
        $port = config("database.connections.{$this->getConnection()}.port");

        return $host.':'.$port;
    }

    /**
     * Fires when calling method which doesnt exists.
     *
     * @param $method
     * @param $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return (new Elasticsearch(new static))->$method(...$parameters);
    }

    /**
     * Fires when calling static method which doesnt exists.
     *
     * @param $method
     * @param $parameters
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        return (new static)->$method(...$parameters);
    }
}
