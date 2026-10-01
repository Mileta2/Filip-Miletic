<?php

namespace Tests\Feature;

use App\Enums\TopicType;
use App\Models\Topic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_sadrzi_zadate_profesore_predmete_i_teme(): void
    {
        $this->seed();

        foreach (['Petar Milić', 'Branimir Jakšić', 'Siniša Ilić', 'Dragiša Miljković', 'Nenad Jovanović', 'Dragana Radosavljević'] as $professor) {
            $this->assertDatabaseHas('users', ['name' => $professor]);
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
    }
}
