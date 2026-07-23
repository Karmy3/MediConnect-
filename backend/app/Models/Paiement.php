<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Paiement extends Model
{
    use HasFactory;

    protected $fillable = ['rendez_vous_id', 'montant', 'stripe_payment_id', 'statut'];

    public function rendezVous(): BelongsTo
    {
        return $this->belongsTo(RendezVous::class);
    }
}