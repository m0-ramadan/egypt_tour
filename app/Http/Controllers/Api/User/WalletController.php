<?php

namespace App\Http\Controllers\Api\User;

use App\Models\User;
use App\Models\Driver;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use Illuminate\Support\Facades\Validator;
use App\Services\Wallet\UserWalletService;
use App\Traits\ApiResponseTrait;

class WalletController extends Controller
{
    use ApiResponseTrait;

    private UserWalletService $walletService;

    public function __construct(UserWalletService $walletService)
    {
        $this->walletService = $walletService;
        $this->middleware('auth:sanctum');
    }

    /**
     * Get wallet balance
     */
    public function getBalance(Request $request)
    {
        $user = $request->user();

        try {
            $balance = $this->walletService->getBalance($user);

            return $this->successResponse($balance, 'تم الحصول على رصيد المحفظة بنجاح');
        } catch (\Exception $e) {
            return $this->errorResponse('فشل في الحصول على رصيد المحفظة', 500);
        }
    }

    /**
     * Initiate deposit
     */
    public function initiateDeposit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:10|max:50000',
            'payment_method' => 'sometimes|in:paymob,tamara,tabby'
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors(), 'خطأ في البيانات المرسلة');
        }

        $user = $request->user();

        try {
            $result = $this->walletService->initiateDeposit($user, $request->amount, [
                'payment_method' => $request->payment_method ?? 'paymob'
            ]);

            return $this->successResponse([
                'payment_url' => $result['payment_url'],
                'order_id' => $result['order_id'],
                'amount' => $request->amount
            ], 'تم إنشاء طلب الدفع بنجاح');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * Withdraw funds
     */
    public function withdraw(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:50',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'description' => 'nullable|string|max:255',
            'payment_identifier' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors(), 'خطأ في البيانات المرسلة');
        }

        $user = $request->user();

        try {
            $entry = $this->walletService->withdraw($user, $request->amount, [
                'bank_account_id' => $request->bank_account_id,
                'description' => $request->description,
                'payment_identifier' => $request->payment_identifier
            ]);

            return $this->successResponse([
                'entry' => [
                    'id' => $entry->id,
                    'reference' => $entry->reference,
                    'amount' => $entry->amount,
                    'status' => $entry->status
                ]
            ], 'تم تقديم طلب السحب بنجاح');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }


    /**
     * Transfer funds
     */
    public function transfer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1',
            'to_user_id' => 'required_without:to_driver_id|exists:users,id',
            'to_driver_id' => 'required_without:to_user_id|exists:drivers,id',
            'description' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors(), 'خطأ في البيانات المرسلة');
        }

        $user = $request->user();

        try {
            // Determine recipient
            if ($request->has('to_user_id')) {
                $toUser = User::findOrFail($request->to_user_id);
                $result = $this->walletService->transfer($user, $toUser, $request->amount, [
                    'description' => $request->description
                ]);
            } else {
                return $this->errorResponse("لا يوجد مستخدم بهذا الاسم", 404);
            }

            return $this->successResponse([
                'transfer' => [
                    'id' => $result['transfer_id'],
                    'debit_entry_id' => $result['debit_entry']->id,
                    'credit_entry_id' => $result['credit_entry']->id,
                    'amount' => $request->amount
                ]
            ], 'تم التحويل بنجاح');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * Get transaction history
     */
    public function getTransactions(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'nullable|string',
            'status' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'limit' => 'nullable|integer|min:1|max:100'
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors(), 'خطأ في البيانات المرسلة');
        }

        $user = $request->user();

        try {
            $entries = $this->walletService->getTransactionHistory($user, [
                'type' => $request->type,
                'status' => $request->status,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'limit' => $request->limit ?? 20
            ]);

            return $this->paginated($entries, 'تم جلب سجل المعاملات بنجاح');
        } catch (\Exception $e) {
            return $this->errorResponse('فشل في الحصول على سجل المعاملات', 500);
        }
    }

    // public function getBanks()
    // {
    //     return BankAccount::where('is_active', true)->select(['id', 'name', 'image', 'type'])->get();
    // }
}
