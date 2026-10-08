<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->password_change_required) {
                return redirect()->route('auth.change-password');
            }

            return $user->isAdmin()
                ? redirect()->route('admin.dashboard')
                : redirect()->route('engineer.dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'login' => ['nullable', 'string'],
            'email' => ['nullable', 'string'],
            'password' => ['required', 'string'],
        ]);

        $loginIdentifier = trim($validated['login'] ?? $validated['email'] ?? '');
        if (empty($loginIdentifier)) {
            return back()->withErrors(['login' => 'Email address or User ID is required.'])->withInput();
        }

        $user = User::where('email', $loginIdentifier)
            ->orWhere('username', $loginIdentifier)
            ->orWhere('employee_code', $loginIdentifier)
            ->first();

        if ($user && Hash::check($request->password, $user->password)) {
            if (!$user->isActive()) {
                return back()->withErrors(['login' => 'This account has been deactivated. Please contact an administrator.'])->withInput();
            }

            if ($user->hasExpiredTemporaryPassword()) {
                AuditLog::create([
                    'auditable_type' => get_class($user),
                    'auditable_id' => $user->id,
                    'user_id' => $user->id,
                    'event' => 'login_failed_expired_password',
                    'description' => "User {$user->name} attempted login with expired temporary password from " . $request->ip(),
                    'ip_address' => $request->ip(),
                ]);

                return back()->withErrors([
                    'login' => 'Your temporary onboarding password has expired. Please contact an administrator to regenerate credentials.',
                ])->withInput();
            }

            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();

            $user->update(['last_login_at' => now()]);

            AuditLog::create([
                'auditable_type' => get_class($user),
                'auditable_id' => $user->id,
                'user_id' => $user->id,
                'event' => 'login',
                'description' => "User {$user->name} logged in from " . $request->ip(),
                'ip_address' => $request->ip(),
            ]);

            if ($user->password_change_required) {
                return redirect()->route('auth.change-password')
                    ->with('warning', 'Welcome to SolarOps! For security, you must change your temporary password before continuing.');
            }

            if ($user->isAdmin()) {
                return redirect()->intended(route('admin.dashboard'));
            }

            return redirect()->intended(route('engineer.dashboard'));
        }

        return back()->withErrors([
            'login' => 'The provided credentials do not match our records.',
        ])->onlyInput('login', 'email');
    }

    public function showChangePasswordForm(): View|RedirectResponse
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        return view('auth.change-password', compact('user'));
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $rules = [
            'current_password' => ['nullable', 'string'],
        ];

        if ($request->has('new_password')) {
            $rules['new_password'] = [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#^~_+=<>|-])/',
            ];
            $passwordKey = 'new_password';
        } else {
            $rules['password'] = [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#^~_+=<>|-])/',
            ];
            $passwordKey = 'password';
        }

        $request->validate($rules, [
            "{$passwordKey}.regex" => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
            "{$passwordKey}.min" => 'Password must be at least 8 characters long.',
            "{$passwordKey}.confirmed" => 'Password confirmation does not match.',
        ]);

        if ($request->filled('current_password') && !Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The provided current password does not match our records.']);
        }

        $plainNewPassword = $request->input($passwordKey);
        $user->consumeTemporaryPassword();
        $user->password = Hash::make($plainNewPassword);
        $user->last_login_at = now();
        $user->save();

        AuditLog::create([
            'auditable_type' => get_class($user),
            'auditable_id' => $user->id,
            'user_id' => $user->id,
            'event' => 'EMPLOYEE_TEMP_PASSWORD_CONSUMED',
            'description' => "User {$user->name} changed temporary password and completed onboarding verification",
            'ip_address' => $request->ip(),
        ]);

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard')
                ->with('success', 'Your password has been changed successfully. Welcome to SolarOps!');
        }

        return redirect()->route('engineer.dashboard')
            ->with('success', 'Your password has been changed successfully. Welcome to SolarOps!');
    }

    public function setupWizard(): View|RedirectResponse
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            return redirect()->route('login');
        }

        return view('admin.setup_wizard');
    }

    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if ($user) {
            AuditLog::create([
                'auditable_type' => get_class($user),
                'auditable_id' => $user->id,
                'user_id' => $user->id,
                'event' => 'logout',
                'description' => "User {$user->name} logged out",
                'ip_address' => $request->ip(),
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'You have been logged out successfully.');
    }
}
