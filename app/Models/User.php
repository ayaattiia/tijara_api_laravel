<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * PRIORITÉ 1 — CORRIGÉE
 *
 * L'ancien User.php était le modèle Laravel par défaut (name, email uniquement).
 * Il ne correspondait pas au schéma réel du projet (IdUser, FirstName, LastName,
 * Role, Telephone…). Tous les contrôleurs qui faisaient auth()->user()->IdUser
 * obtenaient null. Rebuildt pour correspondre exactement à la table Users.
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $table      = 'Users';
    protected $primaryKey = 'IdUser';
    public    $timestamps = false;

    protected $fillable = [
        'FirstName',
        'LastName',
        'email',
        'password',
        'Telephone',
        'Address',
        'Role',
        'Active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'Active'            => 'boolean',
            'CreatedAt'         => 'datetime',
            'UpdatedAt'         => 'datetime',
        ];
    }

    // ── Accessors ────────────────────────────────────────────────

    /** Nom complet pour l'affichage */
    public function getNameAttribute(): string
    {
        return trim("{$this->FirstName} {$this->LastName}");
    }

    public function isAdmin(): bool  { return $this->Role === 'admin'; }
    public function isVendor(): bool { return $this->Role === 'vendor'; }
    public function isUser(): bool   { return $this->Role === 'user'; }

    // ── Relationships ────────────────────────────────────────────

    public function deals()
    {
        return $this->hasMany(Deal::class, 'IdUser', 'IdUser');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'IdUser', 'IdUser');
    }

    public function supplier()
    {
        return $this->hasOne(Supplier::class, 'IdUser', 'IdUser');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'IdUser', 'IdUser');
    }

    /** Avis écrits par cet utilisateur */
    public function reviewsWritten()
    {
        return $this->hasMany(Review::class, 'IdUser', 'IdUser');
    }

    /** Avis reçus en tant que vendeur */
    public function reviewsReceived()
    {
        return $this->hasMany(Review::class, 'TargetId', 'IdUser')
                    ->where('TargetType', 'vendor');
    }

    public function preInvoicesAsBuyer()
    {
        return $this->hasMany(PreInvoice::class, 'IdUser', 'IdUser');
    }

    public function preInvoicesAsVendor()
    {
        return $this->hasMany(PreInvoice::class, 'IdVendor', 'IdUser');
    }

    /** Note moyenne en tant que fournisseur */
    public function getAverageRatingAttribute(): float
    {
        return round((float) $this->reviewsReceived()->avg('Rating'), 1);
    }
}
