<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class User extends Authenticatable implements FilamentUser, HasMedia
{
    use HasApiTokens, InteractsWithMedia;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;

    use HasProfilePhoto;
    use HasTeams;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'id_number',
        'phone',
        'address',
        'city',
        'state',
        'country',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
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
        ];
    }

    // public function teams(): BelongsToMany
    // {
    //     return $this->belongsToMany(Team::class);
    // }

    // public function getTenants(Panel $panel): array|Collection
    // {
    //     return $this->teams;
    // }

    // public function canAccessTenant(Model $tenant): bool
    // {
    //     return $this->teams()->whereKey($tenant)->exists();
    // }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    /**
     *  HasMany relationship with Loan model
     */
    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

    /**
     * HasManyThrough relationship with Payment model
     */
    public function payments()
    {
        return $this->hasManyThrough(Payment::class, Loan::class, 'user_id', 'loan_id', 'id', 'id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('profile_photo')
            ->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('preview')
            ->fit(Fit::Contain, 300, 300);
    }

    public function getFilamentAvatarUrl(): ?string
    {
        //        return $this->avatar_url;
        // use spatie image
        return $this->profile_photo_url;
    }

    protected function profilePhotoUrl(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if ($this->hasMedia('profile_photo')) {
                    return $this->getFirstMediaUrl('profile_photo');
                }

                return 'https://api.dicebear.com/9.x/bottts/svg?seed='.urlencode($this->name);
            }
        )->shouldCache();
    }
}
