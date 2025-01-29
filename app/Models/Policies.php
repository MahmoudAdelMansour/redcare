<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Policies extends Model
{
    use HasFactory;

    protected $fillable = [
        'policy_name',
        'policy_number',
        'description',
        'purpose',
        'version',
        'details',
        'link',
        'attachment',
        'status',
        'approval',
        'compliance',
        'notes',
        'user_id',
    ];

    public function departments(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Department::class, 'policy_department');
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

}
