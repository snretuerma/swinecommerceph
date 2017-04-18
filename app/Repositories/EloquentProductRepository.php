<?php

namespace App\Repositories;

use App\Models\Product;
use App\Models\Breeder;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class EloquentProductRepository implements ProductRepository
{
    public function search(string $query = "")
    {
        return Product::where(function($q) use($query){
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('type', 'like', "%{$query}%");
            })
            ->whereIn('status', ['displayed', 'requested'])
            ->where('quantity', '!=', 0);

    }
}
