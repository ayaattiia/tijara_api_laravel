<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $table      = 'Reviews';
    protected $primaryKey = 'IdReview';
    public    $timestamps = false;

    protected $fillable = [
        'IdUser',
        'TargetType',
        'TargetId',
        'Rating',
        'Comment',
        'Active',
        'CreatedAt',
    ];

    protected $casts = [
        'Rating'    => 'integer',
        'Active'    => 'boolean',
        'CreatedAt' => 'datetime',
    ];

    // Relationships

    public function author()
    {
        return $this->belongsTo(User::class, 'IdUser', 'IdUser');
    }

    /**
     * Polymorphic-style helper: resolve the reviewed entity.
     * Usage: $review->target() — returns Deal, User, etc.
     */
    public function target(): ?Model
    {
        return match ($this->TargetType) {
            'vendor' => User::find($this->TargetId),
            default  => null,
        };
    }
}
