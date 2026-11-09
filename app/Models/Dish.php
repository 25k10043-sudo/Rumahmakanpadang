<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'category', 'description', 'price', 'unit', 'emoji', 'is_available', 'is_recommended', 'minimum_stock'])]
class Dish extends Model
{
    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'is_available' => 'boolean',
            'is_recommended' => 'boolean',
            'minimum_stock' => 'integer',
        ];
    }
}
