<?php

namespace App\Http\Src\Elasticsearch;

use ONGR\ElasticsearchDSL\Query\MatchAllQuery;
use ONGR\ElasticsearchDSL\Query\Compound\BoolQuery;
use ONGR\ElasticsearchDSL\Query\FullText\MatchQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\TermQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\TermsQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\RangeQuery;
use ONGR\ElasticsearchDSL\Query\FullText\MatchPhraseQuery;
use ONGR\ElasticsearchDSL\Query\FullText\SimpleQueryStringQuery;

trait ElasticQueries
{
    /**
     * Queries container.
     *
     * @var array
     */
    private $queries = [];

    /**
     * Boolean queries container.
     *
     * @var BoolQuery $booleans
     */
    private $booleans;

    /**
     * Term Query
     *
     * @see https://github.com/ongr-io/ElasticsearchDSL/blob/master/docs/Query/TermLevel/Term.md
     *
     * @param  string  $field
     * @param  string  $value
     * @param  array  $parameters
     * @return \App\Http\Src\Elasticsearch\Elasticsearch
     */
    public function term(string $field, string $value, array $parameters = []): self
    {
        $query = new TermQuery($field, $value, $parameters);

        $this->setQueries($query);

        return $this;
    }

    /**
     * The match_phrase query analyzes the text and creates a phrase query out of the analyzed text.
     *
     * @see https://github.com/ongr-io/ElasticsearchDSL/blob/master/docs/Query/FullText/MatchPhrase.md
     *
     * @param  string  $field
     * @param  string  $query
     * @param  array  $parameters
     * @return \App\Http\Src\Elasticsearch\Elasticsearch
     */
    public function matchPhrase(string $field, string $query, array $parameters = []): self
    {
        $q = new MatchPhraseQuery($field, $query);

        $this->addParams($q, $parameters);

        $this->setQueries($q);

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
     * Terms query.
     *
     * @see https://github.com/ongr-io/ElasticsearchDSL/blob/master/docs/Query/TermLevel/Terms.md
     *
     * @param  string  $field
     * @param  array  $terms
     * @param  array  $parameters
     * @return \App\Http\Src\Elasticsearch\Elasticsearch
     */
    public function terms(string $field, iterable $terms, array $parameters = []): self
    {
        $query = new TermsQuery($field, $terms, $parameters);

        $this->setQueries($query);

        return $this;
    }

    /**
     * Limits query to given range.
     *
     * @see https://github.com/ongr-io/ElasticsearchDSL/blob/master/docs/Query/TermLevel/Range.md
     *
     * @param  string  $field
     * @param  string  $gte
     * @param  string  $lte
     * @param  string|null  $format
     * @param  array  $parameters
     * @return \App\Http\Src\Elasticsearch\ElasticQueries
     */
    public function range(string $field, string $gte, string $lte, string $format = null, array $parameters = []): self
    {
        $parameters['gte'] = $gte;
        $parameters['lte'] = $lte;
        $parameters['format'] = $format ?? 'yyyy-MM-dd HH:mm:ss';

        $query = new RangeQuery($field, $parameters);

        $this->setQueries($query);

        return $this;
    }

    /**
     * A family of match queries that accept text/numerics/dates, analyzes it, and constructs a query out of it.
     *
     * @see https://github.com/ongr-io/ElasticsearchDSL/blob/master/docs/Query/FullText/Match.md
     *
     * @param  string  $field
     * @param  string  $query
     * @param  array  $parameters
     * @return \App\Http\Src\Elasticsearch\ElasticQueries
     */
    public function match(string $field, string $query, array $parameters = []): self
    {
        $q = new MatchQuery($field, $query, $parameters);

        $this->setQueries($q);

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

    /**
     * A query that uses the SimpleQueryParser to parse its context
     *
     * @see https://github.com/ongr-io/ElasticsearchDSL/blob/master/docs/Query/FullText/SimpleQueryString.md
     *
     * @param  string  $query
     * @param  array  $fields
     * @param  array  $parameters
     * @return $this
     */
    public function simpleQueryString(string $query, array $fields, array $parameters = []): self
    {
        $parameters['fields'] = $fields;
        $parameters['default_operator'] = $parameters['default_operator'] ?? 'and';

        $q = new SimpleQueryStringQuery($query, $parameters);

        $this->setQueries($q);

        return $this;
    }

    /**
     * The clause (query) must appear in matching
     * documents and will contribute to the score.
     *
     * @see https://github.com/ongr-io/ElasticsearchDSL/blob/master/docs/Query/Compound/Bool.md
     *
     * @param  callable  $callback
     * @return $this
     */
    public function must(callable $callback): self
    {
        $this->boolQuerySetter($callback, BoolQuery::MUST);

        return $this;
    }

    /**
     * The clause (query) must not appear in the matching documents.
     * Clauses are executed in filter context meaning that
     * scoring is ignored and clauses are considered for
     * caching. Because scoring is ignored, a score of 0
     * for all documents is returned.
     *
     * @see https://github.com/ongr-io/ElasticsearchDSL/blob/master/docs/Query/Compound/Bool.md
     *
     * @param  callable  $callback
     * @return $this
     */
    public function mustNot(callable $callback): self
    {
        $this->boolQuerySetter($callback, BoolQuery::MUST_NOT);

        return $this;
    }

    /**
     * The clause (query) should appear in the matching document.
     *
     * @see https://github.com/ongr-io/ElasticsearchDSL/blob/master/docs/Query/Compound/Bool.md
     *
     * @param  callable  $callback
     * @return $this
     */
    public function should(callable $callback): self
    {
        $this->boolQuerySetter($callback, BoolQuery::SHOULD);

        return $this;
    }

    /**
     * The clause (query) must appear in matching documents.
     * However unlike must the score of the query will be
     * ignored. Filter clauses are executed in filter
     * context, meaning that scoring is ignored and
     * clauses are considered for caching.
     *
     * @see https://github.com/ongr-io/ElasticsearchDSL/blob/master/docs/Query/Compound/Bool.md
     *
     * @param  callable  $callback
     * @return $this
     */
    public function filter(callable $callback): self
    {
        $this->boolQuerySetter($callback, BoolQuery::FILTER);

        return $this;
    }

    /**
     * A wrapper to handle bool queries of must, must_not, should and filter.
     *
     * @param  callable  $callback
     * @param  string  $type
     * @return $this
     */
    private function boolQuerySetter(callable $callback, string $type): self
    {
        $result = $callback(new Elasticsearch($this->model));

        $this->setBooleans($result, $type);

        return $this;
    }

    /**
     * Set booleans container.
     *
     * @param  \App\Http\Src\Elasticsearch\Elasticsearch  $elasticsearch
     * @param  string  $type
     */
    private function setBooleans(Elasticsearch $elasticsearch, string $type): void
    {
        foreach ($elasticsearch->queries as $query) {
            $this->booleans->add($query, $type);
        }

        if (empty($elasticsearch->queries)) {
            $this->booleans->add($elasticsearch->booleans, $type);
        }

        $this->queries = [];
    }

    /**
     * Convenient way to add query parameters.
     *
     * @param  MatchQuery  $query
     * @param  array  $parameters
     */
    private function addParams($query, array $parameters): void
    {
        foreach ($parameters as $key => $parameter) {
            $query->addParameter($key, $parameter);
        }
    }
}
