<?php

namespace Tests\Feature;

use App\Enums\StudyLevel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_administrator_moze_da_pristupi_upravljanju_korisnicima(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)->get(route('admin.students.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.professors.index'))->assertOk();
    }

    public function test_profesor_ne_moze_da_pristupi_administraciji(): void
    {
        $professor = User::factory()->professor()->create();

        $this->actingAs($professor)->get(route('admin.students.index'))->assertForbidden();
    }

    public function test_student_ne_moze_da_pristupi_administraciji(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($student)->get(route('admin.professors.index'))->assertForbidden();
    }

    public function test_svaka_uloga_dobija_odgovarajucu_kontrolnu_tablu(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $professor = User::factory()->professor()->create();
        $student = User::factory()->student(StudyLevel::Master)->create();

        $this->actingAs($admin)->get(route('dashboard'))->assertRedirect(route('admin.dashboard'));
        $this->actingAs($professor)->get(route('professor.dashboard'))->assertOk();
        $this->actingAs($student)->get(route('student.dashboard'))->assertOk();
    }
}
