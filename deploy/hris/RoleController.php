<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Company;
use App\Models\LegalEntity;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class RoleController extends Controller
{
    /**
     * Display a list of roles and permissions config.
     */
    public function index()
    {
        Gate::authorize('manage settings');

        $roles = Role::with('permissions')->withCount('users')->orderBy('name')->get();
        $permissions = Permission::orderBy('module')->orderBy('name')->get()->groupBy('module');

        return view('settings.roles', [
            'active' => 'roles',
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Store a new role.
     */
    public function store(Request $request)
    {
        Gate::authorize('manage settings');

        $request->validate([
            'name' => 'required|string|unique:roles,name|max:255',
            'description' => 'nullable|string|max:255',
        ]);

        $role = Role::create($request->only('name', 'description'));

        AuditLogger::record(
            'role.created',
            "Role '{$role->name}' dibuat.",
            $role,
            [],
            $role->only(['name', 'description']),
            $request
        );

        return redirect()->route('settings.roles')->with('success', 'Peran baru berhasil ditambahkan.');
    }

    /**
     * Update permissions for a specific role.
     */
    public function updatePermissions(Request $request, Role $role)
    {
        Gate::authorize('manage settings');

        $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $oldPermissions = $role->permissions()->pluck('name')->values()->all();
        $role->permissions()->sync($request->input('permissions', []));
        $role->load('permissions');
        $newPermissions = $role->permissions()->pluck('name')->values()->all();

        AuditLogger::record(
            'role.permissions_updated',
            "Hak akses role '{$role->name}' diperbarui.",
            $role,
            ['permissions' => $oldPermissions],
            ['permissions' => $newPermissions],
            $request
        );

        return redirect()->route('settings.roles')->with('success', "Hak akses untuk peran '{$role->name}' berhasil diperbarui.");
    }

    /**
     * Manage user role assignments.
     */
    public function usersIndex()
    {
        Gate::authorize('manage settings');

        $users = User::with(['role', 'company', 'legalEntity', 'branch'])->orderBy('name')->get();
        $roles = Role::orderBy('name')->get();
        $companies = Company::where('is_active', true)->orderBy('name')->get(['id', 'code', 'name']);
        $legalEntities = LegalEntity::where('is_active', true)->orderBy('name')->get(['id', 'company_id', 'code', 'name']);
        $branches = Branch::where('is_active', true)->orderBy('name')->get(['id', 'company_id', 'legal_entity_id', 'code', 'name']);

        return view('settings.users', [
            'active' => 'users',
            'users' => $users,
            'roles' => $roles,
            'companies' => $companies,
            'legalEntities' => $legalEntities,
            'branches' => $branches,
        ]);
    }

    /**
     * Update a user's role.
     */
    public function updateUserRole(Request $request, User $user)
    {
        Gate::authorize('manage settings');

        $data = $request->validate([
            'role_id' => 'required|exists:roles,id',
            'company_id' => 'nullable|exists:companies,id',
            'legal_entity_id' => 'nullable|exists:legal_entities,id',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $oldValues = $user->only(['role_id', 'company_id', 'legal_entity_id', 'branch_id']);
        $user->update($data);

        AuditLogger::record(
            'user.access_updated',
            "Akses user '{$user->name}' diperbarui.",
            $user,
            $oldValues,
            $user->only(['role_id', 'company_id', 'legal_entity_id', 'branch_id']),
            $request
        );

        return redirect()->route('settings.users')->with('success', "Akses untuk user '{$user->name}' berhasil diperbarui.");
    }
}
