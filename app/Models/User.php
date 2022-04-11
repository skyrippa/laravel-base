<?php

namespace App\Models;

use App\Enums\UserRoles;
use App\Models\Traits\LegalEntityTrait;
use App\Models\Traits\SanitizeTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, SanitizeTrait, LegalEntityTrait, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'document',
        'password',
    ];

    protected $guard_name = 'api';

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $appends = ['role'];

    public function getRoleAttribute()
    {
        $roles = $this->roles;
        if (count($roles) > 0) {
            return strtoupper($roles->first()->name);
        } else {
            return null;
        }
    }

    public function isSuperAdmin ()
    {
        if ($this->hasRole(UserRoles::SUPER_ADMIN))
            return true;

        return false;
    }

    public function client()
    {
        return $this->hasOne(Client::class);
    }
}
