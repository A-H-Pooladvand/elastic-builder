<?php

namespace App\Library\Elasticsearch;

use ONGR\ElasticsearchDSL\Aggregation\Bucketing\DateHistogramAggregation;
use ONGR\ElasticsearchDSL\Aggregation\Metric\AvgAggregation;

class Aggregation
{
    /**
     * Average aggregation.
     *
     * @see https://github.com/ongr-io/ElasticsearchDSL/blob/master/docs/Aggregation/Metric/Avg.md
     *
     * @param string $name
     * @param string|null $field
     * @param string|null $script
     * @return \ONGR\ElasticsearchDSL\Aggregation\Metric\AvgAggregation
     */
    public function avg(string $name, string $field = null, string $script = null): AvgAggregation
    {
        return new AvgAggregation($name, $field, $script);
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
     * @return \ONGR\ElasticsearchDSL\Aggregation\Bucketing\DateHistogramAggregation
     */
    public function dateHistogram(string $name, string $field = null, string $interval = null, string $format = null): DateHistogramAggregation
    {
        return new DateHistogramAggregation($name, $field, $interval, $format);
    }
}
