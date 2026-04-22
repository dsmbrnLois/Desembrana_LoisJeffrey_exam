<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $query = User::query()->where('role', 'guest');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('admin/users/Index', [
            'users' => $users,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'] ?? 'guest',
            'status' => 'active',
        ]);

        ActivityLogger::log('user_created', "Created user: {$user->name}", $user);

        return back()->with('success', 'User created successfully.');
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if (! empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        if (isset($validated['role'])) {
            $data['role'] = $validated['role'];
        }

        if (isset($validated['status'])) {
            $data['status'] = $validated['status'];
        }

        $user->update($data);

        ActivityLogger::log('user_updated', "Updated user: {$user->name}", $user);

        return back()->with('success', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $userName = $user->name;
        $user->delete();

        ActivityLogger::log('user_deleted', "Deleted user: {$userName}");

        return back()->with('success', 'User deleted successfully.');
    }

    /**
     * Toggle user active/deactivated status.
     */
    public function toggleStatus(User $user): RedirectResponse
    {
        $newStatus = $user->isActive() ? 'deactivated' : 'active';
        $user->update(['status' => $newStatus]);

        $action = $newStatus === 'active' ? 'activated' : 'deactivated';
        ActivityLogger::log('user_status_changed', "User {$user->name} has been {$action}", $user);

        return back()->with('success', "User {$action} successfully.");
    }
}
