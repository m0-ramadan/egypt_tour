<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $language = $request->header('lang', 'ar');

        // Define bilingual mappings for type_task
        $typeTaskMap = [
            'ar' => [
                1 => 'تجميع',
                2 => 'تسوية',
                3 => 'تسليم المرتجعات',
                'default' => 'غير معروف'
            ],
            'en' => [
                1 => 'Collection',
                2 => 'Settlement',
                3 => 'Return Delivery',
                'default' => 'Unknown'
            ]
        ];

        // Define bilingual mappings for duration
        $durationMap = [
            'ar' => [
                1 => 'شهر',
                2 => 'شهرين',
                3 => 'ثلاثة أشهر',
                'default' => 'غير معروف'
            ],
            'en' => [
                1 => 'One Month',
                2 => 'Two Months',
                3 => 'Three Months',
                'default' => 'Unknown'
            ]
        ];

        // Get type_task text based on language
        $typeTaskText = isset($typeTaskMap[$language][$this->type_task])
            ? $typeTaskMap[$language][$this->type_task]
            : $typeTaskMap[$language]['default'];

        // Get duration text based on language
        $durationText = isset($durationMap[$language][$this->duration])
            ? $durationMap[$language][$this->duration]
            : $durationMap[$language]['default'];

        return [
            'id' => $this->id,
            'type_task' => $typeTaskText,
            'date_implementation' => $this->date_implementation,
            'duration' => $this->duration,
        ];
    }
}