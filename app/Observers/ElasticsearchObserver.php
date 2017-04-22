<?php

namespace App\Observers;

use Elasticsearch\Client;
use App\Jobs\IndexProductToElasticsearch;
use App\Jobs\DeleteProductFromElasticsearch;

class ElasticsearchObserver
{
    private $elasticsearch;

    /**
     * Setup Elasticsearch client
     *
     * @param   Client      $client
     */
    public function __construct(Client $client)
    {
        $this->elasticsearch = $client;
    }

    /**
     * Index certain attributes of the Product model
     * to the Elasticsearch search engine
     *
     * @param   Product     $model
     * @return  void
     */
    public function saved($model)
    {
        $modelDetails = [];
        $modelDetails['index'] = $model->getSearchIndex();
        $modelDetails['type'] = $model->getSearchType();
        $modelDetails['id'] = $model->id;
        $modelDetails['body'] = $model->toSearchArray();

        // Queue IndexProductToElasticsearch job
        dispatch(new IndexProductToElasticsearch($modelDetails));
    }

    /**
     * Delete document in Elasticsearch search engine
     *
     * @param   Product     $model
     * @return  void
     */
    public function deleted($model)
    {
        $modelDetails = [];
        $modelDetails['index'] = $model->getSearchIndex();
        $modelDetails['type'] = $model->getSearchType();
        $modelDetails['id'] = $model->id;

        // Queue IndexProductToElasticsearch job
        dispatch(new DeleteProductFromElasticsearch($modelDetails));
    }
}
