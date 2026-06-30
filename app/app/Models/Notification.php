<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table      = 'Notifications';
    protected $primaryKey = 'IdNotification';
    public    $timestamps = false;

    protected $fillable = [
        'IdUser',
        'Type',
        'Title',
        'Message',
        'Link',
        'IsRead',
        'IdReference',
        'CreatedAt',
    ];

    protected $casts = [
        'IsRead'    => 'boolean',
        'CreatedAt' => 'datetime',
    ];

    // Relationships

    public function user()
    {
        return $this->belongsTo(User::class, 'IdUser', 'IdUser');
    }

    // Helpers

    public function getIconAttribute(): string
    {
        return match ($this->Type) {
            'new_product'  => 'bx-store',
            'order_update' => 'bx-package',
            'follow'       => 'bx-user-plus',
            default        => 'bx-bell',
        };
    }
}
