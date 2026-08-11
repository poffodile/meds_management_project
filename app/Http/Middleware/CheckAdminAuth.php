<?php

namespace App\Http\Middleware;
use Closure;
use Session;

class CheckAdminAuth
{
	public function handle($request, Closure $next)
    {
	    if(!Session::has('scitsAdminSession'))
	    {  
            if (\Illuminate\Support\Facades\Auth::check()) {
                $user = \Illuminate\Support\Facades\Auth::user();
                if ($user->user_type == 'O' || $user->user_type == 'A') {
                    $admin = \App\Admin::where('id', $user->admn_id)->where('is_deleted', 0)->first();
                    if (!empty($admin)) {
                        $admin->home_id = $user->home_id;
                        Session::put('scitsAdminSession', $admin);
                        if ($user->user_type == 'A') {
                            Session::put('scitsAgentSession', $user);
                        }
                    } else {
                        return redirect('admin/login');
                    }
                } else {
                    return redirect('admin/login');
                }
            } else {
                return redirect('admin/login');
            }
        } 
        
        return $next($request);
    }
	
}