<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WalletDetailResource extends JsonResource
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
            'amount' => $this->amount,
            'currency_id' => $this->currency_id,
           //'indebtedness' => $this->indebtedness,
          //  'walletHistories'=>WalletHistoryResource::collection($this->walletHistories)
        ];
    }
}