<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedecinProfile extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'specialite', 'tarif_consultation', 'bio'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creneaux(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Creneau::class);
    }
}