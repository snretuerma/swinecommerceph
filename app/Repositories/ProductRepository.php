<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Collection;

interface ProductRepository
{
    public function search(string $query = "");
}
