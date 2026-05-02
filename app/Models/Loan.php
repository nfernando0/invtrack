<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Model;

#[Guarded(['id'])]
class Loan extends Model
{

    protected $casts = [
        'loan_date' => 'date',
    ];
    public function items()
    {
        return $this->hasMany(LoanItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
