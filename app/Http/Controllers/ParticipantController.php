<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreParticipantRequest;
use App\Models\Attempt;
use App\Models\Participant;
use App\Services\PlayableQuestionSetPicker;
use App\Services\Settings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ParticipantController extends Controller
{
    public function create(Settings $settings): View
    {
        return view('participant.create', [
            'initialMessage' => $settings->get('initial_message', 'Gracias por jugar con Ianus SA!!'),
            'formText' => $settings->get('form_text', 'Por favor, dejanos tus datos:'),
            'consentText' => 'Al participar, acepto que Ianus SA utilice mis datos exclusivamente para la gestion de la trivia, sorteo y contacto relacionado al evento.',
        ]);
    }

    public function store(StoreParticipantRequest $request, PlayableQuestionSetPicker $picker): RedirectResponse
    {
        $email = mb_strtolower(trim($request->validated('email')));
        $phone = preg_replace('/\s+/', '', $request->validated('phone'));

        $playedSets = collect(json_decode($request->cookie('ianus_played_sets', '[]'), true) ?: [])
            ->filter()
            ->map(fn ($id) => (int) $id);

        $playedByIdentity = Attempt::query()
            ->whereHas('participant', fn ($query) => $query
                ->whereRaw('LOWER(email) = ?', [$email])
                ->orWhere('phone', $phone))
            ->pluck('question_set_id');

        $questionSet = $picker->pick(
            $playedSets->merge($playedByIdentity)->unique()->values()->all()
        );

        if (! $questionSet) {
            return back()->withErrors(['trivia' => 'La trivia no esta disponible en este momento.'])->withInput();
        }

        // Sin DNI no se puede garantizar identidad unica absoluta; esta validacion bloquea duplicados razonables por email/celular y set.
        $duplicateExists = Attempt::query()
            ->where('question_set_id', $questionSet->id)
            ->whereHas('participant', fn ($query) => $query
                ->whereRaw('LOWER(email) = ?', [$email])
                ->orWhere('phone', $phone))
            ->exists();

        if ($duplicateExists || $playedSets->contains($questionSet->id)) {
            return back()->withErrors([
                'trivia' => 'Ya registramos una participacion para este set con esos datos o dispositivo.',
            ])->withInput();
        }

        $attempt = DB::transaction(function () use ($request, $questionSet, $email, $phone): Attempt {
            $participant = Participant::create([
                'full_name' => $request->validated('full_name'),
                'email' => $email,
                'phone' => $phone,
                'institution_role' => $request->validated('institution_role'),
                'consent_accepted' => true,
            ]);

            return Attempt::create([
                'participant_id' => $participant->id,
                'question_set_id' => $questionSet->id,
                'status' => Attempt::STATUS_STARTED,
                'started_at' => now(),
                'duplicate_flag' => false,
                'user_agent' => $request->userAgent(),
                'ip_address' => $request->ip(),
            ]);
        });

        $playedSets->push($questionSet->id);

        return redirect()
            ->route('play.show', $attempt)
            ->cookie('ianus_played_sets', $playedSets->unique()->values()->toJson(), 60 * 24 * 30);
    }
}
