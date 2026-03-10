<?php
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;

it('allows admin to import users', function () {
    Storage::fake('local');

    // Use a valid CSV matching UserImportController expected headers
    $csv = "nombre,apellidos,email,n_tel,password,tipo\n";
    $csv .= "Juan,Perez,juan.perez@example.local,600123456,password123,alumno\n";

    $file = UploadedFile::fake()->createWithContent('import.csv', $csv);

    $admin = User::factory()->create(['tipo' => 'admin']);
    $response = $this->actingAs($admin, 'sanctum')
        ->postJson('/api/users/import', ['file' => $file]);

    // admin should be allowed -> controller returns 200 on success
    $response->assertStatus(200);
});

it('forbids non-admin to import users', function () {
    Storage::fake('local');
    $file = UploadedFile::fake()->createWithContent('import.csv', "alias_profesor;nombre\nABC;Juan");

    $tutor = User::factory()->create(['tipo' => 'tutor']);
    $response = $this->actingAs($tutor, 'sanctum')
        ->postJson('/api/users/import', ['file' => $file]);

    $response->assertStatus(403);
});

it('allows tutors to assign-instructor but not create instructors', function () {
    $tutor = User::factory()->create(['tipo' => 'tutor']);
    $admin = User::factory()->create(['tipo' => 'admin']);

    // Test the Gate logic directly to avoid depending on route implementation or DB fixtures
    $this->assertTrue(Gate::forUser($tutor)->allows('assign-instructor'));
    $this->assertFalse(Gate::forUser($tutor)->allows('create-instructor'));
    // Sanity: admin can create instructor
    $this->assertTrue(Gate::forUser($admin)->allows('create-instructor'));
});