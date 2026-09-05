<?php

namespace App\Http\Resources\Website;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactUsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'first_name' => $this->first_name,
            'last_name'  => $this->last_name,
            'full_name'  => trim($this->first_name . ' ' . $this->last_name),

            'phone'      => $this->phone,
            'email'      => $this->email,
            'company'    => $this->company,
            'message'    => $this->message,

            // ✅ لو عندك status في الجدول
            'status'       => $this->status ?? 'pending',
            'status_label' => $this->getStatusLabel(),

            // ✅ عدد الردود
            'replies_count' => $this->whenLoaded('replies', function () {
                return $this->replies->count();
            }, fn() => $this->replies()->count()),

            // ✅ آخر رد
            'last_reply' => [
                'message'     => $this->lastReply?->message,
                'sender_type' => $this->lastReply?->sender_type,
                'created_at'  => optional($this->lastReply?->created_at)->format('Y-m-d H:i'),
            ],

            'created_at' => optional($this->created_at)->format('Y-m-d H:i'),
        ];
    }

    private function getStatusLabel(): string
    {
        return match ($this->status) {
            'pending'     => 'معلقة',
            'in_progress' => 'قيد المعالجة',
            'resolved'    => 'تم الحل',
            'closed'      => 'مغلقة',
            default       => 'معلقة',
        };
    }
}
