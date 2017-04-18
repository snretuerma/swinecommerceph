<?php

namespace App\Observers;

use App\Models\Breed;
use App\Models\Breeder;
use App\Models\FarmAddress;

trait Searchable
{
    public static function bootSearchable()
    {
        // Check if Elasticsearch is enabled
        if(config('services.search.enabled')){
            static::observe(ElasticsearchObserver::class);
        }
    }

    /**
     * Default index name
     *
     * @return  string
     */
    public function getSearchIndex()
    {
        return 'swinecart';
    }

    /**
     * Get table name of Product model
     *
     * @return  string
     */
    public function getSearchType()
    {
        if(property_exists($this, 'useSearchType')){
            return $this->useSearchType;
        }

        return $this->getTable();
    }

    // Customized array to be indexed in Elasticsearch search engine
    /**
     * Create a customized array to be indexed in
     * the Elasticsearch search engine
     *
     * @return  Array
     */
    public function toSearchArray()
    {
        return [
            'id' => $this->id,
            'breeder_name' => Breeder::find($this->breeder_id)->users()->first()->name,
            'province' => FarmAddress::find($this->farm_from_id)->province,
            'type' => $this->type,
            'breed' => Breed::find($this->breed_id)->name,
            'suggest' => [
                [
                    'input' => Breeder::find($this->breeder_id)->users()->first()->name,
                    'weight' => 12,
                ],
                [
                    'input' => FarmAddress::find($this->farm_from_id)->province,
                    'weight' => 9,
                ],
                [
                    'input' => Breed::find($this->breed_id)->name,
                    'weight' => 5,
                ],
                [
                    'input' => $this->type,
                    'weight' => 3,
                ]
            ],
            'output' => [
                $this->type . " " . Breed::find($this->breed_id)->name,
                Breeder::find($this->breeder_id)->users()->first()->name . " " . FarmAddress::find($this->farm_from_id)->province . " " . $this->type . " " . Breed::find($this->breed_id)->name 
            ]
        ];
    }

}
