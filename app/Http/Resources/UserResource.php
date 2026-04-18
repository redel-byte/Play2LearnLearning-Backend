<?php
declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'is_active' => $this->is_active,
            'last_login_at' => $this->last_login_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'roles' => $this->whenLoaded('roles', fn () => RoleResource::collection($this->roles)),
            'direct_permissions' => $this->whenLoaded('directPermissions', fn () => PermissionResource::collection($this->directPermissions)),
            'permissions' => $this->whenLoaded('permissions', fn () => PermissionResource::collection($this->permissions)),
            'permission_names' => $this->when(
                optional($request->user())->isAdmin(),
                $this->getPermissionNames()
            ),
            'badges' => $this->whenLoaded('badges', fn () => BadgeResource::collection($this->badges)),
            'total_points' => $this->when(
                $request->boolean('include_stats') || $request->routeIs('admin.users.*'),
                (int) $this->quizAttempts()->sum('score')
            ),
            'completed_quizzes_count' => $this->when(
                $request->boolean('include_stats') || $request->routeIs('admin.users.*'),
                $this->quizAttempts()->whereNotNull('submitted_at')->count()
            ),
            'average_score' => $this->when(
                $request->boolean('include_stats') || $request->routeIs('admin.users.*'),
                round((float) ($this->quizAttempts()->whereNotNull('submitted_at')->avg('percentage') ?? 0), 2)
            ),
            'is_admin' => $this->isAdmin(),
            'is_teacher' => $this->isTeacher(),
            'is_learner' => $this->isLearner(),
            'email_verified_at' => $this->when(
                optional($request->user())->isAdmin() || optional($request->user())->id === $this->id,
                $this->email_verified_at
            ),
            'deleted_at' => $this->when(
                optional($request->user())->isAdmin(),
                $this->deleted_at
            ),
        ];
    }
}
