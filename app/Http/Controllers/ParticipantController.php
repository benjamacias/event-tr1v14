<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreParticipantRequest;
use App\Models\Attempt;
use App\Models\Participant;
use App\Services\PlayableQuestionSetPicker;
use App\Services\Settings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
        $deviceIdentifier = $this->deviceIdentifier($request);
        $documentNumber = $request->validated('document_number');
        $email = mb_strtolower(trim($request->validated('email')));
        $phone = preg_replace('/\s+/', '', $request->validated('phone'));

        $playedSets = collect(json_decode($request->cookie('ianus_played_sets', '[]'), true) ?: [])
            ->filter()
            ->map(fn ($id) => (int) $id);

        $playedSets = $playedSets->isEmpty()
            ? $playedSets
            : Attempt::query()
                ->whereIn('question_set_id', $playedSets)
                ->pluck('question_set_id')
                ->map(fn ($id) => (int) $id);

        $playedByDevice = Attempt::query()
            ->where('device_identifier', $deviceIdentifier)
            ->pluck('question_set_id');

        $playedByIdentity = Attempt::query()
            ->whereHas('participant', fn ($query) => $query
                ->where('document_number', $documentNumber)
                ->orWhereRaw('LOWER(email) = ?', [$email])
                ->orWhere('phone', $phone))
            ->pluck('question_set_id');

        $questionSet = $picker->pick(
            $playedSets->merge($playedByDevice)->merge($playedByIdentity)->unique()->values()->all()
        );

        if (! $questionSet) {
            return back()->withErrors(['trivia' => 'La trivia no esta disponible en este momento.'])->withInput();
        }

        $duplicateExists = Attempt::query()
            ->where('question_set_id', $questionSet->id)
            ->where(fn ($query) => $query
                ->where('device_identifier', $deviceIdentifier)
                ->orWhereHas('participant', fn ($participantQuery) => $participantQuery
                    ->where('document_number', $documentNumber)
                    ->orWhereRaw('LOWER(email) = ?', [$email])
                    ->orWhere('phone', $phone)))
            ->exists();

        if ($duplicateExists || $playedSets->contains($questionSet->id) || $playedByDevice->contains($questionSet->id)) {
            return back()->withErrors([
                'trivia' => 'Ya registramos una participacion para este set con esos datos o dispositivo.',
            ])->withInput();
        }

        $attempt = DB::transaction(function () use ($request, $questionSet, $documentNumber, $email, $phone, $deviceIdentifier): Attempt {
            $participant = Participant::create([
                'full_name' => $request->validated('full_name'),
                'document_number' => $documentNumber,
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
                'device_identifier' => $deviceIdentifier,
                'user_agent' => $request->userAgent(),
                'ip_address' => $request->ip(),
            ]);
        });

        $playedSets->push($questionSet->id);

        return redirect()
            ->route('play.show', $attempt)
            ->cookie('ianus_device_id', $deviceIdentifier, 60 * 24 * 365)
            ->cookie('ianus_played_sets', $playedSets->unique()->values()->toJson(), 60 * 24 * 30);
    }

    private function deviceIdentifier(Request $request): string
    {
        $identifier = (string) $request->cookie('ianus_device_id');

        if (Str::isUuid($identifier)) {
            return $identifier;
        }

        return (string) Str::uuid();
    }
}
