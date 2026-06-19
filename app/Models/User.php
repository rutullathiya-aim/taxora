<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserListRole;
use App\Enums\UserListStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property string $id
 * @property string $name
 * @property string $email
 * @property string $phone
 * @property UserRole $role
 * @property bool $is_active
 * @property string $password
 * @property Carbon|null $email_verified_at
 * @property Carbon|null $last_login_at
 * @property array|null $preferences
 * @property-read Collection<int, UserInvitation> $invitations
 * @property-read UserInvitation|null $invitation
 * @property-read Collection<int, Project> $projects
 * @property-read Collection<int, Task> $tasks
 */
class User extends Authenticatable // implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, HasUlids, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'role',
        'is_active',
        'password',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
            'preferences' => 'array',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        $words = explode(' ', $this->name);
        $initials = '';
        foreach (array_slice($words, 0, 2) as $w) {
            $initials .= Str::upper(Str::substr($w, 0, 1));
        }

        return $initials;
    }

    /**
     * @return HasMany<UserInvitation, $this>
     */
    public function invitations(): HasMany
    {
        return $this->hasMany(UserInvitation::class);
    }

    /**
     * @return HasOne<UserInvitation, $this>
     */
    public function invitation(): HasOne
    {
        return $this->hasOne(UserInvitation::class)->latestOfMany();
    }

    /**
     * @return BelongsToMany<Project, $this>
     */
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class);
    }

    /**
     * @return BelongsToMany<Task, $this>
     */
    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(UserRole::Admin->value);
    }

    public function isManager(): bool
    {
        return $this->hasRole(UserRole::Manager->value);
    }

    public function isAdminOrManager(): bool
    {
        return $this->isAdmin() || $this->isManager();
    }

    public function isStaff(): bool
    {
        return $this->hasRole(UserRole::Staff->value);
    }

    public function status(): UserStatus
    {
        if (is_null($this->email_verified_at)) {
            return UserStatus::Pending;
        }

        return $this->is_active ? UserStatus::Active : UserStatus::Inactive;
    }

    public function statusColor(): string
    {
        if ($this->trashed()) {
            return 'red';
        }

        return $this->status()->color();
    }

    public function statusLabel(): string
    {
        if ($this->trashed()) {
            return 'Deleted';
        }

        return $this->status()->label();
    }

    public function listStatus(): UserListStatus
    {
        return UserListStatus::fromState($this->status(), $this->trashed());
    }

    public function getPreference(string $key, mixed $default = null): mixed
    {
        return data_get($this->preferences, $key, $default);
    }

    public function setPreference(string $key, mixed $value): void
    {
        $preferences = $this->preferences ?? [];
        data_set($preferences, $key, $value);

        $this->forceFill(['preferences' => $preferences])->save();
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        $search = Str::of((string) $search)->trim()->squish();

        if ($search->isEmpty()) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', '%' . $search . '%')
                ->orWhere('email', 'like', '%' . $search . '%')
                ->orWhere('phone', 'like', '%' . $search . '%');
        });
    }

    public function scopeFilterStatus(Builder $query, UserListStatus $status): Builder
    {
        return match ($status) {
            UserListStatus::Active => $query->whereNull('deleted_at')->where('is_active', true)->whereNotNull('email_verified_at'),
            UserListStatus::Inactive => $query->whereNull('deleted_at')->where('is_active', false)->whereNotNull('email_verified_at'),
            UserListStatus::Pending => $query->whereNull('deleted_at')->whereNull('email_verified_at'),
            UserListStatus::Deleted => $query->onlyTrashed(),
            UserListStatus::All => $query->whereNull('deleted_at'),
        };
    }

    public function scopeFilterRole(Builder $query, UserListRole $role): Builder
    {
        if ($role === UserListRole::All) {
            return $query;
        }

        return $query->where('role', $role->value);
    }
}
