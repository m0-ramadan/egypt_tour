<?php

namespace App\Http\Controllers\Api\Website;

use App\Models\Language;
use Illuminate\Http\Request;
use App\Traits\ApiResponseTrait;
use App\Http\Controllers\Controller;

class LanguageController extends Controller
{
    use ApiResponseTrait;
    public function index(Request $request)
    {
        $languages = Language::select('id', 'code', 'name')->active()->get();
        return $this->successResponse($languages);
    }
}
