<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Student extends Model
{
    protected $guarded = ['id'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'weekday_availability' => 'array',
    ];

    /**
     * Get the user that owns the Student
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
