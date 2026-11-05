<?php

namespace Teo\Admin\Http\Controllers\Admin;

use Teo\Admin\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class DashboardController extends Controller
{
    public function stats()
    {
//        return response()->json(['message' => 'Test']);
        try {
            return response()->json([
                'total_pages' => Post::count(), // или Post::all()->count()
                'total_posts' => 0,
                'published_pages' => Post::where('status', 'published')->count(),
                'draft_pages' => Post::where('status', 'draft')->count(),
                'total_users' => User::count(),
                'recent_pages' => Post::latest()->take(5)->get(),
            ]);
        } catch (\Exception $e) {
            // Логируем ошибку для отладки
            \Log::error('Dashboard stats error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
