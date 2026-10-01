<?php

namespace Tests\Feature;

use App\Enums\UserRole;
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
            'index_number' => '108/22',
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
            'index_number' => '108/22',
            'study_level' => 'diplomski',
            'study_year' => 4,
            'password' => 'Lozinka123',
            'password_confirmation' => 'Lozinka123',
            'is_active' => '1',
        ])->assertSessionDoesntHaveErrors();

        $this->assertDatabaseHas('users', ['email' => 's.stojanovic.108-22@ftnkm.rs']);
    }

    public function test_email_van_domena_fakulteta_se_ne_prihvata(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)->post(route('admin.students.store'), [
            'first_name' => 'Petar',
            'last_name' => 'Petrović',
            'email' => 'petar@gmail.com',
            'index_number' => '109/22',
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
            'index_number' => $student->studentProfile->index_number,
            'study_level' => 'diplomski',
            'study_year' => 3,
            'is_active' => '1',
        ])->assertRedirect(route('admin.students.show', $student));

        $this->assertSame(3, $student->studentProfile->fresh()->study_year);
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
