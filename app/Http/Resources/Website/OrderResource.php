<?php

namespace App\Http\Resources\Website;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'               => $this->id,
            'order_number'     => $this->order_number,
            'status'           => $this->status,
            'status_label'     => $this->getStatusLabelAttribute(), // أو __('order.status.' . $this->status)
            'total_amount'     => $this->total_amount,
            'formatted_total'  => number_format($this->total_amount, 2) . ' ج.م',
            'items_count'      => $this->orderItems?->sum('quantity'),
            'created_at'       => $this->created_at->format('Y-m-d H:i'),
            'can_cancel'       => in_array($this->status, ['pending', 'processing']),

            // كل تفاصيل العناصر مع المنتج كامل
            'items'            => OrderItemResource::collection($this->orderItems),

            // معلومات العميل (اختياري)
            'customer' => [
                'name'  => $this->customer_name,
                'phone' => $this->customer_phone?? null,
                'email' => $this->customer_email?? null,
            ],

     
        ];
    }
}