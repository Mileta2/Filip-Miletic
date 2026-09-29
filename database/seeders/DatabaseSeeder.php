<?php

namespace Database\Seeders;

use App\Enums\CommitteeRole;
use App\Enums\StudyLevel;
use App\Enums\TopicStatus;
use App\Enums\TopicType;
use App\Enums\UserRole;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::create([
            'name' => 'Sistemski Administrator',
            'email' => 'admin@example.test',
            'password' => 'Lozinka123',
            'role' => UserRole::SuperAdmin,
            'must_change_password' => false,
            'is_active' => true,
        ]);

        $professorData = [
            ['Dr Aleksandar Jovanović', 'profesor@example.test', 'Redovni profesor', 'Informacioni sistemi', 'Web sistemi i baze podataka'],
            ['Dr Milica Petrović', 'milica.petrovic@example.test', 'Vanredni profesor', 'Računarsko inženjerstvo', 'Veštačka inteligencija i mašinsko učenje'],
            ['Dr Nikola Ilić', 'nikola.ilic@example.test', 'Docent', 'Računarske mreže', 'Cyber security i cloud sistemi'],
            ['Dr Jelena Savić', 'jelena.savic@example.test', 'Docent', 'Softversko inženjerstvo', 'Distribuirani i mobilni sistemi'],
            ['Dr Marko Stanković', 'marko.stankovic@example.test', 'Vanredni profesor', 'Automatika', 'IoT i embedded sistemi'],
        ];

        $professors = collect($professorData)->map(function (array $data) {
            $professor = User::create([
                'name' => $data[0],
                'email' => $data[1],
                'password' => 'Lozinka123',
                'role' => UserRole::Professor,
                'must_change_password' => false,
                'is_active' => true,
            ]);
            $professor->professorProfile()->create([
                'academic_title' => $data[2],
                'department' => $data[3],
                'research_area' => $data[4],
            ]);

            return $professor;
        });

        $studentNames = [
            'Ana Nikolić', 'Filip Đorđević', 'Luka Milošević', 'Sara Pavlović', 'Stefan Ristić',
            'Mina Marković', 'Vuk Popović', 'Teodora Đukić', 'Ognjen Lazić', 'Iva Kovačević',
        ];
        $students = collect($studentNames)->map(function (string $name, int $index) {
            $level = $index < 5 ? StudyLevel::Undergraduate : StudyLevel::Master;
            $student = User::create([
                'name' => $name,
                'email' => $index === 0 ? 'student@example.test' : 'student'.($index + 1).'@example.test',
                'password' => 'Lozinka123',
                'role' => UserRole::Student,
                'must_change_password' => false,
                'is_active' => true,
            ]);
            $student->studentProfile()->create([
                'index_number' => 'IT '.(2021 + ($index % 3)).'/'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                'study_level' => $level,
                'date_of_birth' => now()->subYears(22 + ($index % 4))->subDays($index * 17)->toDateString(),
                'city' => ['Beograd', 'Novi Sad', 'Niš', 'Kragujevac', 'Čačak'][$index % 5],
                'study_year' => $level === StudyLevel::Master ? 2 : 4,
            ]);

            return $student;
        });

        $undergraduateTitles = [
            'Razvoj informacionog sistema za studentsku službu',
            'Web aplikacija za upravljanje sportskim klubom',
            'Sistem za nadzor lokalne računarske mreže',
            'Prepoznavanje objekata primenom neuronskih mreža',
            'Bezbednost REST API servisa',
            'IoT sistem za pametno navodnjavanje',
            'Projektovanje baze podataka za biblioteku',
            'Mobilna aplikacija za gradski prevoz',
            'GIS prikaz turističkih lokacija',
            'Analiza otvorenih skupova podataka',
        ];
        $masterTitles = [
            'Mikroservisna arhitektura u cloud okruženju',
            'Detekcija anomalija metodama mašinskog učenja',
            'Napredni sistem preporuke obrazovnog sadržaja',
            'Digitalni blizanac embedded uređaja',
            'Analiza bezbednosti distribuiranih sistema',
            'Obrada velikih podataka u realnom vremenu',
            'Primena veštačke inteligencije u medicinskoj dijagnostici',
            'Optimizacija kontejnerskih aplikacija',
            'Semantička pretraga akademskih radova',
            'Prediktivno održavanje industrijskih IoT sistema',
        ];

        foreach ([TopicType::Undergraduate->value => $undergraduateTitles, TopicType::Master->value => $masterTitles] as $typeValue => $titles) {
            $type = TopicType::from($typeValue);
            $studentOffset = $type === TopicType::Undergraduate ? 0 : 5;

            foreach ($titles as $index => $title) {
                $status = $index < 5
                    ? TopicStatus::Available
                    : ($index < 8 ? TopicStatus::Reserved : TopicStatus::Defended);
                $student = $status === TopicStatus::Available ? null : $students[$studentOffset + $index - 5];
                $mentor = $professors[$index % $professors->count()];

                $topic = Topic::create([
                    'title' => $title,
                    'description' => 'Tema obuhvata analizu zahteva, projektovanje rešenja, praktičnu implementaciju i proveru rezultata u realnom okruženju. Student će dokumentovati arhitekturu, korišćene tehnologije i ključne odluke tokom razvoja.',
                    'type' => $type,
                    'status' => $status,
                    'mentor_id' => $mentor->id,
                    'student_id' => $student?->id,
                    'reserved_at' => $student ? now()->subMonths(3)->addDays($index) : null,
                    'defended_at' => $status === TopicStatus::Defended ? now()->subDays(15 + $index)->toDateString() : null,
                ]);

                if ($status === TopicStatus::Defended) {
                    $committee = $professors->where('id', '!=', $mentor->id)->values();
                    $topic->committeeMembers()->create([
                        'professor_id' => $committee[0]->id,
                        'role' => CommitteeRole::President,
                    ]);
                    $topic->committeeMembers()->create([
                        'professor_id' => $committee[1]->id,
                        'role' => CommitteeRole::Member,
                    ]);
                }
            }
        }
    }
}
