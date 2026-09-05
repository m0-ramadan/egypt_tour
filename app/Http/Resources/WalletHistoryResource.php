<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WalletHistoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'transfer_admin_name' =>$this->transfer->name,
            'type' => $this->type,
            'amount' => $this->amount,
            'transaction_date' => $this->transaction_date->toDateTimeString(),
            'commission' => $this->commission,
            'currency_id' => $this->walletDetail->currency_id,
        ];
    }
}