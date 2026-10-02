<?php

namespace Tests\Feature;

use App\Enums\StudyLevel;
use App\Enums\TopicStatus;
use App\Enums\TopicType;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class TopicWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_osnovne_studije_imaju_odgovarajucu_oznaku(): void
    {
        $this->assertSame('Osnovne studije', StudyLevel::Undergraduate->label());
        $this->assertSame('Diplomski rad', TopicType::Undergraduate->label());
    }

    public function test_profesor_moze_da_kreira_i_izmeni_svoju_temu(): void
    {
        $professor = User::factory()->professor()->create();

        $this->actingAs($professor)->post(route('topics.store'), [
            'title' => 'Nova tema za diplomski rad',
            'course' => 'Programiranje internet aplikacija',
            'description' => 'Dovoljno detaljan opis nove teme za potrebe automatskog testa.',
            'type' => TopicType::Undergraduate->value,
        ])->assertRedirect();

        $topic = Topic::firstOrFail();
        $this->assertSame($professor->id, $topic->mentor_id);
        $this->assertSame(TopicStatus::Available, $topic->status);

        $this->actingAs($professor)->put(route('topics.update', $topic), [
            'title' => 'Izmenjen naslov teme',
            'course' => 'OOP 2',
            'description' => 'Dovoljno detaljan izmenjeni opis teme za potrebe automatskog testa.',
            'type' => TopicType::Undergraduate->value,
        ])->assertRedirect(route('topics.show', $topic));
        $this->assertDatabaseHas('topics', ['id' => $topic->id, 'title' => 'Izmenjen naslov teme']);
    }

    public function test_profesor_ne_moze_da_menja_tudju_temu(): void
    {
        $owner = User::factory()->professor()->create();
        $other = User::factory()->professor()->create();
        $topic = Topic::factory()->create(['mentor_id' => $owner->id]);

        $this->actingAs($other)->put(route('topics.update', $topic), [
            'title' => 'Nedozvoljena izmena',
            'course' => 'Operativni sistemi 1',
            'description' => 'Ovaj sadržaj ne treba da bude sačuvan u bazi podataka.',
            'type' => TopicType::Undergraduate->value,
        ])->assertForbidden();
    }

    public function test_profesor_moze_da_obrise_svoju_zauzetu_temu(): void
    {
        $professor = User::factory()->professor()->create();
        $student = User::factory()->student()->create();
        $topic = Topic::factory()->create([
            'mentor_id' => $professor->id,
            'student_id' => $student->id,
            'status' => TopicStatus::Reserved,
            'reserved_at' => now(),
        ]);

        $this->actingAs($professor)->delete(route('topics.destroy', $topic))
            ->assertRedirect(route('topics.index'));

        $this->assertDatabaseMissing('topics', ['id' => $topic->id]);
    }

    public function test_profesor_ne_moze_da_obrise_tudju_temu(): void
    {
        $owner = User::factory()->professor()->create();
        $other = User::factory()->professor()->create();
        $topic = Topic::factory()->create(['mentor_id' => $owner->id]);

        $this->actingAs($other)->delete(route('topics.destroy', $topic))->assertForbidden();
        $this->assertDatabaseHas('topics', ['id' => $topic->id]);
    }

    public function test_super_administrator_moze_da_obrise_bilo_koju_temu(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $topic = Topic::factory()->create(['status' => TopicStatus::Defended]);

        $this->actingAs($admin)->delete(route('topics.destroy', $topic))
            ->assertRedirect(route('topics.index'));

        $this->assertDatabaseMissing('topics', ['id' => $topic->id]);
    }

    public function test_obrisani_profesor_ne_moze_da_bude_mentor_nove_teme(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $deletedProfessor = User::factory()->professor()->create();
        $deletedProfessor->delete();

        $this->actingAs($admin)->post(route('topics.store'), [
            'title' => 'Tema sa obrisanim mentorom',
            'course' => 'Web dizajn',
            'description' => 'Opis teme koji je dovoljno dug za proveru validacije obrisanog mentora.',
            'type' => TopicType::Undergraduate->value,
            'mentor_id' => $deletedProfessor->id,
        ])->assertSessionHasErrors('mentor_id');

        $this->assertDatabaseCount('topics', 0);
    }

    public function test_student_moze_da_izabere_slobodnu_temu_odgovarajuceg_nivoa(): void
    {
        $student = User::factory()->student(StudyLevel::Undergraduate)->create();
        $topic = Topic::factory()->create(['type' => TopicType::Undergraduate]);

        $this->actingAs($student)->post(route('student.topics.select', $topic))
            ->assertRedirect(route('student.dashboard'));

        $topic->refresh();
        $this->assertSame(TopicStatus::Reserved, $topic->status);
        $this->assertSame($student->id, $topic->student_id);
        $this->assertNotNull($topic->reserved_at);
    }

    public function test_student_ne_moze_da_izabere_zauzetu_temu(): void
    {
        $first = User::factory()->student()->create();
        $second = User::factory()->student()->create();
        $topic = Topic::factory()->create([
            'student_id' => $first->id,
            'status' => TopicStatus::Reserved,
            'reserved_at' => now(),
        ]);

        $this->actingAs($second)->from(route('topics.show', $topic))
            ->post(route('student.topics.select', $topic))
            ->assertSessionHasErrors('topic');
        $this->assertSame($first->id, $topic->fresh()->student_id);
    }

    public function test_student_ne_moze_da_izabere_dve_teme(): void
    {
        $student = User::factory()->student()->create();
        Topic::factory()->create([
            'student_id' => $student->id,
            'status' => TopicStatus::Reserved,
            'reserved_at' => now(),
        ]);
        $secondTopic = Topic::factory()->create();

        $this->actingAs($student)->post(route('student.topics.select', $secondTopic))
            ->assertSessionHasErrors('topic');
        $this->assertSame(TopicStatus::Available, $secondTopic->fresh()->status);
    }

    public function test_student_ne_moze_da_izabere_temu_pogresnog_nivoa(): void
    {
        $student = User::factory()->student(StudyLevel::Master)->create();
        $topic = Topic::factory()->create(['type' => TopicType::Undergraduate]);

        $this->actingAs($student)->post(route('student.topics.select', $topic))
            ->assertSessionHasErrors('topic');
        $this->assertSame(TopicStatus::Available, $topic->fresh()->status);
    }

    public function test_student_osnovnih_studija_ne_moze_da_izabere_master_temu(): void
    {
        $student = User::factory()->student(StudyLevel::Undergraduate)->create();
        $topic = Topic::factory()->create(['type' => TopicType::Master]);

        $this->actingAs($student)->post(route('student.topics.select', $topic))
            ->assertSessionHasErrors('topic');
        $this->assertSame(TopicStatus::Available, $topic->fresh()->status);
    }

    public function test_mentor_moze_da_oslobodi_zauzetu_temu(): void
    {
        $mentor = User::factory()->professor()->create();
        $student = User::factory()->student()->create();
        $topic = Topic::factory()->create([
            'mentor_id' => $mentor->id,
            'student_id' => $student->id,
            'status' => TopicStatus::Reserved,
            'reserved_at' => now(),
        ]);

        $this->actingAs($mentor)->patch(route('topics.release', $topic))->assertSessionHas('success');
        $topic->refresh();
        $this->assertSame(TopicStatus::Available, $topic->status);
        $this->assertNull($topic->student_id);
    }

    public function test_zauzeta_tema_moze_da_postane_odbranjena_sa_komisijom(): void
    {
        $mentor = User::factory()->professor()->create();
        $president = User::factory()->professor()->create();
        $member = User::factory()->professor()->create();
        $student = User::factory()->student()->create();
        $topic = Topic::factory()->create([
            'mentor_id' => $mentor->id,
            'student_id' => $student->id,
            'status' => TopicStatus::Reserved,
            'reserved_at' => now()->subMonth(),
        ]);

        $this->actingAs($mentor)->put(route('topics.defense.update', $topic), [
            'defended_at' => now()->toDateString(),
            'president_id' => $president->id,
            'member_ids' => [$member->id],
        ])->assertRedirect(route('topics.show', $topic));

        $this->assertSame(TopicStatus::Defended, $topic->fresh()->status);
        $this->assertDatabaseCount('defense_committee_members', 2);
    }

    public function test_datum_odbrane_ne_moze_da_bude_pre_rezervacije(): void
    {
        $mentor = User::factory()->professor()->create();
        $president = User::factory()->professor()->create();
        $member = User::factory()->professor()->create();
        $student = User::factory()->student()->create();
        $topic = Topic::factory()->create([
            'mentor_id' => $mentor->id,
            'student_id' => $student->id,
            'status' => TopicStatus::Reserved,
            'reserved_at' => now()->subDays(3),
        ]);

        $this->actingAs($mentor)->put(route('topics.defense.update', $topic), [
            'defended_at' => now()->subDays(4)->toDateString(),
            'president_id' => $president->id,
            'member_ids' => [$member->id],
        ])->assertSessionHasErrors('defended_at');

        $this->assertSame(TopicStatus::Reserved, $topic->fresh()->status);
        $this->assertDatabaseCount('defense_committee_members', 0);
    }

    public function test_obrisani_profesor_ne_moze_da_bude_dodat_u_komisiju(): void
    {
        $mentor = User::factory()->professor()->create();
        $president = User::factory()->professor()->create();
        $deletedMember = User::factory()->professor()->create();
        $student = User::factory()->student()->create();
        $topic = Topic::factory()->create([
            'mentor_id' => $mentor->id,
            'student_id' => $student->id,
            'status' => TopicStatus::Reserved,
            'reserved_at' => now()->subWeek(),
        ]);
        $deletedMember->delete();

        $this->actingAs($mentor)->put(route('topics.defense.update', $topic), [
            'defended_at' => now()->toDateString(),
            'president_id' => $president->id,
            'member_ids' => [$deletedMember->id],
        ])->assertSessionHasErrors('member_ids.0');

        $this->assertSame(TopicStatus::Reserved, $topic->fresh()->status);
    }

    public function test_upload_prihvata_samo_pdf_dokument(): void
    {
        $professor = User::factory()->professor()->create();

        $this->actingAs($professor)->post(route('topics.store'), [
            'title' => 'Tema sa neispravnim dokumentom',
            'course' => 'Web dizajn',
            'description' => 'Opis teme koji je dovoljno dug za uspešnu validaciju ostalih polja.',
            'type' => TopicType::Undergraduate->value,
            'pdf' => UploadedFile::fake()->create('dokument.txt', 10, 'text/plain'),
        ])->assertSessionHasErrors('pdf');
    }

    public function test_teme_mogu_da_se_filtriraju_po_profesoru_predmetu_i_statusu(): void
    {
        $firstProfessor = User::factory()->professor()->create(['name' => 'Petar Milić']);
        $secondProfessor = User::factory()->professor()->create(['name' => 'Siniša Ilić']);
        $matchingTopic = Topic::factory()->create([
            'title' => 'Tražena tema iz oblaka',
            'course' => 'Računarstvo u oblaku',
            'mentor_id' => $firstProfessor->id,
            'status' => TopicStatus::Available,
        ]);
        Topic::factory()->create([
            'title' => 'Tema pogrešnog profesora',
            'course' => 'Računarstvo u oblaku',
            'mentor_id' => $secondProfessor->id,
            'status' => TopicStatus::Available,
        ]);
        Topic::factory()->create([
            'title' => 'Tema pogrešnog statusa',
            'course' => 'Računarstvo u oblaku',
            'mentor_id' => $firstProfessor->id,
            'status' => TopicStatus::Reserved,
        ]);

        $this->get(route('topics.index', [
            'mentor_id' => $firstProfessor->id,
            'course' => 'Računarstvo u oblaku',
            'status' => TopicStatus::Available->value,
        ]))
            ->assertOk()
            ->assertSee($matchingTopic->title)
            ->assertDontSee('Tema pogrešnog profesora')
            ->assertDontSee('Tema pogrešnog statusa')
            ->assertSee('Svi profesori')
            ->assertSee('Svi predmeti')
            ->assertSee('Svi statusi');
    }

    public function test_kartice_tema_imaju_ikone_i_blage_boje_prema_statusu(): void
    {
        Topic::factory()->create(['status' => TopicStatus::Available]);
        Topic::factory()->create(['status' => TopicStatus::Reserved]);
        Topic::factory()->create(['status' => TopicStatus::Defended]);

        $this->get(route('topics.index'))
            ->assertOk()
            ->assertSee('topic-card-available', false)
            ->assertSee('bi-unlock-fill', false)
            ->assertSee('topic-card-reserved', false)
            ->assertSee('bi-lock-fill', false)
            ->assertSee('topic-card-defended', false)
            ->assertSee('bi-check-circle-fill', false);
    }
}
