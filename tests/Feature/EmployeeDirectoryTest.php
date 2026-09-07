<?php

namespace Tests\Feature;

use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeDirectoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_employee_directory(): void
    {
        Employee::factory()->count(20)->create();

        $response = $this->get('/employees');

        $response->assertStatus(200);
        $response->assertSee('Direktori Karyawan');
        $response->assertSee('TechCorp Indonesia');
    }

    public function test_pagination_limits_to_15_items(): void
    {
        Employee::factory()->count(30)->create();

        $response = $this->get('/employees');

        $response->assertStatus(200);
        $employees = $response->viewData('employees');
        $this->assertEquals(15, $employees->count());
        $this->assertEquals(30, $employees->total());
    }

    public function test_can_search_by_name_or_email(): void
    {
        Employee::factory()->create([
            'name' => 'Budi Santoso',
            'email' => 'budi@techcorp.id',
            'department' => 'IT',
        ]);
        Employee::factory()->create([
            'name' => 'Siti Rahma',
            'email' => 'siti@techcorp.id',
            'department' => 'HR',
        ]);

        $response = $this->get('/employees?search=Budi');
        $response->assertSee('Budi Santoso');
        $response->assertDontSee('Siti Rahma');
    }

    public function test_can_filter_by_department(): void
    {
        Employee::factory()->create([
            'name' => 'Karyawan IT',
            'department' => 'IT',
        ]);
        Employee::factory()->create([
            'name' => 'Karyawan Finance',
            'department' => 'Finance',
        ]);

        $response = $this->get('/employees?department=IT');
        $response->assertSee('Karyawan IT');
        $response->assertDontSee('Karyawan Finance');
    }

    public function test_empty_state_message_is_displayed_when_no_data_matches(): void
    {
        $response = $this->get('/employees?search=NonExistentKeyword');
        $response->assertSee('Data tidak ditemukan. Coba ubah kata kunci atau filter.');
    }
}
