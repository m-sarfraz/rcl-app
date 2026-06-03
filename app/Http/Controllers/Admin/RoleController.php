<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('users')->get();
        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = Permission::orderBy('module')->orderBy('name')->get()->groupBy('module');
        return view('admin.roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100|unique:roles',
            'description' => 'nullable|string|max:500',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $data['slug'] = Str::slug($data['name']);
        $role = Role::create($data);

        if ($request->filled('permissions')) {
            $role->permissions()->sync($request->permissions);
        }

        return redirect()->route('admin.roles.index')->with('success', 'Role created.');
    }

    public function edit(Role $role)
    {
        $permissions        = Permission::orderBy('module')->orderBy('name')->get()->groupBy('module');
        $assignedPermissions = $role->permissions->pluck('id')->toArray();
        return view('admin.roles.edit', compact('role','permissions','assignedPermissions'));
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100|unique:roles,name,'.$role->id,
            'description' => 'nullable|string|max:500',
            'permissions' => 'nullable|array',
        ]);

        $role->update($data);
        $role->permissions()->sync($request->get('permissions', []));

        return redirect()->route('admin.roles.index')->with('success', 'Role updated.');
    }

    public function destroy(Role $role)
    {
        if ($role->is_system) {
            return back()->with('error', 'System roles cannot be deleted.');
        }
        $role->delete();
        return back()->with('success', 'Role deleted.');
    }

    // User management
    public function users()
    {
        $users = User::with('role')->orderBy('name')->paginate(20);
        $roles = Role::all();
        return view('admin.roles.users', compact('users','roles'));
    }

    public function assignRole(Request $request, User $user)
    {
        $request->validate(['role_id' => 'required|exists:roles,id']);
        $user->update(['role_id' => $request->role_id]);
        return back()->with('success', 'Role assigned.');
    }

    public function toggleUserStatus(User $user)
    {
        if ($user->isSuperAdmin()) {
            return back()->with('error', 'Cannot deactivate super admin.');
        }
        $user->update(['is_active' => !$user->is_active]);
        return back()->with('success', 'User status updated.');
    }
}
