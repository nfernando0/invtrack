<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Guarded(['id'])]
class LoanItem extends Model
{
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Relasi ke model Loan (Opsional tapi disarankan)
     */
    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }
}
