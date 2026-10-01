<?php

namespace Database\Seeders;

use App\Enums\CommitteeRole;
use App\Enums\StudyLevel;
use App\Enums\TopicStatus;
use App\Enums\TopicType;
use App\Enums\UserRole;
use App\Models\Topic;
use App\Models\User;
use App\Support\InstitutionalEmail;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::create([
            'name' => 'Sistemski Administrator',
            'email' => 'admin@ftnkm.rs',
            'password' => 'Lozinka123',
            'role' => UserRole::SuperAdmin,
            'must_change_password' => false,
            'is_active' => true,
        ]);

        $professorData = [
            'petar-milic' => [
                'name' => 'Petar Milić',
                'email' => 'petar.milic@ftnkm.rs',
                'area' => 'Računarstvo u oblaku, operativni sistemi, veb tehnologije, elektronska uprava i računarske mreže',
            ],
            'branimir-jaksic' => [
                'name' => 'Branimir Jakšić',
                'email' => 'branimir.jaksic@ftnkm.rs',
                'area' => 'Programiranje u jezicima C i Python',
            ],
            'sinisa-ilic' => [
                'name' => 'Siniša Ilić',
                'email' => 'sinisa.ilic@ftnkm.rs',
                'area' => 'Baze podataka, informacioni sistemi, bezbednost komunikacija i računarstvo u biomedicini',
            ],
            'dragisa-miljkovic' => [
                'name' => 'Dragiša Miljković',
                'email' => 'dragisa.miljkovic@ftnkm.rs',
                'area' => 'Programiranje mobilnih aplikacija',
            ],
            'nenad-jovanovic' => [
                'name' => 'Nenad Jovanović',
                'email' => 'nenad.jovanovic@ftnkm.rs',
                'area' => 'Internet aplikacije, objektno orijentisano i distribuirano programiranje',
            ],
            'dragana-radosavljevic' => [
                'name' => 'Dragana Radosavljević',
                'email' => 'dragana.radosavljevic@ftnkm.rs',
                'area' => 'Istraživanje podataka i infrastruktura za elektronsko poslovanje',
            ],
        ];

        $professors = collect($professorData)->mapWithKeys(function (array $data, string $key) {
            $professor = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => 'Lozinka123',
                'role' => UserRole::Professor,
                'must_change_password' => false,
                'is_active' => true,
            ]);
            $professor->professorProfile()->create([
                'academic_title' => 'Profesor',
                'department' => 'Katedra za računarstvo i informatiku',
                'research_area' => $data['area'],
            ]);

            return [$key => $professor];
        });

        $studentNames = [
            'Ana Nikolić', 'Filip Đorđević', 'Luka Milošević', 'Sara Pavlović', 'Stefan Ristić',
            'Mina Marković', 'Vuk Popović', 'Teodora Đukić', 'Ognjen Lazić', 'Iva Kovačević',
        ];
        $students = collect($studentNames)->map(function (string $name, int $index) {
            $level = $index < 5 ? StudyLevel::Undergraduate : StudyLevel::Master;
            $indexNumber = (101 + $index).'/'.str_pad((string) (22 + ($index % 3)), 2, '0', STR_PAD_LEFT);
            $nameParts = explode(' ', $name);
            $firstName = array_shift($nameParts);
            $lastName = array_pop($nameParts);
            $student = User::create([
                'name' => $name,
                'email' => InstitutionalEmail::forStudent($firstName, $lastName, $indexNumber),
                'password' => 'Lozinka123',
                'role' => UserRole::Student,
                'must_change_password' => false,
                'is_active' => true,
            ]);
            $student->studentProfile()->create([
                'index_number' => $indexNumber,
                'study_level' => $level,
                'date_of_birth' => now()->subYears(22 + ($index % 4))->subDays($index * 17)->toDateString(),
                'city' => ['Beograd', 'Novi Sad', 'Niš', 'Kragujevac', 'Čačak'][$index % 5],
                'study_year' => $level === StudyLevel::Master ? 2 : 4,
            ]);

            return $student;
        });

        $topicData = [
            TopicType::Undergraduate->value => [
                ['mentor' => 'petar-milic', 'course' => 'Računarstvo u oblaku', 'title' => 'Projektovanje skalabilne veb aplikacije u okruženju računarstva u oblaku'],
                ['mentor' => 'petar-milic', 'course' => 'Računarstvo u oblaku', 'title' => 'Automatizovano raspoređivanje kontejnerskih servisa u oblaku'],
                ['mentor' => 'petar-milic', 'course' => 'Operativni sistemi 1', 'title' => 'Simulacija upravljanja procesima i raspoređivanja procesora'],
                ['mentor' => 'petar-milic', 'course' => 'Operativni sistemi 1', 'title' => 'Analiza algoritama upravljanja memorijom u operativnim sistemima'],
                ['mentor' => 'petar-milic', 'course' => 'Operativni sistemi 2', 'title' => 'Implementacija sistema za nadzor resursa u Linux okruženju'],
                ['mentor' => 'petar-milic', 'course' => 'Operativni sistemi 2', 'title' => 'Projektovanje mehanizma sinhronizacije konkurentnih procesa'],
                ['mentor' => 'petar-milic', 'course' => 'Web dizajn', 'title' => 'Projektovanje pristupačnog responzivnog sajta fakulteta'],
                ['mentor' => 'petar-milic', 'course' => 'Web dizajn', 'title' => 'Razvoj sistema dizajna za akademske veb aplikacije'],
                ['mentor' => 'branimir-jaksic', 'course' => 'Programiranje 1', 'title' => 'Implementacija konzolnog sistema za evidenciju studentskih rezultata u jeziku C'],
                ['mentor' => 'branimir-jaksic', 'course' => 'Programiranje 1', 'title' => 'Razvoj biblioteke struktura podataka u programskom jeziku C'],
                ['mentor' => 'branimir-jaksic', 'course' => 'Programiranje 2', 'title' => 'Razvoj Python aplikacije za analizu studentskih podataka'],
                ['mentor' => 'branimir-jaksic', 'course' => 'Programiranje 2', 'title' => 'Automatizacija obrade dokumenata primenom jezika Python'],
                ['mentor' => 'sinisa-ilic', 'course' => 'Baze podataka 1', 'title' => 'Projektovanje relacione baze podataka za studentsku službu'],
                ['mentor' => 'sinisa-ilic', 'course' => 'Baze podataka 1', 'title' => 'Sistem evidencije bibliotečkog fonda zasnovan na SQL bazi'],
                ['mentor' => 'sinisa-ilic', 'course' => 'Baze podataka 2', 'title' => 'Optimizacija upita i indeksiranje u sistemima za upravljanje bazama podataka'],
                ['mentor' => 'sinisa-ilic', 'course' => 'Baze podataka 2', 'title' => 'Projektovanje skladišta podataka za analizu uspeha studenata'],
                ['mentor' => 'sinisa-ilic', 'course' => 'Bezbednost računarskih komunikacija', 'title' => 'Analiza bezbednosti bežičnih računarskih mreža'],
                ['mentor' => 'sinisa-ilic', 'course' => 'Bezbednost računarskih komunikacija', 'title' => 'Sistem za detekciju sumnjivog saobraćaja u lokalnoj mreži'],
                ['mentor' => 'sinisa-ilic', 'course' => 'Informacioni sistemi', 'title' => 'Informacioni sistem za prijavu i praćenje stručne prakse'],
                ['mentor' => 'sinisa-ilic', 'course' => 'Informacioni sistemi', 'title' => 'Modelovanje informacionog sistema za upravljanje nastavnim procesom'],
                ['mentor' => 'dragisa-miljkovic', 'course' => 'Programiranje mobilnih aplikacija', 'title' => 'Mobilna aplikacija za obaveštenja o nastavnim aktivnostima'],
                ['mentor' => 'dragisa-miljkovic', 'course' => 'Programiranje mobilnih aplikacija', 'title' => 'Razvoj mobilne aplikacije za evidenciju laboratorijske opreme'],
                ['mentor' => 'nenad-jovanovic', 'course' => 'Programiranje internet aplikacija', 'title' => 'Veb aplikacija za zakazivanje konsultacija'],
                ['mentor' => 'nenad-jovanovic', 'course' => 'Programiranje internet aplikacija', 'title' => 'Internet aplikacija za upravljanje studentskim projektima'],
                ['mentor' => 'nenad-jovanovic', 'course' => 'OOP 1', 'title' => 'Objektno orijentisani sistem za evidenciju ispita'],
                ['mentor' => 'nenad-jovanovic', 'course' => 'OOP 1', 'title' => 'Primena obrazaca projektovanja u aplikaciji za rezervaciju termina'],
                ['mentor' => 'nenad-jovanovic', 'course' => 'OOP 2', 'title' => 'Višeslojna poslovna aplikacija za upravljanje nastavnim materijalima'],
                ['mentor' => 'nenad-jovanovic', 'course' => 'OOP 2', 'title' => 'Sistem dodataka za proširivu desktop aplikaciju'],
                ['mentor' => 'nenad-jovanovic', 'course' => 'Konkurentno i distribuirano programiranje', 'title' => 'Distribuirani sistem za obradu studentskih prijava'],
                ['mentor' => 'nenad-jovanovic', 'course' => 'Konkurentno i distribuirano programiranje', 'title' => 'Simulacija konkurentne obrade zahteva u mrežnom servisu'],
            ],
            TopicType::Master->value => [
                ['mentor' => 'petar-milic', 'course' => 'Tehnologija e-uprave', 'title' => 'Interoperabilnost servisa elektronske uprave primenom API pristupa'],
                ['mentor' => 'petar-milic', 'course' => 'Tehnologija e-uprave', 'title' => 'Sistem elektronskog podnošenja i praćenja upravnih zahteva'],
                ['mentor' => 'petar-milic', 'course' => 'Računarske mreže 2', 'title' => 'Softverski definisane mreže za upravljanje akademskom infrastrukturom'],
                ['mentor' => 'petar-milic', 'course' => 'Računarske mreže 2', 'title' => 'Analiza kvaliteta servisa u savremenim računarskim mrežama'],
                ['mentor' => 'sinisa-ilic', 'course' => 'Računarstvo u biomedicini', 'title' => 'Analiza biomedicinskih signala metodama mašinskog učenja'],
                ['mentor' => 'sinisa-ilic', 'course' => 'Računarstvo u biomedicini', 'title' => 'Informacioni sistem za obradu i vizuelizaciju medicinskih podataka'],
                ['mentor' => 'dragana-radosavljevic', 'course' => 'Istraživanje podataka', 'title' => 'Prediktivna analiza uspeha studenata primenom tehnika istraživanja podataka'],
                ['mentor' => 'dragana-radosavljevic', 'course' => 'Istraživanje podataka', 'title' => 'Otkrivanje obrazaca u skupovima podataka elektronskog poslovanja'],
                ['mentor' => 'dragana-radosavljevic', 'course' => 'Infrastruktura za elektronsko poslovanje', 'title' => 'Projektovanje visoko dostupne infrastrukture za elektronsko poslovanje'],
                ['mentor' => 'dragana-radosavljevic', 'course' => 'Infrastruktura za elektronsko poslovanje', 'title' => 'Bezbedna kontejnerska platforma za sisteme elektronskog poslovanja'],
            ],
        ];

        foreach ($topicData as $typeValue => $topics) {
            $type = TopicType::from($typeValue);
            $studentOffset = $type === TopicType::Undergraduate ? 0 : 5;
            $occupiedStart = count($topics) - 5;

            foreach ($topics as $index => $data) {
                // poslednjih pet tema svakog nivoa prikazuje tok od rezervacije do odbrane
                $status = $index < $occupiedStart
                    ? TopicStatus::Available
                    : ($index < $occupiedStart + 3 ? TopicStatus::Reserved : TopicStatus::Defended);
                $student = $status === TopicStatus::Available
                    ? null
                    : $students[$studentOffset + $index - $occupiedStart];
                $mentor = $professors[$data['mentor']];

                $topic = Topic::create([
                    'title' => $data['title'],
                    'course' => $data['course'],
                    'description' => "Rad iz predmeta {$data['course']} obuhvata analizu stručnih zahteva, projektovanje rešenja, praktičnu implementaciju i proveru rezultata. Student dokumentuje primenjene metode, arhitekturu sistema i rezultate sprovedenog ispitivanja.",
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
