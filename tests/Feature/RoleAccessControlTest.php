<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login_from_every_role_area(): void
    {
        $this->get(route('patient.dashboard'))->assertRedirect(route('login'));
        $this->get(route('physician.dashboard'))->assertRedirect(route('login'));
        $this->get(route('admin.queue.index'))->assertRedirect(route('login'));
        $this->get(route('admin.users.index'))->assertRedirect(route('login'));
        $this->post(route('logout'))->assertRedirect(route('login'));
    }

    public function test_each_role_is_sent_to_its_own_home_after_login(): void
    {
        $patient = $this->createUserWithRole('patient');
        $this->makePatientProfile($patient);
        $this->post(route('login'), ['email' => $patient->email, 'password' => 'password'])
            ->assertRedirect(route('patient.dashboard'));
        $this->post(route('logout'));

        $physician = $this->createUserWithRole('physician');
        $this->makePhysicianProfile($physician);
        $this->post(route('login'), ['email' => $physician->email, 'password' => 'password'])
            ->assertRedirect(route('physician.dashboard'));
        $this->post(route('logout'));

        $administrator = $this->createUserWithRole('administrator');
        $this->post(route('login'), ['email' => $administrator->email, 'password' => 'password'])
            ->assertRedirect(route('admin.queue.index'));
    }

    public function test_the_root_url_sends_each_role_to_its_dashboard(): void
    {
        $this->get('/')->assertRedirect(route('login'));

        $patient = $this->createUserWithRole('patient');
        $this->makePatientProfile($patient);

        $this->actingAs($patient)->get('/')->assertRedirect(route('patient.dashboard'));
    }

    public function test_administration_routes_are_isolated_from_clinical_roles(): void
    {
        $administrator = $this->createUserWithRole('administrator');
        $receptionist = $this->createUserWithRole('receptionist');
        $physician = $this->createUserWithRole('physician');
        $this->makePhysicianProfile($physician);
        $patient = $this->createUserWithRole('patient');
        $this->makePatientProfile($patient);

        // Receptionists run the queue but never administer accounts or audit trails.
        $this->actingAs($receptionist)->get(route('admin.queue.index'))->assertOk();
        $this->actingAs($receptionist)->get(route('admin.schedules.index'))->assertOk();
        $this->actingAs($receptionist)->get(route('admin.reports.index'))->assertOk();
        $this->actingAs($receptionist)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($receptionist)->get(route('admin.audit-logs.index'))->assertForbidden();

        // Administrators reach every administrative screen.
        $this->actingAs($administrator)->get(route('admin.queue.index'))->assertOk();
        $this->actingAs($administrator)->get(route('admin.users.index'))->assertOk();
        $this->actingAs($administrator)->get(route('admin.audit-logs.index'))->assertOk();

        // Clinical and patient accounts stay out of the administration area.
        $this->actingAs($physician)->get(route('admin.queue.index'))->assertForbidden();
        $this->actingAs($patient)->get(route('admin.queue.index'))->assertForbidden();
        $this->actingAs($patient)->get(route('admin.users.index'))->assertForbidden();
    }

    public function test_clinical_and_administrative_areas_are_isolated_from_each_other(): void
    {
        $physician = $this->createUserWithRole('physician');
        $this->makePhysicianProfile($physician);
        $patient = $this->createUserWithRole('patient');
        $this->makePatientProfile($patient);

        $this->actingAs($physician)->get(route('patient.dashboard'))->assertForbidden();
        $this->actingAs($physician)->get(route('patient.appointments.index'))->assertForbidden();
        $this->actingAs($physician)->get(route('physician.dashboard'))->assertOk();

        $this->actingAs($patient)->get(route('physician.dashboard'))->assertForbidden();
        $this->actingAs($patient)->get(route('physician.encounters.index'))->assertForbidden();
        $this->actingAs($patient)->get(route('patient.dashboard'))->assertOk();
    }

    public function test_a_suspended_account_is_logged_out_on_its_next_request(): void
    {
        $patient = $this->createUserWithRole('patient');
        $this->makePatientProfile($patient);

        $this->post(route('login'), ['email' => $patient->email, 'password' => 'password'])
            ->assertRedirect(route('patient.dashboard'));

        $this->actingAs($patient)->get(route('patient.dashboard'))->assertOk();

        $patient->update(['is_active' => false]);

        $this->get(route('patient.dashboard'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');
    }

    public function test_a_suspended_account_cannot_log_in_at_all(): void
    {
        $patient = $this->createUserWithRole('patient');
        $this->makePatientProfile($patient);

        $patient->update(['is_active' => false]);

        // Credentials are correct, but the account is suspended, so the
        // credential gate itself must refuse the attempt.
        $this->from(route('login'))
            ->post(route('login'), ['email' => $patient->email, 'password' => 'password'])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();

        // An active account with the same password still gets in.
        $patient->update(['is_active' => true]);

        $this->post(route('login'), ['email' => $patient->email, 'password' => 'password'])
            ->assertRedirect(route('patient.dashboard'));

        $this->assertAuthenticated();
    }

    public function test_registration_provisions_a_patient_profile_and_signs_the_user_in(): void
    {
        $this->seed(RoleSeeder::class);

        $response = $this->post(route('register'), [
            'name' => 'Sample Patient',
            'email' => 'sample.patient@clinicsync.test',
            'phone' => '09171234567',
            'date_of_birth' => '1995-03-08',
            'sex' => 'female',
            'civil_status' => 'single',
            'address' => 'Mambajao, Camiguin',
            'blood_type' => 'O+',
            'emergency_contact_name' => 'Next of Kin',
            'emergency_contact_phone' => '09180000000',
            'emergency_contact_relationship' => 'Parent',
            'password' => 'supersecret',
            'password_confirmation' => 'supersecret',
        ]);

        $response->assertRedirect(route('patient.dashboard'));

        $user = User::where('email', 'sample.patient@clinicsync.test')->firstOrFail();

        $this->assertTrue($user->hasRole('patient'));

        $profile = Patient::where('user_id', $user->id)->firstOrFail();
        $this->assertMatchesRegularExpression('/^PT-\d{6}$/', $profile->patient_number);

        $this->assertAuthenticatedAs($user);

        $this->assertTrue($user->password !== 'supersecret');
    }
}
