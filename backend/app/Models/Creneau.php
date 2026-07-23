<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Creneau extends Model
{
    use HasFactory;

    protected $table = 'creneaux';

    protected $fillable = ['medecin_profile_id', 'date_debut', 'date_fin', 'statut'];

    protected $casts = [
        'date_debut' => 'datetime',
        'date_fin' => 'datetime',
    ];

    public function medecinProfile(): BelongsTo
    {
        return $this->belongsTo(MedecinProfile::class);
    }

    public function rendezVous(): HasOne
    {
        return $this->hasOne(RendezVous::class);
    }
}