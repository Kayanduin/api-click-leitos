<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class User extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected array $fillable = [
        'name',
        'email',
        'password',
        'cpf'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected array $hidden = [
        'password',
    ];

    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */
    protected array $visible = [
        'id',
        'name',
        'email',
        'password',
        'cpf',
        'created_at',
        'updated_at'
    ];

}
