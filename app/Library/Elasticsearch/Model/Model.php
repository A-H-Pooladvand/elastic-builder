<?php

namespace App\Http\Src\Elasticsearch\Model;

use App\Http\Src\Elasticsearch\Elasticsearch;

/**
 * Class Model
 *
 * @mixin \App\Http\Src\Elasticsearch\Elasticsearch
 * @package App\Http\Src\Elasticsearch\Model
 */
abstract class Model
{
    protected $index;

    protected $connection;

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
     * Connection setter.
     *
     * @param  string|null  $connection
     */
    private function setConnection(string $connection = null): void
    {
        $this->connection = $connection;
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
}
