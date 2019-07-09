<?php

namespace App\Library\Elasticsearch;

use ONGR\ElasticsearchDSL\Query\MatchAllQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\TermQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\TermsQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\RangeQuery;

trait ElasticQueries
{
    private $queries = [];

    /**
     * Term Query
     *
     * @see https://github.com/ongr-io/ElasticsearchDSL/blob/master/docs/Query/TermLevel/Term.md
     *
     * @param  string  $field
     * @param  string  $value
     * @param  array  $parameters
     * @return \App\Library\Elasticsearch\Elasticsearch
     */
    public function term(string $field, string $value, array $parameters = []): self
    {
        $query = new TermQuery($field, $value, $parameters);

        $this->setQueries($query);

        return $this;
    }

    /**
     * Terms query.
     *
     * @see https://github.com/ongr-io/ElasticsearchDSL/blob/master/docs/Query/TermLevel/Terms.md
     *
     * @param  string  $field
     * @param  array  $terms
     * @param  array  $parameters
     * @return \App\Library\Elasticsearch\Elasticsearch
     */
    public function terms(string $field, iterable $terms, array $parameters = []): self
    {
        $query = new TermsQuery($field, $terms, $parameters);

        $this->setQueries($query);

        return $this;
    }

    /**
     * Push an query to queries container.
     *
     * @param $query
     */
    private function setQueries($query): void
    {
        $this->queries[] = $query;
    }

    /**
     * Limits query to given range.
     *
     * @see https://github.com/ongr-io/ElasticsearchDSL/blob/master/docs/Query/TermLevel/Range.md
     *
     * @param  string  $field
     * @param  string  $gte
     * @param  string  $lte
     * @param  array  $parameters
     * @return \App\Library\Elasticsearch\ElasticQueries
     */
    public function range(string $field, string $gte, string $lte, array $parameters = []): self
    {
        $parameters['gte'] = $gte;
        $parameters['lte'] = $lte;

        $query = new RangeQuery($field, $parameters);

        $this->setQueries($query);

        return $this;
    }

    /**
     * A query that matches all documents
     *
     * @see https://github.com/ongr-io/ElasticsearchDSL/blob/master/docs/Query/MatchAll.md
     *
     * @return self
     */
    public function matchAll(): self
    {
        $query = new MatchAllQuery();

        $this->setQueries($query);

        return $this;
    }
}
