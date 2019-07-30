<?php

namespace App\Http\Src\Elasticsearch;

use App;
use Elasticsearch\ClientBuilder;
use ONGR\ElasticsearchDSL\Search;
use ONGR\ElasticsearchDSL\Sort\FieldSort;
use App\Http\Src\Elasticsearch\Model\Model;
use ONGR\ElasticsearchDSL\Query\Compound\BoolQuery;

/**
 * Class Elasticsearch
 *
 * @package App\Http\Src\Elasticsearch
 */
class Elasticsearch
{
    use  ElasticQueries, ElasticAggregations;

    /**
     * Contains selecting fields.
     *
     * @var array $source
     */
    private $source;

    /**
     * Size of query results.
     *
     * @var int $size
     */
    private $size;

    /**
     * Sorts given fields to given directions.
     *
     * @var array $sort
     */
    private $sort = [];

    /**
     * Abstract model class.
     *
     * @var \App\Http\Src\Elasticsearch\Model\Model $model
     */
    private $model;

    /**
     * The offset of query.
     *
     * @var int $from
     */
    private $from = 0;

    /**
     * Aggregation class.
     *
     * @var \App\Http\Src\Elasticsearch\Aggregation
     */
    private $aggregation;

    public function __construct(Model $model)
    {
        $this->model = $model;

        $this->aggregation = App::make(Aggregation::class);

        $this->booleans = new BoolQuery;
    }

    /**
     * Get query results.
     *
     * @param  bool  $debug
     * @return \App\Http\Src\Elasticsearch\Collection
     */
    public function get(bool $debug = null)
    {
        $search = new Search;

        $search = $this->addBooleans($search);
        $search = $this->addQueries($search);
        $search = $this->addAggregations($search);
        $search = $this->addSort($search);

        if ($this->getSource()) {
            $search->setSource($this->getSource());
        }

        $search->setSize($this->getSize());
        $search->setFrom($this->from);

        if ($debug) {
            return $search->toArray();
        }

        $searchResult = $this->search($search, $this->model->getIndex());

        $this->resetProperties();

        return new Collection($searchResult);
    }

    /**
     * Add queries property container to search query.
     *
     * @param  \ONGR\ElasticsearchDSL\Search  $search
     * @return \ONGR\ElasticsearchDSL\Search
     */
    private function addQueries(Search $search): Search
    {
        if (empty($this->queries)) {
            return $search;
        }

        foreach ($this->queries as $query) {
            $search->addQuery($query);
        }

        return $search;
    }

    /**
     * Add aggregations property container to search aggregation.
     *
     * @param  \ONGR\ElasticsearchDSL\Search  $search
     * @return \ONGR\ElasticsearchDSL\Search
     */
    private function addAggregations(Search $search): Search
    {
        if (empty($this->aggregations)) {
            return $search;
        }

        foreach ($this->aggregations as $aggregation) {
            $search->addAggregation($aggregation);
        }

        return $search;
    }

    /**
     * Push sort to sort container.
     *
     * @param  \ONGR\ElasticsearchDSL\Search  $search
     * @return \ONGR\ElasticsearchDSL\Search
     */
    private function addSort(Search $search): Search
    {
        foreach ($this->sort as $sort) {
            $search->addSort(
                new FieldSort($sort['field'], $sort['order'], $sort['params'])
            );
        }

        return $search;
    }

    /**
     * Get selecting fields.
     *
     * @return array
     */
    private function getSource(): ?array
    {
        return $this->source;
    }

    /**
     * Gets size of query results.
     *
     * @return mixed
     */
    private function getSize()
    {
        return $this->size;
    }

    /**
     * Search query in the given index.
     *
     * @param  \ONGR\ElasticsearchDSL\Search  $search
     * @param  string  $index
     * @return array
     */
    private function search(Search $search, string $index): array
    {
        $client = ClientBuilder::create()->setHosts([$this->model->getHost()])->build();

        $searchParams = [
            'index' => $index,
            'body' => $search->toArray(),
        ];

        return $client->search($searchParams);
    }

    /**
     * Set null to all properties.
     *
     * @return void
     */
    private function resetProperties(): void
    {
        $this->queries = null;
        $this->aggregations = null;
        $this->size = null;
        $this->source = null;
    }

    /**
     * Selects required fields.
     *
     * @param  string|array  $fields
     * @return \App\Http\Src\Elasticsearch\Elasticsearch
     */
    public function source($fields): self
    {
        $this->source = is_array($fields)
            ? $fields
            : func_get_args();

        return $this;
    }

    /**
     * Sets size of query to zero.
     *
     * @return self
     */
    public function sizeLess(): self
    {
        $this->size(0);

        return $this;
    }

    /**
     * Sets size of query results.
     *
     * @param  int  $size
     * @return self
     */
    public function size(int $size = null): self
    {
        $this->size = $size ?? 15;

        return $this;
    }

    /**
     * Sorts given field.
     *
     * @param  string  $field
     * @param  string|null  $order
     * @param  array  $params
     * @return \App\Http\Src\Elasticsearch\Elasticsearch
     */
    public function sort(string $field, string $order = null, $params = []): self
    {
        $order = $order ?? FieldSort::DESC;

        $this->sort[] = [
            'field' => $field,
            'order' => $order,
            'params' => $params,
        ];

        return $this;
    }

    /**
     * Determines offset of query.
     *
     * @param  int  $from
     */
    public function from(int $from): void
    {
        $this->from = $from;
    }

    /**
     * Indicates offset of query.
     *
     * @return int
     */
    public function getFrom(): int
    {
        return $this->from;
    }

    /**
     * Add booleans query to search query.
     *
     * @param  \ONGR\ElasticsearchDSL\Search  $search
     * @return \ONGR\ElasticsearchDSL\Search
     */
    private function addBooleans(Search $search): Search
    {
        return $search->addQuery($this->booleans);
    }
}
