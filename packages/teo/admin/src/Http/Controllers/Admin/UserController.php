<?php

namespace Teo\Admin\Http\Controllers\Admin;

use Teo\Admin\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Поиск по имени или email
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Фильтр по роли
        if ($request->has('role') && $request->role) {
            $query->where('role', $request->role);
        }

        // Сортировка
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        // Пагинация
        $perPage = $request->get('per_page', 15);
        $users = $query->paginate($perPage);

        return response()->json([
            'data' => $users->items(),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total()
            ]
        ]);
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')],
            'role' => 'nullable|in:user,admin,editor',
            'password' => 'required|string|min:8|confirmed',
            'is_blocked' => 'sometimes|boolean',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['role'] = $validated['role'] ?? 'user';

        $user = User::create($validated);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user,
        ], 201);
    }

    /**
     * Display the specified user.
     */
    public function show($id)
    {
        $user = User::findOrFail($id);

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
            'email_verified_at' => $user->email_verified_at,
            'is_blocked' => $user->is_blocked ?? false,
        ]);
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => [
                'sometimes',
                'email',
                Rule::unique('users')->ignore($user->id),
            ],
            'role' => 'sometimes|in:user,admin,editor',
            'password' => 'sometimes|string|min:8|confirmed',
            'is_blocked' => 'sometimes|boolean',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user
        ]);
    }

    /**
     * Remove the specified user.
     */
    public function destroy($id)
    {
        // Не позволяем удалить самого себя
        if ($id == auth()->id()) {
            return response()->json([
                'message' => 'You cannot delete your own account'
            ], 403);
        }

        $user = User::findOrFail($id);
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully'
        ]);
    }

    /**
     * Block/unblock user.
     */
    public function toggleBlock($id)
    {
        $user = User::findOrFail($id);

        // Не позволяем заблокировать самого себя
        if ($id == auth()->id()) {
            return response()->json([
                'message' => 'You cannot block your own account'
            ], 403);
        }

        $user->is_blocked = !$user->is_blocked;
        $user->save();

        return response()->json([
            'message' => $user->is_blocked ? 'User blocked successfully' : 'User unblocked successfully',
            'is_blocked' => $user->is_blocked
        ]);
    }

    /**
     * Get user statistics.
     */
    public function stats()
    {
        return response()->json([
            'total_users' => User::count(),
            'admin_count' => User::where('role', 'admin')->count(),
            'editor_count' => User::where('role', 'editor')->count(),
            'user_count' => User::where('role', 'user')->count(),
            'blocked_count' => User::where('is_blocked', true)->count(),
            'new_today' => User::whereDate('created_at', today())->count(),
            'new_this_week' => User::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
        ]);
    }
}
