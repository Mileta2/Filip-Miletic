<?php

namespace Tests\Feature;

use App\Enums\StudyLevel;
use App\Enums\TopicType;
use App\Enums\UserRole;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_moze_da_kreira_studenta(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($admin)->post(route('admin.students.store'), [
            'first_name' => 'Stojan',
            'last_name' => 'Stojanović',
            'index_number_prefix' => '108',
            'index_number_suffix' => '22',
            'study_level' => 'diplomski',
            'study_year' => 4,
            'password' => 'Lozinka123',
            'password_confirmation' => 'Lozinka123',
            'is_active' => '1',
        ]);

        $student = User::where('email', 'stojan.stojanovic.108-22@ftnkm.rs')->firstOrFail();
        $response->assertRedirect(route('admin.students.show', $student));
        $this->assertSame(UserRole::Student, $student->role);
        $this->assertTrue($student->must_change_password);
        $this->assertDatabaseHas('student_profiles', ['index_number' => '108/22', 'study_year' => 4]);
    }

    public function test_administrator_moze_da_izmeni_predlozeni_studentski_email(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)->post(route('admin.students.store'), [
            'first_name' => 'Stojan',
            'last_name' => 'Stojanović',
            'email' => 's.stojanovic.108-22@ftnkm.rs',
            'index_number_prefix' => '108',
            'index_number_suffix' => '22',
            'study_level' => 'diplomski',
            'study_year' => 4,
            'password' => 'Lozinka123',
            'password_confirmation' => 'Lozinka123',
            'is_active' => '1',
        ])->assertSessionDoesntHaveErrors();

        $this->assertDatabaseHas('users', ['email' => 's.stojanovic.108-22@ftnkm.rs']);
    }

    public function test_broj_indeksa_prihvata_do_osam_cifara_sa_obe_strane(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)->post(route('admin.students.store'), [
            'first_name' => 'Veliki',
            'last_name' => 'Indeks',
            'index_number_prefix' => '32009001',
            'index_number_suffix' => '20251234',
            'study_level' => 'diplomski',
            'study_year' => 4,
            'password' => 'Lozinka123',
            'password_confirmation' => 'Lozinka123',
            'is_active' => '1',
        ])->assertSessionDoesntHaveErrors();

        $this->assertDatabaseHas('student_profiles', ['index_number' => '32009001/20251234']);
    }

    public function test_email_van_domena_fakulteta_se_ne_prihvata(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)->post(route('admin.students.store'), [
            'first_name' => 'Petar',
            'last_name' => 'Petrović',
            'email' => 'petar@gmail.com',
            'index_number_prefix' => '109',
            'index_number_suffix' => '22',
            'study_level' => 'diplomski',
            'study_year' => 4,
            'password' => 'Lozinka123',
            'password_confirmation' => 'Lozinka123',
            'is_active' => '1',
        ])->assertSessionHasErrors('email');
    }

    public function test_administrator_moze_da_promeni_godinu_studija(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $student = User::factory()->student()->create();

        $this->actingAs($admin)->put(route('admin.students.update', $student), [
            'name' => $student->name,
            'email' => $student->email,
            'index_number_prefix' => explode('/', $student->studentProfile->index_number)[0],
            'index_number_suffix' => explode('/', $student->studentProfile->index_number)[1],
            'study_level' => 'diplomski',
            'study_year' => 3,
            'is_active' => '1',
        ])->assertRedirect(route('admin.students.show', $student));

        $this->assertSame(3, $student->studentProfile->fresh()->study_year);
    }

    public function test_nivo_studija_ne_moze_da_se_promeni_suprotno_odabranoj_temi(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $student = User::factory()->student(StudyLevel::Undergraduate)->create();
        Topic::factory()->create([
            'student_id' => $student->id,
            'type' => TopicType::Undergraduate,
        ]);

        $this->actingAs($admin)->put(route('admin.students.update', $student), [
            'name' => $student->name,
            'email' => $student->email,
            'index_number_prefix' => explode('/', $student->studentProfile->index_number)[0],
            'index_number_suffix' => explode('/', $student->studentProfile->index_number)[1],
            'study_level' => StudyLevel::Master->value,
            'study_year' => 1,
            'is_active' => '1',
        ])->assertSessionHasErrors('study_level');

        $this->assertSame(StudyLevel::Undergraduate, $student->studentProfile->fresh()->study_level);
    }

    public function test_obrazac_za_studenta_ima_podeljen_indeks_i_odvojen_prikaz_emaila(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)->get(route('admin.students.create'))
            ->assertOk()
            ->assertSee('name="index_number_prefix"', false)
            ->assertSee('name="index_number_suffix"', false)
            ->assertSee('class="input-group"', false)
            ->assertSee('id="email_preview"', false)
            ->assertSee('id="email" name="email" type="hidden"', false)
            ->assertSee('data-student-form', false)
            ->assertSee('data-email-edit', false)
            ->assertSee('Najmanje 8 znakova, uz veliko slovo, malo slovo i broj.');
    }

    public function test_slozenost_lozinke_ima_srpsku_poruku(): void
    {
        $this->assertSame(
            'Polje lozinka mora sadržati najmanje jedno veliko i jedno malo slovo.',
            trans('validation.password.mixed', ['attribute' => 'lozinka'])
        );
    }

    public function test_student_se_brise_tek_posle_provere_administratorske_lozinke(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $student = User::factory()->student()->create();
        $topic = Topic::factory()->create(['student_id' => $student->id]);

        $this->actingAs($admin)->from(route('admin.students.show', $student))->delete(route('admin.students.destroy', $student), [
            'admin_password' => 'pogresna-lozinka',
        ])->assertSessionHasErrors('admin_password');
        $this->assertNotSoftDeleted($student);

        $this->actingAs($admin)->delete(route('admin.students.destroy', $student), [
            'admin_password' => 'password',
        ])->assertRedirect(route('admin.students.index'));

        $this->assertSoftDeleted($student);
        $this->assertSame($student->id, $topic->fresh()->student_id);
        $this->assertSame($student->id, $topic->fresh()->student->id);
    }

    public function test_profesor_mora_da_ima_email_na_domenu_fakulteta(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)->post(route('admin.professors.store'), [
            'first_name' => 'Novi',
            'last_name' => 'Profesor',
            'email' => 'novi.profesor@gmail.com',
            'academic_title' => 'Docent',
            'department' => 'Katedra za računarstvo i informatiku',
            'research_area' => 'Softversko inženjerstvo',
            'password' => 'Lozinka123',
            'password_confirmation' => 'Lozinka123',
            'is_active' => '1',
        ])->assertSessionHasErrors('email');
    }

    public function test_administrator_moze_da_deaktivira_i_resetuje_lozinku_profesoru(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $professor = User::factory()->professor()->create();

        $this->actingAs($admin)->patch(route('admin.professors.toggle', $professor))->assertSessionHas('success');
        $this->assertFalse($professor->fresh()->is_active);

        $this->actingAs($admin)->put(route('admin.professors.password', $professor), [
            'password' => 'Privremena123',
            'password_confirmation' => 'Privremena123',
        ])->assertSessionHas('success');
        $this->assertTrue($professor->fresh()->must_change_password);
    }
}
