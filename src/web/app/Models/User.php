<?php

namespace App\Models;

use App\Modules\Identity\Models\TenantMembership;
use App\Modules\Tenancy\Models\Tenant;
use Database\Factories\UserFactory;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $locale
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'email', 'password', 'locale'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements HasLocalePreference, MustVerifyEmail, PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    protected $fillable = [
        'name',
        'email',
        'locale',
        'password',
        'current_tenant_id',
        'is_superadmin',
    ];

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
            'is_superadmin' => 'boolean',
        ];
    }

    public function currentTenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'current_tenant_id');
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(TenantMembership::class, 'user_id');
    }

    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'tenant_memberships')
            ->withPivot(['role', 'is_active', 'permissions'])
            ->withTimestamps();
    }

    public function hasAccessToTenant(int $tenantId): bool
    {
        if ($this->is_superadmin) {
            return true;
        }

        return $this->memberships()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->exists();
    }

    public function getRoleInTenant(?int $tenantId = null): ?string
    {
        if ($this->is_superadmin) {
            return 'superadmin';
        }

        $tenantId = $tenantId ?? $this->current_tenant_id;
        if (! $tenantId) {
            return null;
        }

        return $this->memberships()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->value('role');
    }

    public function hasPermissionInTenant(string $permission, ?int $tenantId = null): bool
    {
        if ($this->is_superadmin) {
            return true;
        }

        $tenantId = $tenantId ?? $this->current_tenant_id;
        if (! $tenantId) {
            return false;
        }

        $membership = $this->memberships()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->first();

        if (! $membership) {
            return false;
        }

        if ($membership->role === 'tenant_admin' || $membership->role === 'superadmin') {
            return true;
        }

        $permissions = $membership->permissions ?? [];

        return in_array('*', $permissions) || in_array($permission, $permissions);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        $initials = Str::initials($this->name, true);

        return Str::length($initials) > 1
            ? Str::substr($initials, 0, 1).Str::substr($initials, -1)
            : $initials;
    }

    /**
     * Get the user's preferred locale for notifications.
     */
    public function preferredLocale(): ?string
    {
        $locale = $this->locale;

        if ($locale && in_array($locale, ['it', 'en', 'de', 'fr'], true)) {
            return $locale;
        }

        if (session()->has('locale')) {
            $sessionLocale = session()->get('locale');
            if (is_string($sessionLocale) && in_array($sessionLocale, ['it', 'en', 'de', 'fr'], true)) {
                return $sessionLocale;
            }
        }

        return config('app.locale', 'en');
    }
}
