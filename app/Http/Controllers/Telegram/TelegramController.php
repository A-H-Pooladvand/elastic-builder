<?php

namespace App\Http\Controllers\Telegram;

use App\TelegramChannelMessage;
use App\Http\Controllers\Controller;
use App\Library\Elasticsearch\Aggregation;
use App\Library\Elasticsearch\Elasticsearch;
use ONGR\ElasticsearchDSL\Aggregation\Bucketing\DateHistogramAggregation;

class TelegramController extends Controller
{
    /**
     * Elasticsearch instance.
     *
     * @var \App\Library\Elasticsearch\Elasticsearch
     */
    private $search;

    public function __construct(Elasticsearch $search)
    {
        $this->search = $search;
    }

    public function index()
    {
        return $this->search
            //->sizeLess()
            ->term('to_id.channel_id', 1062906179)
            ->range('date', '2019-06-10', '2019-06-24', ['format' => 'yyyy-MM-dd'])
            ->dateHistogram('monthly_views', 'date', 'day', null, static function (DateHistogramAggregation $dateHistogram, Aggregation $aggregation) {
                return $dateHistogram->addAggregation($aggregation->avg('avg_views', 'views'));
            })->get(TelegramChannelMessage::class)->aggregations('monthly_views');
    }
}
