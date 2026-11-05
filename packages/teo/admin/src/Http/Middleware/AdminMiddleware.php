<?php
// app/Http/Middleware/AdminMiddleware.php

namespace Teo\Admin\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Проверяем авторизацию
        if (!$request->user()) {
            return response()->json([
                'message' => 'Unauthenticated',
                'status' => 401
            ], 401);
        }

        // Проверяем role или is_admin поле
        // Вариант 1: Если используете поле 'role'
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Access denied. Admin only.',
                'user_role' => $request->user()->role,
                'status' => 403
            ], 403);
        }

        // Вариант 2: Если используете поле 'is_admin' (как у вас)
        // if (!$request->user()->is_admin) {
        //     return response()->json([
        //         'message' => 'Access denied. Admin only.',
        //         'status' => 403
        //     ], 403);
        // }

        return $next($request);
    }
}
