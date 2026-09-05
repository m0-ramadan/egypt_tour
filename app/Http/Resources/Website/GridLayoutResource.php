<?php

namespace App\Http\Resources\Website;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GridLayoutResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'grid_type'       => $this->grid_type,
            'desktop_columns' => $this->desktop_columns,
            'tablet_columns'  => $this->tablet_columns,
            'mobile_columns'  => $this->mobile_columns,
            'row_gap'         => $this->row_gap,
            'column_gap'      => $this->column_gap,
        ];
    }
}