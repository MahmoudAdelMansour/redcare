<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Department extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'avatar',
        'code',
        'user_id',
        'goals',
        'main_responsibilities',

    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'department_id');
    }

    public function head(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function policies(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Policies::class, 'policy_department');
    }


}
