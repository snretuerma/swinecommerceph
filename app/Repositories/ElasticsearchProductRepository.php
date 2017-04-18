<?php

namespace App\Repositories;

use App\Models\Product;
use App\Modesl\Breeder;
use Elasticsearch\Client;

class ElasticsearchProductRepository implements ProductRepository
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
     * Perform search on Elasticsearch search engine
     * then return a QueryBuilder
     *
     * @param   string      $query
     * @return  QueryBuilder
     */
    public function search(string $query="")
    {
        $items = $this->searchOnElastic($query);

        return $this->buildQueryBuilder($items);
    }

    /**
     * Performs search on Elasticsearch search engine
     * based on the query given by Customer
     *
     * @param   string      $query
     * @return  Array
     */
    private function searchOnElastic($query)
    {
        $instance = new Product;

        $items = $this->elasticsearch->search([
            'index' => $instance->getSearchIndex(),
            'type' => $instance->getSearchType(),
            'body' =>[
                'from' => 0,
                'size' => 100,
                'query' => [
                    'multi_match' => [
                        'fields' => ['breeder_name^5', 'province^3', 'type', 'breed'],
                        'query' => $query
                    ]
                ]
            ]
        ]);

        return $items;
    }

    /**
     * Get the product ids from the hits found
     * on Elasticsearch search engine then
     * create a QueryBuilder
     *
     * @param   array       $items
     * @return  QueryBuilder
     */
    private function buildQueryBuilder(array $items)
    {
        $productDetails = array_pluck($items['hits']['hits'], '_score', '_source.id') ? : [];
        $productsQueryBuilder = Product::whereIn('id', array_keys($productDetails));
        $productsQueryBuilder->scores = $productDetails;

        return $productsQueryBuilder;
    }
}
