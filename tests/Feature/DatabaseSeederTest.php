<?php

namespace Tests\Feature;

use App\Enums\TopicType;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_sadrzi_zadate_profesore_predmete_i_teme(): void
    {
        $this->seed();

        $professors = [
            'Petar Milić' => 'petar.milic@ftnkm.rs',
            'Branimir Jakšić' => 'branimir.jaksic@ftnkm.rs',
            'Siniša Ilić' => 'sinisa.ilic@ftnkm.rs',
            'Dragiša Miljković' => 'dragisa.miljkovic@ftnkm.rs',
            'Nenad Jovanović' => 'nenad.jovanovic@ftnkm.rs',
            'Dragana Radosavljević' => 'dragana.radosavljevic@ftnkm.rs',
        ];

        foreach ($professors as $name => $email) {
            $this->assertDatabaseHas('users', compact('name', 'email'));
        }

        $courses = [
            'Računarstvo u oblaku',
            'Operativni sistemi 1',
            'Operativni sistemi 2',
            'Web dizajn',
            'Programiranje 1',
            'Programiranje 2',
            'Baze podataka 1',
            'Baze podataka 2',
            'Bezbednost računarskih komunikacija',
            'Informacioni sistemi',
            'Programiranje mobilnih aplikacija',
            'Programiranje internet aplikacija',
            'OOP 1',
            'OOP 2',
            'Konkurentno i distribuirano programiranje',
            'Tehnologija e-uprave',
            'Računarske mreže 2',
            'Računarstvo u biomedicini',
            'Istraživanje podataka',
            'Infrastruktura za elektronsko poslovanje',
        ];

        foreach ($courses as $course) {
            $this->assertSame(2, Topic::where('course', $course)->count(), "Predmet {$course} mora imati dve teme.");
        }

        $this->assertSame(30, Topic::where('type', TopicType::Undergraduate)->count());
        $this->assertSame(10, Topic::where('type', TopicType::Master)->count());
        $this->assertSame(0, User::where('email', 'not like', '%@ftnkm.rs')->count());
    }
}
