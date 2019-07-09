<?php

namespace App\Library\Elasticsearch;

use stdClass;
use Countable;
use ArrayAccess;
use Traversable;
use ArrayIterator;
use JsonSerializable;
use IteratorAggregate;
use Illuminate\Support\Arr;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;

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
     * Results array of items from Collection or Arrayable.
     *
     * @param  mixed  $items
     * @return array
     */
    protected function getArrayableItems($items): array
    {
        if (is_array($items)) {
            return $items;
        } elseif ($items instanceof self) {
            return $items->all();
        } elseif ($items instanceof Arrayable) {
            return $items->toArray();
        } elseif ($items instanceof Jsonable) {
            return json_decode($items->toJson(), true);
        } elseif ($items instanceof JsonSerializable) {
            return $items->jsonSerialize();
        } elseif ($items instanceof Traversable) {
            return iterator_to_array($items);
        }

        return (array) $items;
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
     * Determine if the given value is callable, but not a string.
     *
     * @param  mixed  $value
     * @return bool
     */
    protected function useAsCallable($value): bool
    {
        return ! is_string($value) && is_callable($value);
    }

    /**
     * Get the first item from the collection passing the given truth test.
     *
     * @param  callable|null  $callback
     * @param  mixed  $default
     * @return mixed
     */
    public function first(callable $callback = null, $default = null)
    {
        return Arr::first($this->items, $callback, $default);
    }

    /**
     * Get an operator checker callback.
     *
     * @param  string  $key
     * @param  string  $operator
     * @param  mixed  $value
     * @return \Closure
     */
    protected function operatorForWhere($key, $operator = null, $value = null): callable
    {
        if (func_num_args() === 1) {
            $value = true;

            $operator = '=';
        }

        if (func_num_args() === 2) {
            $value = $operator;

            $operator = '=';
        }

        return static function ($item) use ($key, $operator, $value) {
            $retrieved = data_get($item, $key);

            $strings = array_filter([$retrieved, $value], static function ($value) {
                return is_string($value) || (is_object($value) && method_exists($value, '__toString'));
            });

            if (count($strings) < 2 && count(array_filter([$retrieved, $value], 'is_object')) === 1) {
                return in_array($operator, ['!=', '<>', '!==']);
            }

            switch ($operator) {
                default:
                case '=':
                case '==':
                    return $retrieved === $value;
                case '!=':
                case '<>':
                    return $retrieved !== $value;
                case '<':
                    return $retrieved < $value;
                case '>':
                    return $retrieved > $value;
                case '<=':
                    return $retrieved <= $value;
                case '>=':
                    return $retrieved >= $value;
                case '===':
                    return $retrieved === $value;
                case '!==':
                    return $retrieved !== $value;
            }
        };
    }

    /**
     * Determine if an item exists in the collection.
     *
     * @param  mixed  $key
     * @param  mixed  $operator
     * @param  mixed  $value
     * @return bool
     */
    public function contains($key, $operator = null, $value = null): bool
    {
        if (func_num_args() === 1) {
            if ($this->useAsCallable($key)) {
                $placeholder = new stdClass;

                return $this->first($key, $placeholder) !== $placeholder;
            }

            return in_array($key, $this->items, true);
        }

        return $this->contains($this->operatorForWhere(...func_get_args()));
    }

    /**
     * Get all of the items in the collection.
     *
     * @return array
     */
    public function all(): array
    {
        return $this->items;
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
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return array_map(static function ($value) {
            if ($value instanceof JsonSerializable) {
                return $value->jsonSerialize();
            } elseif ($value instanceof Jsonable) {
                return json_decode($value->toJson(), true);
            } elseif ($value instanceof Arrayable) {
                return $value->toArray();
            }

            return $value;
        }, $this->items);
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
        $keys = array_keys($this->items);

        $items = array_map($callback, $this->items, $keys);

        return new static(array_combine($keys, $items));
    }

    /**
     * Key an associative array by a field or using a callback.
     *
     * @param  callable|string  $keyBy
     * @return self
     */
    public function keyBy($keyBy): self
    {
        $keyBy = $this->valueRetriever($keyBy);

        $results = [];

        foreach ($this->items as $key => $item) {
            $resolvedKey = $keyBy($item, $key);

            if (is_object($resolvedKey)) {
                $resolvedKey = (string) $resolvedKey;
            }

            $results[$resolvedKey] = $item;
        }

        return new static($results);
    }

    /**
     * Get a value retrieving callback.
     *
     * @param callable|string|null $value
     * @return callable
     */
    protected function valueRetriever($value): callable
    {
        if ($this->useAsCallable($value)) {
            return $value;
        }

        return static function ($item) use ($value) {
            return data_get($item, $value);
        };
    }

    /**
     * Get the items in the collection that are not present in the given items.
     *
     * @param  mixed  $items
     * @return self
     */
    public function diff($items): self
    {
        return new static(array_diff($this->items, $this->getArrayableItems($items)));
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
