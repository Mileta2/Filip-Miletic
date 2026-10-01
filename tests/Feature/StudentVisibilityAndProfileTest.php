<?php

namespace Tests\Feature;

use App\Enums\StudyLevel;
use App\Enums\TopicType;
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
            ->assertDontSee('Pregled tema za završne radove studenata master studija.');

        $this->actingAs($student)->get(route('topics.master'))
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
}
