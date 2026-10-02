<?php

namespace Tests\Feature;

use App\Enums\StudyLevel;
use App\Enums\TopicType;
use App\Enums\UserRole;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentVisibilityAndProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_neprijavljen_korisnik_vidi_obe_vrste_radova(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Diplomski radovi')
            ->assertSee('Master radovi');
    }

    public function test_student_osnovnih_studija_vidi_samo_diplomske_radove(): void
    {
        $student = User::factory()->student(StudyLevel::Undergraduate)->create();
        $diplomaTopic = Topic::factory()->create([
            'title' => 'Tema dostupna osnovnim studijama',
            'type' => TopicType::Undergraduate,
        ]);
        Topic::factory()->create([
            'title' => 'Tema dostupna master studijama',
            'type' => TopicType::Master,
        ]);

        $this->actingAs($student)->get(route('home'))
            ->assertOk()
            ->assertSee('Pregled tema za diplomske radove studenata osnovnih studija.')
            ->assertDontSee('Pregled tema za završne radove studenata master studija.')
            ->assertSee('Moja tema')
            ->assertDontSee('<a class="nav-link" href="'.route('topics.index', ['status' => 'available']).'">Teme</a>', false);

        $this->actingAs($student)->get(route('topics.master'))
            ->assertRedirect(route('topics.undergraduate'));

        $masterTopic = Topic::factory()->create(['type' => TopicType::Master]);
        $this->actingAs($student)->get(route('topics.show', $masterTopic))
            ->assertRedirect(route('topics.undergraduate'));

        $this->actingAs($student)->get(route('topics.index', ['type' => TopicType::Master->value]))
            ->assertOk()
            ->assertSee($diplomaTopic->title)
            ->assertDontSee('Tema dostupna master studijama');
    }

    public function test_master_student_vidi_samo_master_radove(): void
    {
        $student = User::factory()->student(StudyLevel::Master)->create();

        $this->actingAs($student)->get(route('home'))
            ->assertOk()
            ->assertSee('Pregled tema za završne radove studenata master studija.')
            ->assertDontSee('Pregled tema za diplomske radove studenata osnovnih studija.');

        $this->actingAs($student)->get(route('topics.undergraduate'))
            ->assertRedirect(route('topics.master'));
    }

    public function test_student_ne_moze_da_promeni_godinu_studija_preko_profila(): void
    {
        $student = User::factory()->student(StudyLevel::Undergraduate)->create();
        $student->studentProfile->update([
            'date_of_birth' => '2002-05-14',
            'study_year' => 2,
        ]);

        $this->actingAs($student)->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Menja administrator.')
            ->assertDontSee('name="study_year"', false);

        $this->actingAs($student)->put(route('profile.update'), [
            'name' => $student->name,
            'date_of_birth' => '2002-05-14',
            'phone' => '0641234567',
            'city' => 'Kosovska Mitrovica',
            'address' => 'Kneza Miloša 7',
            'study_year' => 4,
        ])->assertSessionHas('success');

        $this->assertSame(2, $student->studentProfile->fresh()->study_year);
    }

    public function test_student_ne_moze_da_promeni_ime_i_prezime_preko_profila(): void
    {
        $student = User::factory()->student()->create(['name' => 'Petar Petrović']);
        $student->studentProfile->update(['date_of_birth' => '2002-05-14']);

        $this->actingAs($student)->put(route('profile.update'), [
            'name' => 'Promenjeno Ime',
            'date_of_birth' => '2002-05-14',
        ])->assertSessionHas('success');

        $this->assertSame('Petar Petrović', $student->fresh()->name);
    }

    public function test_profesor_ne_moze_da_promeni_sluzbene_podatke_preko_profila(): void
    {
        $professor = User::factory()->professor()->create(['name' => 'Petar Milić']);
        $professor->professorProfile->update([
            'academic_title' => 'Vanredni profesor',
            'department' => 'Katedra za računarstvo',
        ]);

        $this->actingAs($professor)->put(route('profile.update'), [
            'name' => 'Promenjeno Ime',
            'academic_title' => 'Asistent',
            'department' => 'Druga katedra',
            'research_area' => 'Računarstvo u oblaku',
        ])->assertSessionHas('success');

        $professor->refresh();
        $this->assertSame('Petar Milić', $professor->name);
        $this->assertSame('Vanredni profesor', $professor->professorProfile->academic_title);
        $this->assertSame('Katedra za računarstvo', $professor->professorProfile->department);
        $this->assertSame('Računarstvo u oblaku', $professor->professorProfile->research_area);
    }

    public function test_super_administrator_moze_da_promeni_svoje_ime(): void
    {
        $admin = User::factory()->create([
            'name' => 'Staro Ime',
            'role' => UserRole::SuperAdmin,
        ]);

        $this->actingAs($admin)->put(route('profile.update'), [
            'name' => 'Novo Ime',
        ])->assertSessionHas('success');

        $this->assertSame('Novo Ime', $admin->fresh()->name);
    }
}
