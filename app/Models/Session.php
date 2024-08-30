<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Session extends Model
{
    protected $guarded = ['id'];

    /**
     * Get the student(user) associated with the session.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
