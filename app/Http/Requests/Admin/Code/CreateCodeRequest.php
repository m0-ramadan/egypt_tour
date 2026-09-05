<?php

namespace App\Http\Requests\Admin\Code;

use Illuminate\Foundation\Http\FormRequest;

class CreateCodeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'code'=> 'required|unique:codes,code',
            'company'=>'required',
            'discount'=>'required',
            'type'=>'required',
            'from'=>'required',
            'to'=>'required',
            'time'=>'required',
        ];
    }
}
