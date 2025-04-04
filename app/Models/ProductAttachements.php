<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductAttachements extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'image_path',
    ];

    /**
     * Relationship to the Product model
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
