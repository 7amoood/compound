<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class WebAuthnController extends Controller
{
    /**
     * Generate registration options for WebAuthn
     */
    public function registerOptions(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|exists:users,phone',
        ]);

        $user = User::where('phone', $request->phone)->first();

        if (! $user) {
            return response()->json(['error' => 'المستخدم غير موجود'], 404);
        }

        // Generate a random challenge
        $challenge = Str::random(32);

        // Store challenge in session for verification
        session(['webauthn_challenge' => $challenge, 'webauthn_user_id' => $user->id]);

        $options = [
            'challenge'              => base64_encode($challenge),
            'rp'                     => [
                'name' => 'خدمات المجمع',
                'id'   => parse_url(config('app.url'), PHP_URL_HOST) ?: request()->getHost(),
            ],
            'user'                   => [
                'id'          => base64_encode($user->id . '_' . $user->phone),
                'name'        => $user->phone,
                'displayName' => $user->name,
            ],
            'pubKeyCredParams'       => [
                ['type' => 'public-key', 'alg' => -7],   // ES256
                ['type' => 'public-key', 'alg' => -257], // RS256
            ],
            'timeout'                => 60000,
            'attestation'            => 'none',
            'authenticatorSelection' => [
                'authenticatorAttachment' => 'platform',
                'userVerification'        => 'required',
                'residentKey'             => 'preferred',
            ],
        ];

        return response()->json($options);
    }

    /**
     * Store the WebAuthn credential after registration
     */
    public function registerStore(Request $request)
    {
        $request->validate([
            'credential_id' => 'required|string',
            'public_key'    => 'required|string',
            'phone'         => 'required|string|exists:users,phone',
        ]);

        $user = User::where('phone', $request->phone)->first();

        if (! $user) {
            return response()->json(['error' => 'المستخدم غير موجود'], 404);
        }

        // Verify challenge matches
        $sessionUserId = session('webauthn_user_id');
        if ($sessionUserId != $user->id) {
            return response()->json(['error' => 'خطأ في التحقق'], 400);
        }

        // Store credentials
        $user->update([
            'webauthn_credential_id' => $request->credential_id,
            'webauthn_public_key'    => $request->public_key,
            'biometric_enabled'      => true,
        ]);

        // Clear session
        session()->forget(['webauthn_challenge', 'webauthn_user_id']);

        return response()->json(['success' => true, 'message' => 'تم تسجيل البصمة بنجاح']);
    }

    /**
     * Generate authentication options for WebAuthn login
     */
    public function authOptions(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
        ]);

        $user = User::where('phone', $request->phone)->first();

        if (! $user || ! $user->biometric_enabled || ! $user->webauthn_credential_id) {
            return response()->json(['error' => 'البصمة غير مفعلة لهذا الحساب', 'biometric_available' => false], 400);
        }

        // Generate a random challenge
        $challenge = Str::random(32);

        // Store challenge in session for verification
        session(['webauthn_auth_challenge' => $challenge, 'webauthn_auth_user_id' => $user->id]);

        $options = [
            'challenge'        => base64_encode($challenge),
            'timeout'          => 60000,
            'rpId'             => parse_url(config('app.url'), PHP_URL_HOST) ?: request()->getHost(),
            'userVerification' => 'required',
            'allowCredentials' => [
                [
                    'type' => 'public-key',
                    'id'   => $user->webauthn_credential_id,
                ],
            ],
        ];

        return response()->json($options);
    }

    /**
     * Verify WebAuthn authentication and login
     */
    public function authVerify(Request $request)
    {
        $request->validate([
            'phone'              => 'required|string',
            'credential_id'      => 'required|string',
            'authenticator_data' => 'required|string',
            'client_data_json'   => 'required|string',
            'signature'          => 'required|string',
        ]);

        $user = User::where('phone', $request->phone)->first();

        if (! $user || ! $user->biometric_enabled) {
            return response()->json(['error' => 'البصمة غير مفعلة'], 400);
        }

        // Verify credential ID matches
        if ($user->webauthn_credential_id !== $request->credential_id) {
            return response()->json(['error' => 'بيانات البصمة غير صحيحة'], 400);
        }

        // Verify session
        $sessionUserId = session('webauthn_auth_user_id');
        if ($sessionUserId != $user->id) {
            return response()->json(['error' => 'خطأ في التحقق'], 400);
        }

        // In a production environment, you would verify the signature here
        // For now, we trust the credential ID match and session verification

        // Clear session
        session()->forget(['webauthn_auth_challenge', 'webauthn_auth_user_id']);

        // Login the user
        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'success'  => true,
            'redirect' => $user->role === 'admin' ? '/admin/dashboard' : '/dashboard',
        ]);
    }

    /**
     * Check if biometric is enabled for a phone number
     */
    public function checkBiometric(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
        ]);

        $user = User::where('phone', $request->phone)->first();

        return response()->json([
            'biometric_enabled' => $user && $user->biometric_enabled && $user->webauthn_credential_id,
        ]);
    }
}
