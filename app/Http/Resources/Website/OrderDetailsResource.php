<?php

namespace App\Http\Resources\Website;

use App\Services\LikeCardService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderDetailsResource extends JsonResource
{
    public function toArray($request)
    {
        $likeCardService = app(LikeCardService::class);

        return [
            "id"               => $this->id,
            'order_number'     => $this->order_number,
            'status'           => $this->status,
            'status_label'     => __('order.status.' . $this->status),
            'total_amount'     => $this->total_amount,
            'formatted_total'  => number_format($this->total_amount, 2) . ' ج.م',
            'customer_name'    => $this->customer_name,
            'customer_phone'   => $this->customer_phone,
            'notes'            => $this->notes,
            'status_payment'   => $this->status_payment,
            'user'             => new UserResource(auth()->user()),
            'created_at'       => $this->created_at->translatedFormat('d M Y - h:i A'),
            'payment_method_label' => $this->payment_method_label ?: null,
            'full_address'         => $this->address ? new UserAddressResource($this->address) : null,

            'items' => $this->items->map(function ($item) use ($likeCardService) {
                return [
                    'product_name'   => $item->product->name ?? 'منتج محذوف',
                    'quantity'       => $item->quantity,
                    'price'          => $item->total_price,
                    'price_per_unit' => $item->price_per_unit,
                    'options'        => $item->selected_options ?? [],
                    'product'        => $item->product ? new ProductResource($item->product) : null,

                    'serials' => collect($item->serial_codes ?? [])->map(function ($code) use ($likeCardService) {
                        $serialCode = is_array($code) ? ($code['serial_code'] ?? null) : null;

                        try {
                            $voucherCode = $serialCode
                                ? $likeCardService->decryptSerial($serialCode)
                                : null;
                        } catch (\Throwable $e) {
                            $voucherCode = null;
                        }

                        return [
                            'id'            => is_array($code)
                                ? ($code['serial_id'] ?? $code['id'] ?? null)
                                : null,
                            'serial_number' => is_array($code)
                                ? ($code['serial_number'] ?? null)
                                : $code,
                            'serial_code'   => $serialCode,
                            'voucher_code'  => $voucherCode,
                            'valid_to'      => is_array($code)
                                ? ($code['valid_to'] ?? null)
                                : null,
                        ];
                    })->values(),

                    'provider' => $item->product && $item->product->external_id
                        ? 'like4app'
                        : 'internal',
                ];
            })->values(),
        ];
    }
}
