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
            'first_name' => 'Petar',
            'last_name' => 'Petrović',
            'email' => 'petar@example.test',
            'index_number' => 'IT 2026/001',
            'study_level' => 'diplomski',
            'password' => 'Lozinka123',
            'password_confirmation' => 'Lozinka123',
            'is_active' => '1',
        ]);

        $student = User::where('email', 'petar@example.test')->firstOrFail();
        $response->assertRedirect(route('admin.students.show', $student));
        $this->assertSame(UserRole::Student, $student->role);
        $this->assertTrue($student->must_change_password);
        $this->assertDatabaseHas('student_profiles', ['index_number' => 'IT 2026/001']);
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
