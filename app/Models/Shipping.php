<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shipping extends Model
{
    protected $fillable = ['courier', 'type', 'region', 'price', 'region_fee', 'status'];

    protected $casts = [
        'price' => 'float',
        'region_fee' => 'float',
    ];

    public function getFinalPriceAttribute()
    {
        return (float) $this->price + (float) $this->region_fee;
    }

    public function getDisplayNameAttribute()
    {
        return collect([$this->courier, $this->type, $this->region])
            ->filter()
            ->implode(' - ');
    }
}
