<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Creneau;
use App\Models\RendezVous;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RendezVousController extends Controller
{
    // Le patient reserve un creneau disponible
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'creneau_id' => 'required|exists:creneaux,id',
            'symptomes_description' => 'nullable|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        return DB::transaction(function () use ($request) {
            // lockForUpdate verrouille la ligne le temps de la transaction,
            // ce qui empeche deux patients de reserver le meme creneau en meme temps
            $creneau = Creneau::where('id', $request->creneau_id)->lockForUpdate()->first();

            if (! $creneau || $creneau->statut !== 'disponible') {
                return response()->json(['message' => 'Ce creneau n\'est plus disponible.'], 409);
            }

            $rendezVous = RendezVous::create([
                'patient_id' => $request->user()->id,
                'creneau_id' => $creneau->id,
                'statut' => 'en_attente',
                'symptomes_description' => $request->symptomes_description,
            ]);

            $creneau->update(['statut' => 'reserve']);

            return response()->json($rendezVous->load('creneau.medecinProfile.user'), 201);
        });
    }

    // Le patient voit ses propres rendez-vous
    public function mesRendezVous(Request $request)
    {
        $rendezVous = RendezVous::where('patient_id', $request->user()->id)
            ->with('creneau.medecinProfile.user')
            ->orderByDesc('created_at')
            ->get();

        return response()->json($rendezVous);
    }

    // Le medecin voit les rendez-vous pris sur ses creneaux
    public function rendezVousMedecin(Request $request)
    {
        $medecinProfile = $request->user()->medecinProfile;

        $rendezVous = RendezVous::whereHas('creneau', function ($query) use ($medecinProfile) {
            $query->where('medecin_profile_id', $medecinProfile->id);
        })
            ->with(['creneau', 'patient'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json($rendezVous);
    }

    // Le medecin confirme un rendez-vous (apres paiement, par exemple)
    public function confirmer(Request $request, RendezVous $rendezVous)
    {
        $medecinProfile = $request->user()->medecinProfile;

        if ($rendezVous->creneau->medecin_profile_id !== $medecinProfile->id) {
            return response()->json(['message' => 'Acces refuse.'], 403);
        }

        $rendezVous->update(['statut' => 'confirme']);

        return response()->json($rendezVous->fresh());
    }

    // Annulation par le patient ou le medecin : libere le creneau
    public function annuler(Request $request, RendezVous $rendezVous)
    {
        $user = $request->user();

        $estLePatient = $rendezVous->patient_id === $user->id;
        $estLeMedecin = $user->medecinProfile && $rendezVous->creneau->medecin_profile_id === $user->medecinProfile->id;

        if (! $estLePatient && ! $estLeMedecin) {
            return response()->json(['message' => 'Acces refuse.'], 403);
        }

        return DB::transaction(function () use ($rendezVous) {
            $rendezVous->update(['statut' => 'annule']);
            $rendezVous->creneau->update(['statut' => 'disponible']);

            return response()->json(['message' => 'Rendez-vous annule.']);
        });
    }
}