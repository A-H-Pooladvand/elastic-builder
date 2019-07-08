<?php

namespace App\Library\Elasticsearch;

use Elasticsearch\ClientBuilder;
use ONGR\ElasticsearchDSL\Search;
use ONGR\ElasticsearchDSL\Sort\FieldSort;

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
     * Get query results.
     *
     * @param  string  $model
     * @param  bool  $debug
     * @return \App\Library\Elasticsearch\Collection
     */
    public function get($model, bool $debug = null)
    {
        $search = new Search;

        $search = $this->addQueries($search);
        $search = $this->addAggregations($search);
        $search = $this->addSort($search);

        if ($this->getSource()) {
            $search->setSource($this->getSource());
        }

        $search->setSize($this->getSize());

        if ($debug) {
            return $search->toArray();
        }

        $searchResult = $this->search($search, 'divar_post');

        $this->resetProperties();

        return new Collection($searchResult);
    }

    /**
     * Selects required fields.
     *
     * @param  string|array  $fields
     * @return \App\Library\Elasticsearch\Elasticsearch
     */
    public function source($fields): self
    {
        $this->source = is_array($fields)
            ? $fields
            : func_get_args();

        return $this;
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
     * Sets size of query results.
     *
     * @param  int  $size
     * @return self
     */
    public function size(int $size): self
    {
        $this->size = $size;

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
     * Gets size of query results.
     *
     * @return mixed
     */
    private function getSize()
    {
        return $this->size;
    }

    /**
     * Add queries property container to search query.
     *
     * @param  \ONGR\ElasticsearchDSL\Search  $search
     * @return \ONGR\ElasticsearchDSL\Search
     */
    private function addQueries(Search $search): Search
    {
        foreach ($this->queries as $query) {
            $search->addQuery($query);
        }

        return $search;
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
        $client = ClientBuilder::create()->setHosts([config('elastic.host')])->build();

        $searchParams = [
            'index' => $index,
            'body' => $search->toArray(),
        ];

        return $client->search($searchParams);
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
     * Sorts given field.
     *
     * @param  string  $field
     * @param  string|null  $order
     * @param  array  $params
     * @return \App\Library\Elasticsearch\Elasticsearch
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
}
