<?php

namespace Tests\Feature;

use App\Enums\StudyLevel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_aktivan_korisnik_moze_da_se_prijavi(): void
    {
        $user = User::factory()->superAdmin()->create();

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_email_prilikom_prijave_nije_osetljiv_na_velika_slova_i_razmake(): void
    {
        $user = User::factory()->superAdmin()->create(['email' => 'admin@ftnkm.rs']);

        $this->post(route('login.store'), [
            'email' => '  ADMIN@FTNKM.RS  ',
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_deaktiviran_korisnik_ne_moze_da_se_prijavi(): void
    {
        $user = User::factory()->superAdmin()->create(['is_active' => false]);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_inicijalna_lozinka_mora_da_se_promeni(): void
    {
        $student = User::factory()->student(StudyLevel::Undergraduate)->create([
            'must_change_password' => true,
        ]);

        $this->actingAs($student)
            ->get(route('student.dashboard'))
            ->assertRedirect(route('password.change'));

        $this->put(route('password.update'), [
            'password' => 'NovaLozinka123',
            'password_confirmation' => 'NovaLozinka123',
        ])->assertRedirect(route('profile.edit'));

        $student->refresh();
        $this->assertFalse($student->must_change_password);
        $this->assertTrue(Hash::check('NovaLozinka123', $student->password));
    }

    public function test_korisnik_moze_da_se_odjavi(): void
    {
        $user = User::factory()->superAdmin()->create();

        $this->actingAs($user)->post(route('logout'))->assertRedirect(route('home'));

        $this->assertGuest();
    }
}
