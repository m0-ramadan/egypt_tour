<?php

namespace App\Traits;

trait ApiResponseTrait
{
    /**
     * ✅ استجابة نجاح عامة
     */
    protected function success($data = null, string $message = 'تمت العملية بنجاح', int $code = 200)
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * ❌ استجابة خطأ عامة
     */
    protected function error(string $message = 'حدث خطأ أثناء تنفيذ العملية', int $code = 500, $errors = null)
    {
        $response = [
            'status' => false,
            'message' => $message,
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    /**
     * 📦 استجابة بيانات بدون رسالة
     */
    protected function data($data, int $code = 200)
    {
        return response()->json([
            'status' => true,
            'data' => $data,
        ], $code);
    }

    /**
     * ⚠️ استجابة خطأ تحقق (Validation)
     */
    protected function validationError($errors, string $message = 'خطأ في البيانات المرسلة', int $code = 422)
    {
        return $this->error($message, $code, $errors);
    }

    /**
     * 📄 استجابة البيانات مع صفحات (Pagination)
     */
    protected function paginated($paginator, string $message = 'تم جلب البيانات بنجاح', int $code = 200)
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ], $code);
    }

     // ✅ استجابة نجاح
    protected function successResponse($data = null, $message = 'تم بنجاح', $status = 200)
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    // ⚠️ استجابة خطأ
    protected function errorResponse($message = 'حدث خطأ غير متوقع', $status = 500, $code = null)
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'error_code' => $code,
        ], $status);
    }
}
