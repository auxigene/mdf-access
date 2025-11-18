<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorAuthController extends Controller
{
    /**
     * Show the 2FA verification form.
     */
    public function showVerifyForm()
    {
        if (! session()->has('2fa:user:id')) {
            return redirect()->route('login');
        }

        return view('auth.2fa-verify');
    }

    /**
     * Verify the 2FA code.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $userId = session('2fa:user:id');
        if (! $userId) {
            return redirect()->route('login');
        }

        $user = \App\Models\User::findOrFail($userId);

        $google2fa = new Google2FA();
        $valid = $google2fa->verifyKey($user->two_factor_secret, $request->code);

        if (! $valid) {
            return back()->withErrors(['code' => 'Invalid verification code.']);
        }

        // Login the user
        Auth::login($user);
        session()->forget('2fa:user:id');
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Show the 2FA setup form.
     */
    public function showSetupForm()
    {
        $user = Auth::user();

        if ($user->two_factor_enabled) {
            return redirect()->route('dashboard')->with('error', '2FA is already enabled.');
        }

        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();

        // Store secret temporarily in session
        session(['2fa:secret' => $secret]);

        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );

        return view('auth.2fa-setup', [
            'qrCodeUrl' => $qrCodeUrl,
            'secret' => $secret,
        ]);
    }

    /**
     * Enable 2FA for the user.
     */
    public function enable(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
            'password' => ['required', 'string'],
        ]);

        $user = Auth::user();

        // Verify password
        if (! Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Invalid password.']);
        }

        $secret = session('2fa:secret');
        if (! $secret) {
            return redirect()->route('2fa.setup')->with('error', 'Session expired. Please try again.');
        }

        // Verify the code
        $google2fa = new Google2FA();
        $valid = $google2fa->verifyKey($secret, $request->code);

        if (! $valid) {
            return back()->withErrors(['code' => 'Invalid verification code.']);
        }

        // Enable 2FA
        $user->two_factor_secret = encrypt($secret);
        $user->two_factor_enabled = true;
        $user->save();

        session()->forget('2fa:secret');

        return redirect()->route('dashboard')->with('status', '2FA has been enabled successfully!');
    }

    /**
     * Disable 2FA for the user.
     */
    public function disable(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        $user = Auth::user();

        // Verify password
        if (! Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Invalid password.']);
        }

        $user->two_factor_secret = null;
        $user->two_factor_enabled = false;
        $user->save();

        return redirect()->route('dashboard')->with('status', '2FA has been disabled.');
    }
}
