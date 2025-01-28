<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shift extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'status',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'shift_id');
    }

    protected function casts(): array
    {
        return [
            'start_time' => 'timestamp',
            'end_time' => 'timestamp',
            'status' => 'boolean',
        ];
    }

}
