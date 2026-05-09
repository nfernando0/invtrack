<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reason' => $this->reason,
            'status' => $this->status,
            'note' => $this->note,
            'loan_date' => $this->loan_date ? $this->loan_date->format('Y-m-d') : null,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'user' => [
                'id' => $this->whenLoaded('user', fn() => $this->user->id),
                'name' => $this->whenLoaded('user', fn() => $this->user->name),
                'email' => $this->whenLoaded('user', fn() => $this->user->email),
            ],
            'items' => $this->whenLoaded('items', function() {
                return $this->items->map(function($loanItem) {
                    return [
                        'id' => $loanItem->id,
                        'item_id' => $loanItem->item_id,
                        'item_name' => $loanItem->item ? $loanItem->item->name : null,
                        'quantity' => $loanItem->quantity,
                    ];
                });
            })
        ];
    }
}
