<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminAuthController extends Controller
{
    public function home(): View
    {
        return $this->view('admin.dashboard');
    }

    public function loginPage(): View
    {
        return $this->view('admin.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::guard('admin')->attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Invalid credentials.'])->withInput();
        }

        $request->session()->regenerate();

        return redirect()->route('admin.index')->with('success', 'Welcome back.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login.page')->with('success', 'Logged out successfully.');
    }

    public function showForgotPasswordForm(): View
    {
        return $this->view('admin.auth.forgot-password');
    }

    public function sendResetOtp(Request $request): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'email']]);
        $admin = Admin::where('email', $data['email'])->first();

        if (! $admin) {
            return back()->withErrors(['email' => 'Admin not found.']);
        }

        $token = Str::random(64);
        $admin->update(['remember_token' => Hash::make($token)]);

        return redirect()->route('admin.password.reset', ['token' => $token, 'email' => $admin->email])
            ->with('success', 'Reset token created. Connect mail sending here.');
    }

    public function showResetPasswordForm(string $token): View
    {
        return $this->view('admin.auth.reset-password', compact('token'));
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $admin = Admin::where('email', $data['email'])->firstOrFail();
        $admin->update([
            'password' => Hash::make($data['password']),
            'remember_token' => Str::random(60),
        ]);

        return redirect()->route('admin.login.page')->with('success', 'Password reset successfully.');
    }
}
