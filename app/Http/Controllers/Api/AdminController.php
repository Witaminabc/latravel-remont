<?php
// app/Http/Controllers/Api/AdminController.php — только getUsers (список для wpadmin)

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    public function getUsers()
    {
        try {
            Log::info('Admin getUsers started');
            $adminId = auth()->id();
            Log::info('Admin ID: ' . $adminId);

            if (!$adminId) {
                Log::error('Admin not authenticated');
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $users = User::where('id', '!=', $adminId)
                ->select('id', 'name', 'email', 'role', 'is_online', 'is_blocked')
                ->orderBy('is_online', 'desc')
                ->get();

            Log::info('Users found: ' . $users->count());

            return response()->json($users);
        } catch (\Exception $e) {
            Log::error('Admin getUsers error: ' . $e->getMessage());
            Log::error('File: ' . $e->getFile());
            Log::error('Line: ' . $e->getLine());
            Log::error('Trace: ' . $e->getTraceAsString());

            return response()->json([
                'error' => 'Internal server error'
            ], 500);
        }
    }
}
