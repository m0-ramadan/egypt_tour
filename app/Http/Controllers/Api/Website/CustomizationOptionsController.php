<?php

namespace App\Http\Controllers\Api\Website;

use Illuminate\Http\Request;
use App\Models\DesignService;
use App\Models\PrintLocation;
use App\Models\PrintingMethod;
use App\Traits\ApiResponseTrait;
use App\Models\EmbroiderLocation;
use App\Http\Controllers\Controller;
use App\Http\Resources\Website\DesignServiceResource;
use App\Http\Resources\Website\PrintLocationResource;
use App\Http\Resources\Website\PrintingMethodResource;

class CustomizationOptionsController extends Controller
{
    use ApiResponseTrait;

    public function index()
    {
        // جلب الكل مرة واحدة بأقل عدد من الاستعلامات
        $printingMethods   = PrintingMethod::select('id', 'name', 'description', 'base_price')->get();
        $printLocations    = PrintLocation::where('type', 'print')
            ->select('id', 'name', 'additional_price')->get();
        $embroiderLocations = EmbroiderLocation::get();
        $designServices    = DesignService::select('id', 'name', 'price', 'description')->get();

        return $this->successResponse([
            'printing_methods'     => PrintingMethodResource::collection($printingMethods),
            'print_locations'      => PrintLocationResource::collection($printLocations),
            
            'embroider_locations'  => PrintLocationResource::collection($embroiderLocations),
            'design_services'      => DesignServiceResource::collection($designServices),
        ], 'تم جلب خيارات التخصيص بنجاح');
    }
}