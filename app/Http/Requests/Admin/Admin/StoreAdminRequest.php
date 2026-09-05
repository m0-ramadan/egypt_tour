<?php

namespace App\Http\Requests\Admin\Admin;


class StoreAdminRequest extends AdminRequest
{
    public function rules()
    {
        return array_merge(
            parent::rules(),
            [
                'email' => 'required|email|unique:admins,email',
                'password' => 'required|string|confirmed',
            ]
        );
    }
}
