<?php

namespace App\Http\Controllers\Api\Website;

use App\Models\StaticPage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Website\StaticPageResource;
use App\Traits\ApiResponseTrait;

class StaticPagesController extends Controller
{
    use ApiResponseTrait;
    public function index($slug)
    {

        $page = StaticPage::where('slug', $slug)->first();
 
        return $this->success(new StaticPageResource($page), 'تم جلب بيانات الصفحة بنجاح');
    }
}
