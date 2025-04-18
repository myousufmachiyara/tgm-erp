<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductAttributesValues extends Model
{
    use HasFactory;

    protected $fillable = ['product_attribute_id', 'value'];

    /**
     * Relationship with ProductAttribute.
     */
    public function attribute()
    {
        return $this->belongsTo(ProductAttributes::class);
    }
}
