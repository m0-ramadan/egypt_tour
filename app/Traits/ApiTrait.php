<?php
namespace App\Traits;

trait ApiTrait
{
    public function apiResponse($code = true, $message = "", $errors = [], $data = [], $status = 200)
    {
        $array = [
            'status'  => $code,
            'message' => $message,
        ];
        if (empty($data) && !empty($errors)) {
            foreach($errors as $key => $val)
            {
                $array['errors'][$key] = $val[0];
            }
        } elseif (!empty($data) && empty($errors)) {
            $array['data'] = $data;
        } else {
            $array['data'] = $data;
            $array['errors'] = $errors;
        }

        return response()->json($array, $status);
    }
}
