<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * @mixin Builder
 */
class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'cpf',
        'first_time_login',
        'role_id',
        'created_by'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password'
    ];

    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */
    protected $visible = [
        'id',
        'name',
        'email',
        'password',
        'cpf',
        'first_time_login',
        'role_id',
        'created_by',
        'created_at',
        'updated_at'
    ];

    public function contacts()
    {
        return $this->hasMany(UserContact::class)->get();
    }

    public function userUnit(): UserUnit|null
    {
        $userUnit = (new UserUnit())->where('user_id', $this->id)->first();
        if (is_null($userUnit)) {
            return null;
        }
        return $userUnit;
    }

    public function userUnitObject(): null|HealthUnit|SamuUnit
    {
        $userUnit = (new UserUnit())->where('user_id', $this->id)->first();
        if (is_null($userUnit)) {
            return null;
        }
        if ($userUnit->samu_unit_id === null) {
            return (new HealthUnit())->find($userUnit->health_unit_id);
        }
        return (new SamuUnit())->find($userUnit->samu_unit_id);
    }

    public function userRole()
    {
        return (new Role())->find($this->role_id);
    }
}
