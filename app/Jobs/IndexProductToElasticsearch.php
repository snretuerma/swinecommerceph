<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use Elasticsearch\ClientBuilder;

class IndexProductToElasticsearch implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $modelDetails;

    /**
     * Create a new job instance.
     *
     * @param  Array    $modelDetails
     * @return void
     */
    public function __construct($modelDetails)
    {
        $this->modelDetails = $modelDetails;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $client = ClientBuilder::create()
            ->setHosts(config('services.search.hosts'))
            ->build();

        $client->index([
            'index' => $this->modelDetails['index'],
            'type' => $this->modelDetails['type'],
            'id' => $this->modelDetails['id'],
            'body' => $this->modelDetails['body']
        ]);
    }
}
