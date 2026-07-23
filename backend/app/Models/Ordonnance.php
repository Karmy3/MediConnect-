<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ordonnance extends Model
{
    use HasFactory;

    protected $fillable = ['rendez_vous_id', 'contenu', 'medicaments', 'date_emission'];

    protected $casts = [
        'medicaments' => 'array',
        'date_emission' => 'datetime',
    ];

    public function rendezVous(): BelongsTo
    {
        return $this->belongsTo(RendezVous::class);
    }
}