<?php

namespace Database\Seeders;

use App\Models\AnswerOption;
use App\Models\ProviderLogo;
use App\Models\Question;
use App\Models\QuestionSet;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedSettings();
        $this->seedQuestionSets();
        $this->seedProviderLogos();
        $this->seedAdmin();
    }

    private function seedSettings(): void
    {
        $settings = [
            ['key' => 'initial_message', 'value' => 'Gracias por jugar con Ianus SA!!', 'type' => 'text'],
            ['key' => 'form_text', 'value' => 'Por favor, dejanos tus datos:', 'type' => 'text'],
            ['key' => 'final_message_partial', 'value' => 'Gracias por participar! Respondiste :score/5 preguntas correctamente!', 'type' => 'text'],
            ['key' => 'final_message_perfect', 'value' => 'Felicitaciones!! Respondiste todo perfecto! Tu tiempo final fue de :time. Estás participando por el premio final!', 'type' => 'text'],
            ['key' => 'qr_print_text', 'value' => 'Escaneá el código QR para participar de la trivia de Ianus SA.', 'type' => 'text'],
            ['key' => 'qr_target_url', 'value' => null, 'type' => 'text'],
            ['key' => 'main_logo_path', 'value' => 'logos/ianus.svg', 'type' => 'image'],
            ['key' => 'event_active', 'value' => '1', 'type' => 'boolean'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }

    private function seedQuestionSets(): void
    {
        foreach (range(1, 3) as $setNumber) {
            $set = QuestionSet::updateOrCreate(
                ['slug' => 'set-'.$setNumber],
                ['name' => 'Set '.$setNumber, 'is_active' => true]
            );

            foreach (range(1, 5) as $questionNumber) {
                $question = Question::updateOrCreate(
                    ['question_set_id' => $set->id, 'sort_order' => $questionNumber],
                    [
                        'text' => "Pregunta {$questionNumber} del {$set->name}: ¿cuál es la opción correcta?",
                        'explanation' => 'Esta aclaración se puede editar desde el panel administrador.',
                        'is_active' => true,
                    ]
                );

                foreach (['A', 'B', 'C'] as $index => $label) {
                    AnswerOption::updateOrCreate(
                        ['question_id' => $question->id, 'label' => $label],
                        [
                            'text' => $index === 0 ? 'La opción correcta de ejemplo' : 'Una opción distractora',
                            'is_correct' => $index === 0,
                            'explanation' => $index === 0
                                ? 'Correcto: esta es la explicación cargada para la respuesta.'
                                : 'La explicación ayuda a reforzar el aprendizaje luego de responder.',
                            'sort_order' => $index + 1,
                        ]
                    );
                }
            }
        }
    }

    private function seedProviderLogos(): void
    {
        ProviderLogo::query()
            ->where('image_path', 'like', 'logos/provider-%')
            ->update(['is_active' => false]);

        foreach (['Publicidad Ianus Diagnostico', 'Publicidad Soluciones Medicas', 'Publicidad Evento Stand'] as $index => $name) {
            ProviderLogo::updateOrCreate(
                ['name' => $name],
                [
                    'image_path' => 'logos/ad-'.($index + 1).'.svg',
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ]
            );
        }
    }

    private function seedAdmin(): void
    {
        User::updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@ianus.local')],
            [
                'name' => env('ADMIN_NAME', 'Ianus Admin'),
                'password' => Hash::make(env('ADMIN_PASSWORD', 'password')),
            ]
        );
    }
}
