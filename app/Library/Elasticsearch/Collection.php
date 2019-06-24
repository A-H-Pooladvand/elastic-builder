<?php

namespace App\Library\Elasticsearch;

use Countable;
use JsonSerializable;

class Collection implements JsonSerializable, Countable
{
    /**
     * array of data.
     *
     * @var array $items
     */
    private $items;

    public function __construct(array $items)
    {
        $this->items = $items;
    }

    /**
     * Get first level of hits.
     *
     * @return array
     */
    public function hits(): array
    {
        $this->items = $this->items['hits'];

        return $this->getItems();
    }

    /**
     * Get second level of hits (_source).
     *
     * @return array
     */
    public function source(): array
    {
        $this->items = array_map(static function ($item) {
            return $item['_source'];
        }, $this->hits()['hits']);

        return $this->getItems();
    }

    /**
     * Get collection of items as json.
     *
     * @param int $options
     * @return string
     */
    public function toJson(int $options = JSON_ERROR_NONE): string
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * Convert the collection to its string representation.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->toJson();
    }

    /**
     * Get the values of a given key.
     *
     * @param string $column
     * @return array
     */
    public function pluck(string $column): array
    {
        $columns = explode('.', $column);

        $this->source();

        foreach ($columns as $item) {
            $this->items = $this->plucker($this->getItems(), $item);
        }

        return $this->items;
    }

    /**
     * Plucks given items based on provided column.
     *
     * @param array $items
     * @param string $column
     * @return array
     */
    private function plucker(array $items, string $column): array
    {
        return array_map(static function ($item) use ($column) {
            if (! is_array($item)) {
                return $item;
            }

            $reserved = $item;

            if (array_key_exists($column, $reserved)) {
                return $item[$column];
            }

            return array_shift($reserved)[$column];
        }, $items);
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return $this->getItems();
    }

    /**
     * Count elements of an object
     *
     * @link https://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count(): int
    {
        return $this->total();
    }

    /**
     * Count elements of an object
     *
     * @return int
     */
    public function total(): int
    {
        return $this->hits()['total'];
    }

    /**
     * Returns final results.
     *
     * @return array
     */
    private function getItems(): array
    {
        return $this->items;
    }
}
