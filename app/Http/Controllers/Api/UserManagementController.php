<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PermissionResource;
use App\Http\Resources\UserResource;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Schema;

final class UserManagementController extends Controller
{
    public function index(Request $request): ResourceCollection
    {
        abort_unless($request->user()->isAdmin(), 403);

        $relationships = ['roles', 'badges'];
        if (Schema::hasTable('permission_user')) {
            $relationships[] = 'directPermissions';
        }

        $users = User::query()
            ->with($relationships)
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = (string) $request->input('search');

                $query->where(function ($builder) use ($search): void {
                    $builder->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->paginate((int) $request->input('per_page', 15));

        return UserResource::collection($users);
    }

    public function permissions(Request $request): ResourceCollection
    {
        abort_unless($request->user()->isAdmin(), 403);

        $permissions = Permission::query()
            ->orderBy('name')
            ->get();

        return PermissionResource::collection($permissions);
    }

    public function show(Request $request, User $user): UserResource
    {
        abort_unless($request->user()->isAdmin(), 403);

        $relationships = ['roles.permissions', 'badges'];
        if (Schema::hasTable('permission_user')) {
            $relationships[] = 'directPermissions';
        }

        return new UserResource($user->load($relationships));
    }

    public function update(Request $request, User $user): UserResource
    {
        abort_unless($request->user()->isAdmin(), 403);

        $validated = $request->validate([
            'first_name' => ['sometimes', 'required', 'string', 'max:255'],
            'last_name' => ['sometimes', 'required', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
            'roles' => ['sometimes', 'array'],
            'roles.*' => ['string', 'exists:roles,name'],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $user->fill(collect($validated)->only(['first_name', 'last_name', 'is_active'])->all());
        $user->save();

        if (array_key_exists('roles', $validated)) {
            $user->syncRoles($validated['roles']);
        }

        if (array_key_exists('permissions', $validated) && Schema::hasTable('permission_user')) {
            $permissionIds = Permission::whereIn('name', $validated['permissions'])->pluck('id')->all();
            $grantedAt = now();
            $payload = collect($permissionIds)
                ->mapWithKeys(fn (int $permissionId) => [$permissionId => ['granted_at' => $grantedAt]])
                ->all();

            $user->directPermissions()->sync($payload);
        }

        $relationships = ['roles.permissions', 'badges'];
        if (Schema::hasTable('permission_user')) {
            $relationships[] = 'directPermissions';
        }

        return new UserResource($user->fresh($relationships));
    }
}
