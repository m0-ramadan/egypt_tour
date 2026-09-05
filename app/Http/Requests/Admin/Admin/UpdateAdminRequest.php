<?php

namespace App\Http\Requests\Admin\Admin;

class UpdateAdminRequest extends AdminRequest
{
    public function rules()
    {
        return array_merge(
            parent::rules(),
            [
                'email' => 'required|email|unique:admins,email,' . $this->route()->admin->id,
                'password' => 'nullable|string|confirmed',
            ]
        );
    }
}
