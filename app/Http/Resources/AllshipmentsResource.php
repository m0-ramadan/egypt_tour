<?php

namespace App\Http\Resources;

use App\Models\Trip;
use Illuminate\Http\Resources\Json\JsonResource;

class AllshipmentsResource extends JsonResource
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
            'user_id' => $this->user_id ?? "",
            'shipment_company_id' => $this->shipment_company_id ?? "",
            'sender_name' => $this->sender_name ?? "",
            'sender_address' => $this->sender_address ?? "",
            'sender_email' => $this->sender_email ?? "",
            'sender_phone' => $this->sender_phone ?? "",
            'sender_country' => $this->sender_country ?? "",
            'sender_region' => $this->sender_region ?? "",
            'client_name' => $this->client_name ?? "",
            'client_address' => $this->client_address ?? "",
            'client_phone' => $this->client_phone ?? "",
            'client_phone2' => $this->client_phone2 ?? "",
            'client_country' => $this->client_country ?? "",
            'client_region' => $this->client_region ?? "",
            'code' => $this->code ?? "",
            'packaging_cost' => (string) $this->packaging_cost ?? "",
            'expense_code' => (string) $this->expense_code ?? "",
            'refund_code' => (string) $this->refund_code ?? "",

            'customs_cost' => (string) $this->customs_cost ?? "",

            'price_representative' => (float) $this->price_representative,
            'is_priced' => $this->is_priced,
            'shipment_no' => $this->shipment_no,
            'status_id' => $this->status_id,
            'image' => $this->image ? asset('public/storage/' . $this->image) : null,
            'activity' => $this->activity,
            'cancel_reason' => $this->cancel_reason,
            'cost_returend' => $this->cost_returend,
            'custom_status' => $this->custom_status,
            'price' => (int) $this->price,
            'service_id' => $this->service_id,
            'details' => $this->details,
            'weight' => (int) $this->weight,
            'cbm' => (int) $this->cbm,
            'type' => $this->type,
            'phone_received' => $this->phone_received,
            'phone_received_2' => $this->phone_received_2,
            'country_received_id' => $this->country_received_id,
            'country_region_id' => $this->country_region_id,
            'address_received' => $this->address_received,
            'type_shipments_id' => $this->service?->id,
            'describe_shipments' => $this->describe_shipments,
            'assembly_commission' => (int) $this->assembly_commission,
            'all_paper_coin' => (int) $this->all_paper_coin,
            'cost_shipment_by' => (int) $this->cost_shipment_by,
            'additional_shipping_cost' => (int) $this->additional_shipping_cost,
            'collection_commission' => (int) $this->collection_commission,
            'notes' => $this->notes,
            'currency_id' => (int) $this->effective_currency_id,

            'person_id' => $this->person_id,
            'person_type' => $this->person_type,
            'length' => (int) $this->length,
            'width' => (int) $this->width,
            'height' => (int) $this->height,
            'quantity' => $this->type == 1 ? $this->contents->sum('quantity') : (int) $this->quantity,

            'type_invoice' => $this->type_invoice,
            'receipt_location' => $this->receipt_location,
            'delivery_location' => $this->delivery_location,
            'natural_shipments' => $this->natural_shipments,
            'type_vehicle_name' => optional($this->vehicleType)->name ?? "",
            'type_vehicle_id' => $this->type_vehicle_id,
            'type_content' => $this->type_content,
            'shipping_cost' => $this->shipping_cost,
            'price_collection' => (int) $this->price_collection,
            //     'created_at'=>$this->created_at->format('Y-m-d'),
            'created_at' => optional($this->created_at)->format('Y-m-d'),
            'updated_at' => $this->updated_at->format('Y-m-d'),
            'name_received' => $this->name_received,

            'user' => [
                'id' => $this->person->id ?? null,
                'name' => $this->person->name ?? null,
                'email' => $this->person->email ?? null,
            ],
            'trip' => [
                'id' => $this->trip->id ?? null,
                'name' => $this->trip->name ?? null,
                'code' => $this->trip->code ?? null,
            ],
            'service' => [
                'id' => $this->service->id ?? null,
                'name' => $this->service->name ?? null,
            ],
            'company' => [
                'id' => $this->company->id ?? null,
                'name' => $this->company->name ?? null,
            ],
            'branches_from' => [
                'id' => $this->branchFrom->id ?? null,
                'name' => $this->branchFrom->name ?? null,
                'name_country' => $this->branchTo->country->country_ar ?? null,

            ],
            'branches_to' => [
                'id' => $this->branchTo->id ?? null,
                'name' => $this->branchTo->name ?? null,
                'name_country' => $this->branchTo->country->country_ar ?? null,


            ],
            'country_received' => [
                'id' => $this->countryReceived->id ?? null,
                'name' => $this->countryReceived->country_ar ?? null,
            ],
            'region' => [
                'id' => $this->region->id ?? null,
                'name' => $this->region->region_ar ?? null,
            ],
            'vehicle_type' => [
                'id' => $this->type_vehicle->id ?? null,
                'name' => $this->type_vehicle->name ?? null,
            ],
            // 'statuses' => $this->statuses ? $this->statuses->map(function ($status) {
            //     return [
            //         'id' => $status->id,
            //         'status_id' => $status->status_id,
            //         'created_at' => $status->created_at ? $status->created_at->format('Y-m-d H:i:s') : null,
            //     ];
            // }) : [],
            // 'images' => $this->images ? $this->images->map(function ($image) {
            //     return [
            //         'id' => $image->id,
            //         'url' => asset('storage/' . $image->path),
            //     ];
            // }) : [],
            'status_history' => $this->statusHistory ? $this->statusHistory->map(function ($history) {
                return [
                    'id' => $history->id,
                    'status_id' => $history->status_id,
                    'explain' => ($history->note && is_numeric($history->explain)) ? new NoteResource($history->note) : null,
                    'image' => $history->image ? asset('public/storage/' . $history->image) : null,
                    'created_at' => $history->created_at ? $history->created_at->format('Y-m-d H:i:s') : null,
                ];
            }) : [],
            //  'notes' => $this->notes,
            'content' => $this->contents ? $this->contents->map(function ($content) {
                // Check if the content is associated with the current trip (if any)
                $tripContent = $this->trip ? $content->tripShipmentContents->where('trip_id', $this->trip->id)->first() : null;
                return [
                    'content_id' => $content->id,
                    'content_code' => $content->code ?? '--',
                    'content_name' => $content->name ?? '--',
                    'quantity' => $tripContent ? $tripContent->quantity : ($content->quantity ?? 0),
                    'taken' => $tripContent ? $tripContent->taken : ($content->taken ?? 0),
                    'status' => $content->status_id,
                    'barcode' => $content->barcode ? asset('storage/' . $content->barcode) : null,
                    'shipment_code' => $this->code ?? '--',
                ];
            }) : [],


            'products' => $this->products_in ? $this->products_in->map(function ($product) {
                return [
                    'id' => $product->id,
                    'product_id' => $product->product->id,
                    'name' => $product->product->name,
                    'quantity' => $product->quantity,
                    'price' => $product->price,
                ];
            }) : [],
            'properties_shipment' => $this->propertiesShipment ? $this->propertiesShipment->map(function ($propertiesShipment) {
                return [
                    'id' => $propertiesShipment->id,
                    'name' => $propertiesShipment->name,
                ];
            }) : [],
        ];
    }
}
