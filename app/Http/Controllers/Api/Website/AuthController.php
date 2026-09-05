<?php

namespace App\Http\Controllers\Api\Website;

use App\Http\Controllers\Controller;
use App\Http\Requests\Website\Auth\ResetPasswordRequest;
use App\Http\Requests\Website\Auth\SendOtpRequest;
use App\Http\Requests\Website\Auth\VerifyOtpRequest;
use App\Http\Requests\Website\ChangePasswordRequest;
use App\Http\Requests\Website\LoginRequest;
use App\Http\Requests\Website\RegisterRequest;
use App\Http\Requests\Website\SocialMediaLoginRequest;
use App\Http\Resources\Website\UserResource;
use App\Mail\Website\OtpMail;
use App\Models\OtpVerification;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    use ApiResponseTrait;

    /**
     * 🔹 تسجيل مستخدم جديد
     */
    public function register(RegisterRequest $request)
    {
        try {
            $user = User::create([
                'name'        => $request->name,
                'email'       => $request->email,
                'phone'       => $request->phone,
                'password'    => Hash::make($request->password),
                'google_id'   => $request->google_id,
                'facebook_id' => $request->facebook_id,
                'apple_id'    => $request->apple_id,
            ]);

            $token = $user->createToken('api_token')->plainTextToken;

            return $this->success([
                'user'  => new UserResource($user),
                'token' => $token,
            ], 'تم إنشاء الحساب بنجاح');
        } catch (\Exception $e) {
            return $this->error('حدث خطأ أثناء إنشاء الحساب', 500, ['exception' => $e->getMessage()]);
        }
    }

private function saveSocialImageToServer(?string $imageUrl): ?string
{
    if (!$imageUrl) {
        return null;
    }

    try {
        $response = Http::timeout(20)->get($imageUrl);

        if (!$response->successful()) {
            return null;
        }

        $contentType = $response->header('Content-Type');

        $extension = match ($contentType) {
            'image/jpeg', 'image/jpg' => 'jpg',
            'image/png'               => 'png',
            'image/webp'              => 'webp',
            default                   => 'jpg',
        };

        $fileName = 'users/social_' . Str::uuid() . '.' . $extension;

        Storage::disk('public')->put($fileName, $response->body());

        return $fileName; // مثل: users/social_xxx.jpg
    } catch (\Throwable $e) {
        return null;
    }
}
    /**
     * 🔹 تسجيل الدخول
     */
    public function login(LoginRequest $request)
    {
        try {
            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return $this->error('بيانات الدخول غير صحيحة', 401);
            }

            $token = $user->createToken('api_token')->plainTextToken;

            return $this->success([
                'user'  => new UserResource($user),
                'token' => $token,
            ], 'تم تسجيل الدخول بنجاح');
        } catch (\Exception $e) {
            return $this->error('حدث خطأ أثناء تسجيل الدخول', 500, ['exception' => $e->getMessage()]);
        }
    }

    /**
     * 🔹 تسجيل الدخول أو إنشاء حساب عبر Google / Facebook / Apple
     */
public function socialLogin(SocialMediaLoginRequest $request)
    {
        try {
            $column = "{$request->provider}_id";

            $user = User::where($column, $request->provider_id)->first();

            if (!$user) {
                $user = User::where('email', $request->email)->first();

                if ($user) {
                    if (!empty($user->google_id) || !empty($user->facebook_id) || !empty($user->apple_id)) {
                        return $this->error('الحساب مرتبط بحساب اجتماعي آخر', 409);
                    }

                    $updateData = [
                        $column => $request->provider_id,
                    ];

                    // لو المستخدم الحالي ما عندوش صورة، خزّن صورة السوشيال
                    if (empty($user->image) && !empty($request->image)) {
                        $savedImage = $this->saveSocialImageToServer($request->image);
                        if ($savedImage) {
                            $updateData['image'] = $savedImage;
                        }
                    }

                    $user->update($updateData);
                    $user->refresh();
                } else {
                    // إنشاء مستخدم جديد
                    $savedImage = $this->saveSocialImageToServer($request->image);

                    $user = User::create([
                        'name'     => $request->name ?? 'User',
                        'email'    => $request->email,
                        'image'    => $savedImage,
                        $column    => $request->provider_id,
                        'password' => Hash::make(Str::random(32)),
                    ]);
                }
            } else {
                // لو الحساب موجود من قبل ومفيش صورة، نحاول نحفظها
                if (empty($user->image) && !empty($request->image)) {
                    $savedImage = $this->saveSocialImageToServer($request->image);
                    if ($savedImage) {
                        $user->update([
                            'image' => $savedImage,
                        ]);
                        $user->refresh();
                    }
                }
            }

            $token = $user->createToken('api_token')->plainTextToken;

            return $this->success([
                'user'  => new UserResource($user),
                'token' => $token,
            ], 'تم تسجيل الدخول بنجاح عبر ' . ucfirst($request->provider));
        } catch (\Exception $e) {
            return $this->error('حدث خطأ أثناء تسجيل الدخول الاجتماعي', 500, [
                'exception' => $e->getMessage()
            ]);
        }
    }

    /**
     * 🔹 تسجيل الخروج
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return $this->success(null, 'تم تسجيل الخروج بنجاح');
    }

    /**
     * 🔹 عرض بيانات المستخدم الحالي
     */
    public function profile(Request $request)
    {
        return $this->success(new UserResource($request->user()), 'تم جلب بيانات المستخدم');
    }

    /**
     * Send OTP to email
     */
    public function sendOtp(SendOtpRequest $request)
    {
        try {
            $email = $request->email;

            // Delete old OTPs
            OtpVerification::where('email', $email)->delete();

            // Generate 6-digit OTP
            $otp = (string) random_int(100000, 999999);

            // Store hashed
            OtpVerification::create([
                'email'      => $email,
                'otp'        => Hash::make($otp),
                'expires_at' => Carbon::now()->addMinutes(5),
            ]);

            // Send email (queue in production)
            Mail::to($email)->send(new OtpMail($otp));

            return $this->success(null, 'تم إرسال رمز التحقق (OTP) إلى بريدك الإلكتروني');
        } catch (\Exception $e) {
            return $this->error('حدث خطأ أثناء إرسال رمز OTP', 500, [
                'exception' => $e->getMessage()
            ]);
        }
    }

    /**
     * Verify OTP + Reset Password
     */
    public function resetPassword(ResetPasswordRequest $request)
    {
        try {
            $email = $request->email;
            $plainOtp = $request->otp;

            $record = OtpVerification::where('email', $email)
                ->whereNull('used_at')
                ->first();

            if (!$record) {
                return $this->error('رمز OTP غير صالح أو تم استخدامه', 422);
            }

            if ($record->isExpired()) {
                $record->delete();
                return $this->error('انتهت صلاحية رمز OTP', 422);
            }

            if (!Hash::check($plainOtp, $record->otp)) {
                return $this->error('رمز OTP غير صحيح', 422);
            }

            // Mark as used
            $record->used_at = now();
            $record->save();

            // Update password
            $user = User::where('email', $email)->firstOrFail();
            $user->password = Hash::make($request->password);
            $user->save();

            // Revoke old tokens
            $user->tokens()->delete();

            // Create new token
            $token = $user->createToken('api_token')->plainTextToken;

            // Cleanup OTP records
            OtpVerification::where('email', $email)->delete();

            return $this->success([
                'user'  => new UserResource($user),
                'token' => $token,
            ], 'تم إعادة تعيين كلمة المرور بنجاح');
        } catch (\Exception $e) {
            return $this->error('حدث خطأ أثناء إعادة تعيين كلمة المرور', 500, [
                'exception' => $e->getMessage()
            ]);
        }
    }


    public function verifyOtp(VerifyOtpRequest $request)
    {
        try {
            $email = $request->email;
            $plainOtp = $request->otp;

            $record = OtpVerification::where('email', $email)
                ->whereNull('used_at')
                ->first();

            if (!$record) {
                return $this->error('رمز OTP غير صالح أو تم استخدامه', 422);
            }

            if ($record->isExpired()) {
                $record->delete();
                return $this->error('انتهت صلاحية رمز OTP', 422);
            }

            if (!Hash::check($plainOtp, $record->otp)) {
                return $this->error('رمز OTP غير صحيح', 422);
            }

            // يمكن فقط تمييزه مؤقتاً أو تركه كما هو بدون حذف
            return $this->success([
                'email' => $email,
            ], 'تم التحقق من رمز OTP بنجاح');
        } catch (\Exception $e) {
            return $this->error('حدث خطأ أثناء التحقق من OTP', 500, [
                'exception' => $e->getMessage()
            ]);
        }
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        try {
            $user = $request->user();
            // Check old password
            if (!Hash::check($request->old_password, $user->password)) {
                return $this->error('كلمة المرور الحالية غير صحيحة', 422);
            }
            if ($request->old_password === $request->new_password) {
                return $this->error('لا يمكن استخدام نفس كلمة المرور القديمة', 422);
            }

            $user->update([
                'password' => Hash::make($request->new_password),
            ]);

            $user->tokens()->delete();
            $token = $user->createToken('api_token')->plainTextToken;

            return $this->success([
                'user'  => new UserResource($user),
                'token' => $token,
            ], 'تم تغيير كلمة المرور بنجاح');
        } catch (\Exception $e) {
            return $this->error('حدث خطأ أثناء تغيير كلمة المرور', 500, [
                'exception' => $e->getMessage()
            ]);
        }
    }
}
