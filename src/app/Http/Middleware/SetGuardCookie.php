<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class SetGuardCookie
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        /*dd([
            '全セッションデータ' => session()->all(),
            'web Guardのログイン状態' => Auth::guard('web')->check(),
            'admin Guardのログイン状態' => Auth::guard('admin')->check(),
        ]);*/

        if (!$request->session()->has('guard_cookie_set')) {

            $guard = $this->getGuard($request);

            // Guardに応じてCookie名を設定
            if ($guard === 'admin') {
                config(['session.cookie' => 'admin_session']);
            }

            if ($guard === 'staff') {
                config(['session.cookie' => 'staff_session']);
            }


            $request->session()->put('guard_cookie_set', true);
            $request->session()->save();

            /*dd([
            '全セッションデータ' => session()->all(),
            'web Guardのログイン状態' => Auth::guard('web')->check(),
            'admin Guardのログイン状態' => Auth::guard('admin')->check(),
        ]);*/
        }

        return $next($request);
    }
    protected function getGuard(Request $request): ?string
    {
        // Guardを判定するロジック（例: URLパターンや認証情報に基づいて判定）
        if ($request->is('admin/*')) {
            return 'admin';
        }

        if ($request->is('staff/*')) {
            return 'staff';
        }

        return null; // Guardが判定できない場合はnull
    }
}
