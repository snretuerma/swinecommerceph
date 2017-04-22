<?php

namespace App\Providers;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use App\Repositories\ProductRepository;
use App\Repositories\EloquentProductRepository;
use App\Repositories\ElasticsearchProductRepository;
use Illuminate\Support\ServiceProvider;

use Validator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // For custom validator
        Validator::extend('is_current_password', 'App\Validators\CustomValidator@currentPasswordValidator');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Bind ElasticsearchProductRepository if enabled
        $this->bindSearchRepository();

        // Bind Elasticsearch client if enabled
        $this->bindSearchClient();
    }

    /**
     * Check if Elasticsearch is enabled then bind it
     *
     * @return void
     */
    private function bindSearchRepository()
    {
        $this->app->singleton(ProductRepository::class, function($app){
            if(!config('services.search.enabled')){
                return new EloquentProductRepository();
            }

            return new ElasticsearchProductRepository($app->make(Client::class));
        });
    }

    /**
     * Bind Elasticsearch client
     *
     * @return void
     */
    private function bindSearchClient()
    {
        // Check first if Elasticsearch is enabled
        if(config('services.search.enabled')){

            $client = ClientBuilder::create()
                ->setHosts(config('services.search.hosts'))
                ->build();

            $indexParameters['index'] = 'swinecart';

            // Check if swinecart index is not yet existing
            if (!$client->indices()->exists($indexParameters)){

                // Setup mapping for the suggester
                $parameters = [
                    'index' => 'swinecart',
                    'body' => [
                        'mappings' => [
                            'products' => [
                                'properties' => [

                                    'suggest' => [
                                        'type' => 'completion'
                                    ]

                                ]
                            ]
                        ]
                    ]
                ];

                $response = $client->indices()->create($parameters);
            }

            $this->app->bind(Client::class, function($app)use($client){
                return $client;
            });

        }
    }
}
