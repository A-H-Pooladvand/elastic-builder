<?php

namespace App\Library\Elasticsearch;

use App;
use Closure;
use ONGR\ElasticsearchDSL\Aggregation\Bucketing\RangeAggregation;
use ONGR\ElasticsearchDSL\Aggregation\Bucketing\TermsAggregation;
use ONGR\ElasticsearchDSL\Aggregation\Bucketing\DateHistogramAggregation;

trait ElasticAggregations
{
    private $aggregations = [];

    /**
     * Terms aggregations.
     *
     * @see https://github.com/ongr-io/ElasticsearchDSL/blob/master/docs/Aggregation/Bucketing/Terms.md
     *
     * @param string $name
     * @param string|null $field
     * @param null $script
     * @return self
     */
    public function termsAggregation(string $name, string $field = null, $script = null): self
    {
        $aggregation = new TermsAggregation($name, $field, $script);

        $this->setAggregations($aggregation);

        return $this;
    }

    /**
     * Date histogram aggregation.
     *
     * @see https://github.com/ongr-io/ElasticsearchDSL/blob/master/docs/Aggregation/Bucketing/DateHistogram.md
     *
     * @param string $name
     * @param string|null $field
     * @param string|null $interval
     * @param string|null $format
     * @param \Closure|null $callable
     * @return \App\Library\Elasticsearch\ElasticAggregations
     */
    public function dateHistogram(string $name, string $field = null, string $interval = null, string $format = null , Closure $callable = null): self
    {
        $aggregation = new DateHistogramAggregation($name, $field, $interval, $format);

        if (isset($callable)) {
            $aggregation = $callable($aggregation, $this->aggregation);
        }

        $this->setAggregations($aggregation);

        return $this;
    }

    /**
     * Range aggregation.
     *
     * @see https://github.com/ongr-io/ElasticsearchDSL/blob/master/docs/Aggregation/Bucketing/Range.md
     *
     * @param string $name
     * @param string|null $field
     * @param array $ranges
     * @param bool $keyed
     * @param \Closure|null $callable
     * @return $this
     */
    public function rangeAggregation(string $name, string $field = null,array $ranges = [],bool $keyed = false, Closure $callable = null)
    {
        // Amirhossein: This range query may not be as expected
        // its just a left alone code please visit @see link and make the query fully supported.
        $aggregation = new RangeAggregation($name, $field, $ranges, $keyed);

        if (isset($callable)) {
            $aggregation = $callable($aggregation, $this->aggregation);
        }

        $this->setAggregations($aggregation);

        return $this;
    }

    /**
     * Set aggregations container.
     *
     * @param $aggregation
     */
    private function setAggregations($aggregation): void
    {
        $this->aggregations[] = $aggregation;
    }
}
