<?php

namespace App\Http\Controllers\Api\Website;

use Illuminate\Http\Request;
use App\Traits\ApiResponseTrait;
use App\Http\Controllers\Controller;
use App\Http\Resources\Website\AdsResource;
use App\Models\Ads;

class AdsController extends Controller
{
    use ApiResponseTrait;
    public function index(){
          return $this->success(AdsResource::collection(Ads::all()), ' تم استرجاع الاعلانات بنجاح ');
    }
}
