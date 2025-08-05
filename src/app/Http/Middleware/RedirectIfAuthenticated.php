<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /***************** Original code ****************************
     *
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string|null  ...$guards
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    /*public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return redirect(RouteServiceProvider::HOME);
            }
        }

        return $next($request);
    }*/

    public function handle($request, Closure $next, ...$guards)
    {

        foreach ($guards as $guard) {
            //dd(Auth::guard($guard)->check());
            if (Auth::guard($guard)->check()) {
                if ($guard === 'admin') {

                    return redirect()->route('admin.home');
                }

                return redirect()->route('staff.home');
            }
        }

        //return $next($request);
        // ログインしていない場合に管理者ログイン画面へリダイレクト
        return redirect()->route('admin.login');
    }
}
