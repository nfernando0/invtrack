<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Model;

#[Guarded(['id'])]
class Item extends Model
{
    protected $fillable = [
        'name',
        'stock',
        'image',
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function loanItems()
    {
        return $this->hasMany(LoanItem::class);
    }
}
