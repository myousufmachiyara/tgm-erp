<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeadOfAccounts extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    // Define the relationship with SubHeadOfAccounts
    public function subHeadOfAccounts()
    {
        return $this->hasMany(SubHeadOfAccounts::class, 'hoa_id', 'id');
    }
}
