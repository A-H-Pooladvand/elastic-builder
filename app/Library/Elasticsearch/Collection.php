<?php

namespace App\Library\Elasticsearch;

use Countable;
use ArrayAccess;
use Traversable;
use ArrayIterator;
use JsonSerializable;
use IteratorAggregate;

class Collection implements JsonSerializable, Countable, ArrayAccess, IteratorAggregate
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
     * @return self
     */
    public function hits(): self
    {
        $this->items = $this->items['hits'];

        return new static($this->items);
    }

    /**
     * Get second level of hits (_source).
     *
     * @return self
     */
    public function source(): self
    {
        $this->hits();

        $this->items = array_map(static function ($item) {
            return $item['_source'];
        }, $this->items['hits']);

        return new static($this->items);
    }

    /**
     * Get collection of items as json.
     *
     * @param  int  $options
     * @return string
     */
    public function toJson(int $options = JSON_ERROR_NONE): string
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * Get the values of a given key.
     *
     * @param  string  $column
     * @return self
     */
    public function pluck(string $column): self
    {
        $columns = explode('.', $column);

        if (! empty($this->items['_shards'])) {
            $this->source();
        }

        foreach ($columns as $item) {
            $this->items = $this->plucker($this->items, $item);
        }

        return new static($this->items);
    }

    /**
     * Plucks given items based on provided column.
     *
     * @param  array  $items
     * @param  string  $column
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
        return $this->items;
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
     * Get aggregation bucket.
     *
     * @param  mixed  ...$index
     * @return \App\Library\Elasticsearch\Collection
     */
    public function aggregations(...$index): self
    {
        if (empty($index)) {
            $this->items = $this->items['aggregations'];

            return new static($this->items);
        }

        $items = [];
        array_map(function (string $index) use (&$items) {
            $items[$index] = array_map(static function ($item) {
                return [
                    'title' => $item['key'],
                    'count' => $item['doc_count'],
                ];
            }, $this->items['aggregations'][$index]['buckets']);
        }, $index);

        $this->items = count($index) > 1 ? $items : reset($items);

        return new static($this->items);
    }

    /**
     * Run a map over each of the items.
     *
     * @param  callable  $callback
     * @return self
     */
    public function map(callable $callback): self
    {
        $this->items = array_map(static function ($item) use ($callback) {
            return $callback($item);
        }, $this->items);

        return new static($this->items);
    }

    /**
     * Returns final results.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->items;
    }

    /**
     * Determine if an item exists at an offset.
     *
     * @param  mixed  $key
     * @return bool
     */
    public function offsetExists($key): bool
    {
        return array_key_exists($key, $this->items);
    }

    /**
     * Remove an item from the collection by key.
     *
     * @param  string|array  $keys
     * @return self
     */
    public function forget($keys): self
    {
        foreach ((array) $keys as $key) {
            $this->offsetUnset($key);
        }

        return new static($this->items);
    }

    /**
     * Reset the keys on the underlying array.
     *
     * @return self
     */
    public function values(): self
    {
        return new static(array_values($this->items));
    }

    /**
     * Get an item at a given offset.
     *
     * @param  mixed  $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->items[$key];
    }

    /**
     * Set the item at a given offset.
     *
     * @param  mixed  $key
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($key, $value): void
    {
        if (null === $key) {
            $this->items[] = $value;
        } else {
            $this->items[$key] = $value;
        }
    }

    /**
     * Unset the item at a given offset.
     *
     * @param  string  $key
     * @return void
     */
    public function offsetUnset($key): void
    {
        unset($this->items[$key]);
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

    public function __set($name, $value)
    {
        // Todo implement method.
        dd('Please implement the magic method.');
    }

    public function __isset($name)
    {
        // Todo implement method.
        dd('Please implement the magic method.');
    }

    public function __get($name)
    {
        if (is_array($this->items[$name])) {
            return $this->items[$name];
        }

        return $this->items[$name];
    }

    /**
     * Get an iterator for the items.
     *
     * @return \ArrayIterator
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }
}
