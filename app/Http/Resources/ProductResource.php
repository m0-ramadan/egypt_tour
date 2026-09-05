<?php

// app/Http/Resources/ProductResource.php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'category' => $this->category,
            'brand' => $this->brand,
            'in_stock_quantity' => $this->in_stock_quantity,
            'reorder_limit' => $this->reorder_limit,
            'minimum_stock' => $this->minimum_stock,
            'location_in_stock' => $this->location_in_stock,
            'product_details' => $this->product_details,
            'purchase_price' => $this->purchase_price,
            'sale_price' => $this->sale_price,
            'discounts' => $this->discounts,
            'expected_profit_margin' => $this->expected_profit_margin,
            'supplier_name' => $this->supplier_name,
            'supplier_contact_information' => $this->supplier_contact_information,
            'expected_delivery_time' => $this->expected_delivery_time,
            'status' => $this->status,
            'date_added_to_stock' => $this->date_added_to_stock,
            'date_last_updated_to_stock' => $this->date_last_updated_to_stock,
            'expiry_date' => $this->expiry_date,
            'unit_type' => $this->unit_type,
            'currency'      => $this->currency,
            'tax_amount'      => $this->tax_amount??0,

            // Polymorphic relationship with person
            'person' => [
                'id' => $this->person_id,
                'type' => $this->person_type,
            ],

            // Relationship with images
            'images' => ImageResource::collection($this->whenLoaded('images')),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}