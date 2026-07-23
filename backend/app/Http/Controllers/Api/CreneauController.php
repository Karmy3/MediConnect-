<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Creneau;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CreneauController extends Controller
{
    // Le medecin cree un creneau de disponibilite
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date_debut' => 'required|date|after:now',
            'date_fin' => 'required|date|after:date_debut',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $medecinProfile = $request->user()->medecinProfile;

        if (! $medecinProfile) {
            return response()->json(['message' => 'Profil medecin introuvable.'], 404);
        }

        $creneau = $medecinProfile->creneaux()->create([
            'date_debut' => $request->date_debut,
            'date_fin' => $request->date_fin,
            'statut' => 'disponible',
        ]);

        return response()->json($creneau, 201);
    }

    // Le medecin voit tous ses propres creneaux (disponibles et reserves)
    public function mesCreneaux(Request $request)
    {
        $medecinProfile = $request->user()->medecinProfile;

        return response()->json(
            $medecinProfile->creneaux()->orderBy('date_debut')->get()
        );
    }

    // N'importe qui (patient) voit les creneaux disponibles d'un medecin donne
    public function creneauxDisponibles(int $medecinProfileId)
    {
        $creneaux = Creneau::where('medecin_profile_id', $medecinProfileId)
            ->where('statut', 'disponible')
            ->where('date_debut', '>', now())
            ->orderBy('date_debut')
            ->get();

        return response()->json($creneaux);
    }

    // Le medecin supprime un creneau (uniquement s'il n'est pas reserve)
    public function destroy(Request $request, Creneau $creneau)
    {
        if ($creneau->medecin_profile_id !== $request->user()->medecinProfile->id) {
            return response()->json(['message' => 'Acces refuse.'], 403);
        }

        if ($creneau->statut === 'reserve') {
            return response()->json(['message' => 'Impossible de supprimer un creneau deja reserve.'], 422);
        }

        $creneau->delete();

        return response()->json(['message' => 'Creneau supprime.']);
    }
}