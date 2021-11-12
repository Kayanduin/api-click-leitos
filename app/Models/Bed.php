<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin Builder
 */
class Bed extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'bed_type_id',
        'total_beds',
        'free_beds',
        'health_unit_id',
        'created_by'
    ];

    public function getBedType(): string
    {
        return (new BedType())->find($this->bed_type_id)->name;
    }

    public function getBedHealthUnit(): HealthUnit|null
    {
        return (new HealthUnit())->find($this->health_unit_id);
    }
}
