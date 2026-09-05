<?php

namespace App\Http\Controllers\Api\Website;

use App\Models\SocialMedia;
use Illuminate\Http\Request;
use App\Traits\ApiResponseTrait;
use App\Http\Controllers\Controller;
use App\Http\Resources\Website\SocialMediaResource;

class SocialMediaController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        return $this->successResponse(SocialMediaResource::collection(SocialMedia::all()), 'تم جلب البانر بنجاح');
    }
}
