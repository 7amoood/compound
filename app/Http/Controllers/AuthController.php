<?php
namespace App\Http\Controllers;

use App\Models\Market;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class AuthController extends Controller
{
    /*
     * View Login Page (Web)
     */
    public function loginView()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return Inertia::render('Auth/Login', [
            'categories' => ServiceCategory::active()->get(['id', 'name', 'icon']),
            'compounds'  => \App\Models\Compound::active()->get(['id', 'name']),
            'markets'    => Market::active()->get(['id', 'name']),
        ]);
    }

    /**
     * Handle Login (Web)
     */
    public function webLogin(Request $request)
    {
        $credentials = $request->validate([
            'phone'    => ['required'],
            'password' => ['required'],
        ]);

        $user = User::where('phone', $request->phone)->first();

        if ($user && ! $user->is_active) {
            return back()->withErrors([
                'message' => 'حسابك لا يزال بانتظار تفعيل المشرف. يرجى الانتظار.',
            ]);
        }

        if (Auth::attempt($credentials, true)) {
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'message' => 'بيانات الدخول غير صحيحة',
        ]);
    }

    /**
     * Handle Register (Web)
     */
    public function webRegister(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'phone'           => 'required|string|unique:users,phone',
            'password'        => 'required|string|min:6',
            'role'            => 'required|in:resident,provider',
            'compound_id'     => 'required_if:role,resident|nullable|exists:compounds,id',
            'block_no'        => 'nullable|string',
            'floor'           => 'nullable|string',
            'apt_no'          => 'nullable|string',
            // Service Type is required for provider ONLY if NOT associated with a market
            'service_type_id' => [
                'nullable',
                'exists:service_categories,id',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->role === 'provider' && ! $request->market_id && ! $value) {
                        $fail('يرجى اختيار نوع الخدمة أو الماركت.');
                    }
                },
            ],
            'market_id'       => 'nullable|exists:markets,id',
        ]);

        $user = User::create([
            'name'            => $request->name,
            'phone'           => $request->phone,
            'password'        => Hash::make($request->password),
            'role'            => $request->role,
            'compound_id'     => $request->compound_id,
            'block_no'        => $request->block_no,
            'floor'           => $request->floor,
            'apt_no'          => $request->apt_no,
            'service_type_id' => $request->service_type_id,
            'market_id'       => $request->market_id,
            'is_active'       => false,
        ]);

        return redirect()->route('login')->with('message', 'تم إنشاء الحساب بنجاح. يرجى انتظار تفعيل المشرف.');
    }

    public function logout()
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/login');
    }
}
