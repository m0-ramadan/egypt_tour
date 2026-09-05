<?php

namespace App\Http\Controllers\Api\Website;

use App\Models\Faq;
use Illuminate\Http\Request;
use App\Traits\ApiResponseTrait;
use App\Http\Controllers\Controller;
use App\Http\Resources\Website\FaqResource;

class FaqController extends Controller
{
    use ApiResponseTrait;
    public function index(Request $request)
    {
        $faqs = Faq::orderBy('sort_order', 'asc')->get();
        return $this->success( FaqResource::collection($faqs), 'تم جلب بيانات بنجاح');
    }


}
