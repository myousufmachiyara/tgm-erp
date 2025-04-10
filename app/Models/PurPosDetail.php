<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurPosDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'pur_pos_id',
        'item_id',
        'item_rate',
        'item_qty',
    ];

    public function purPos()
    {
        return $this->belongsTo(PurPos::class, 'pur_pos_id');
    }

    public function category()
    {
        return $this->belongsTo(Products::class, 'item_id');
    }

    public function product() // Corrected method name
    {
        return $this->belongsTo(Products::class, 'item_id'); // Ensure 'Product' is the correct model name
    }
}
