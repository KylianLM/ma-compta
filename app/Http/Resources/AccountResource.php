<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'   => $this->id,
            'name' => $this->name,
            'type' => [
                'value'   => $this->type->value,
                'label'   => $this->type->label(),
                'icon'    => $this->type->icon(),
                'is_debt' => $this->type->isDebtAccount(),
            ],
            'balance' => [
                'cents'               => $this->balance_cents, // Valeur brute
                'decimal'             => $this->balance_cents / 100, // Conversion explicite
                'formatted'           => $this->currency->formatAmount($this->balance_cents), // Formatage
                'effective_cents'     => $this->getEffectiveBalanceCents(),
                'effective_decimal'   => $this->getEffectiveBalanceCents() / 100,
                'effective_formatted' => $this->currency->formatAmount($this->getEffectiveBalanceCents()),
            ],
            'currency' => [
                'code'   => $this->currency->value,
                'symbol' => $this->currency->symbol(),
                'name'   => $this->currency->name(),
            ],
            'bank_name'             => $this->bank_name,
            'account_number_masked' => $this->masked_account_number,
            'is_active'             => $this->is_active,
            'description'           => $this->when(
                $request->boolean('include_sensitive'),
                $this->description
            ),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
