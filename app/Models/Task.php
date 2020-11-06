<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class Task extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tasks';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'owner_id',
        'reporter_id',
        'title',
        'description',
    ];

    /**
     * Get assignee user data
     *
     * @return HasOne
     */
    public function assignee(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'owner_id');
    }

    /**
     * Get reporter user data
     *
     * @return HasOne
     */
    public function reporter(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'reporter_id');
    }

    /**
     * User filters
     *
     * @param Builder $builder Eloquent builder.
     * @param array $filters Filters.
     * @return Builder
     */
    public function scopeFilter(Builder $builder, array $filters): Builder
    {
        // By assignee user identification
        if (Arr::has($filters, 'ownerId')) {
            $builder->where('owner_id', $filters['ownerId']);
        }

        // By reporter user identification
        if (Arr::has($filters, 'reporterId')) {
            $builder->where('reporter_id', $filters['reporterId']);
        }

        return $builder;
    }
}
