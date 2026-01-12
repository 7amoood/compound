<?php
namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
             ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? [
                    'id'              => $request->user()->id,
                    'name'            => $request->user()->name,
                    'email'           => $request->user()->email,
                    'phone'           => $request->user()->phone,
                    'role'            => $request->user()->role,
                    'photo'           => $request->user()->photo ? (str_starts_with($request->user()->photo, 'http') ? $request->user()->photo : (str_starts_with($request->user()->photo, '/avatars/') ? $request->user()->photo : asset('storage/' . $request->user()->photo))) : null,
                    'block_no'        => $request->user()->block_no,
                    'floor'           => $request->user()->floor,
                    'apt_no'          => $request->user()->apt_no,
                    'compound_id'     => $request->user()->compound_id,
                    'compound'        => $request->user()->compound,
                    'is_active'       => $request->user()->is_active,
                    'market_id'       => $request->user()->market_id,
                    'service_type_id' => $request->user()->service_type_id,
                ] : null,
            ],
        ];
    }
}
