<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'password' => 'required|min:6',
            'email' => 'nullable|email',
            'phone' => 'nullable',
        ]);

        $remember = $request->filled('remember');

        // Dynamic login: email or phone
        if ($request->filled('email')) {
            $credentials = [
                'email' => $request->input('email'),
                'password' => $request->input('password'),
            ];
        } elseif ($request->filled('phone')) {
            $credentials = [
                'phone' => $request->input('phone'),
                'password' => $request->input('password'),
            ];
        } else {
            return back()->withErrors([
                'email' => 'Please enter email or phone number.',
            ]);
        }

        if (auth()->guard('admin')->attempt($credentials, $remember)) {
            $request->session()->regenerate();
            return redirect()->intended(route('admin.dashboard'));
        }

        $errorField = $request->filled('email') ? 'email' : 'phone';
        return back()->withErrors([
            $errorField => 'The provided credentials do not match our records.',
        ])->withInput($request->only('email', 'phone'));
    }

    public function logout(Request $request)
    {
        auth()->guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('admin.login');
    }   
}
