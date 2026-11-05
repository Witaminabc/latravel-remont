<?php

namespace Teo\Chat\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Доступ к /api/admin/chat/* только для пользователей с ролью
 * config('chat.admin_role'). Возвращает JSON (401/403), не HTML.
 */
class EnsureChatAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        if (($user->role ?? null) !== config('chat.admin_role', 'admin')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return $next($request);
    }
}