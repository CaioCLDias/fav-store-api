<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin'
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
            'is_admin' => 'boolean',
        ];
    }

    /**
     * Get the user's favorite products.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function favoriteProducts()
    {
        return $this->hasMany(FavoriteProduct::class);
    }

    /**
     * Get the JWT identifier for the user.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Get the custom JWT claims for the user.
     *
     * @return array
     */
    public function getJWTCustomClaims(): array
    {
        return ['is_admin' => $this->is_admin];
    }

    /**
     * Check if the user is an admin.
     *
     * @return boolean
     */
    public function isAdmin(): bool
    {
        return $this->is_admin;
    }

    /**
     * Check if the user is a regular user.
     *
     * @return boolean
     */
    public function isRegularUser(): bool
    {
        return !$this->is_admin;
    }

    /**
     * Scope a query to only include admins.
     *
     * @param [type] $query
     * @return void
     */
    public function scopeAdmins($query)
    {
        return $query->where('is_admin', true);
    }

    /**
     * Scope a query to only include regular users.
     *
     * @param [type] $query
     * @return void
     */
    public function scopeRegularUsers($query)
    {
        return $query->where('is_admin', false);
    }
}
