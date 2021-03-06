<?php

namespace App\Filters\Contractors;

use EloquentFilter\ModelFilter;

class Contractors extends ModelFilter
{
    /**
     * Related Models that have ModelFilters as well as the method on the ModelFilter
     * As [relatedModel => [input_key1, input_key2]].
     *
     * @var array
     */
    public $relations = [];

    public function search($query)
    {
        return $this->where('name', 'LIKE', '%' . $query . '%')->orWhere('email', 'LIKE', '%' . $query . '%');
    }
}
