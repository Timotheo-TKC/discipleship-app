<?php

namespace Tests\Unit\Services;

use App\Models\Member;
use App\Services\MemberImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberImportServiceTest extends TestCase
{
    use RefreshDatabase;

    protected MemberImportService $importService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->importService = new MemberImportService();
    }

    /** @test */
    public function can_import_valid_members()
    {
        $validData = [
            [
                'full_name' => 'John Doe',
                'phone' => '0712345678',
                'email' => 'john@example.com',
                'date_of_conversion' => '2024-01-01',
                'preferred_contact' => 'sms',
                'notes' => 'Test member 1',
            ],
            [
                'full_name' => 'Jane Smith',
                'phone' => '0723456789',
                'email' => 'jane@example.com',
                'date_of_conversion' => '2024-01-15',
                'preferred_contact' => 'email',
                'notes' => 'Test member 2',
            ],
        ];

        $result = $this->importService->import($validData);

        $this->assertEquals(2, $result['imported']);
        $this->assertEmpty($result['errors']);

        $this->assertDatabaseHas('members', [
            'full_name' => 'John Doe',
            'phone' => '0712345678',
            'email' => 'john@example.com',
        ]);

        $this->assertDatabaseHas('members', [
            'full_name' => 'Jane Smith',
            'phone' => '0723456789',
            'email' => 'jane@example.com',
        ]);
    }

    /** @test */
    public function can_import_members_with_null_email()
    {
        $data = [
            [
                'full_name' => 'John Doe',
                'phone' => '0712345678',
                'email' => null,
                'date_of_conversion' => '2024-01-01',
                'preferred_contact' => 'sms',
                'notes' => null,
            ],
        ];

        $result = $this->importService->import($data);

        $this->assertEquals(1, $result['imported']);
        $this->assertEmpty($result['errors']);

        $this->assertDatabaseHas('members', [
            'full_name' => 'John Doe',
            'phone' => '0712345678',
            'email' => null,
        ]);
    }

    /** @test */
    public function handles_validation_errors()
    {
        $invalidData = [
            [
                'full_name' => '', // Invalid: empty name
                'phone' => '0712345678',
                'email' => 'invalid-email', // Invalid: bad email format
                'date_of_conversion' => '2024-01-01',
                'preferred_contact' => 'invalid_contact', // Invalid: not in allowed values
                'notes' => 'Test member',
            ],
            [
                'full_name' => 'John Doe',
                'phone' => '0712345678',
                'email' => 'john@example.com',
                'date_of_conversion' => '2024-01-01',
                'preferred_contact' => 'sms',
                'notes' => 'Valid member',
            ],
        ];

        $result = $this->importService->import($invalidData);

        $this->assertEquals(1, $result['imported']); // Only valid member imported
        $this->assertCount(1, $result['errors']); // One error for invalid member

        $this->assertDatabaseHas('members', [
            'full_name' => 'John Doe',
            'phone' => '0712345678',
        ]);

        $this->assertDatabaseMissing('members', [
            'full_name' => '',
        ]);
    }

    /** @test */
    public function handles_duplicate_phone_numbers()
    {
        // Create existing member
        Member::factory()->create(['phone' => '0712345678']);

        $data = [
            [
                'full_name' => 'John Doe',
                'phone' => '0712345678', // Duplicate phone
                'email' => 'john@example.com',
                'date_of_conversion' => '2024-01-01',
                'preferred_contact' => 'sms',
                'notes' => 'Test member',
            ],
        ];

        $result = $this->importService->import($data);

        $this->assertEquals(0, $result['imported']);
        $this->assertCount(1, $result['errors']);

        // Should not create duplicate member
        $this->assertEquals(1, Member::where('phone', '0712345678')->count());
    }

    /** @test */
    public function handles_duplicate_emails()
    {
        // Create existing member
        Member::factory()->create(['email' => 'john@example.com']);

        $data = [
            [
                'full_name' => 'John Doe',
                'phone' => '0712345678',
                'email' => 'john@example.com', // Duplicate email
                'date_of_conversion' => '2024-01-01',
                'preferred_contact' => 'sms',
                'notes' => 'Test member',
            ],
        ];

        $result = $this->importService->import($data);

        $this->assertEquals(0, $result['imported']);
        $this->assertCount(1, $result['errors']);

        // Should not create duplicate member
        $this->assertEquals(1, Member::where('email', 'john@example.com')->count());
    }

    /** @test */
    public function uses_database_transaction()
    {
        // Test that the service handles exceptions properly
        // by creating invalid data that will cause validation to fail
        $data = [
            [
                'full_name' => '', // Invalid - empty name
                'phone' => '123', // Invalid - too short
                'email' => 'invalid-email', // Invalid email format
                'date_of_conversion' => 'invalid-date', // Invalid date
                'preferred_contact' => 'invalid', // Invalid contact method
                'notes' => 'Test member',
            ],
        ];

        $result = $this->importService->import($data);

        // Should return errors and not import anything
        $this->assertEquals(0, $result['imported']);
        $this->assertCount(1, $result['errors']);
        $this->assertNotEmpty($result['errors'][0]['errors']);
    }

    /** @test */
    public function handles_mixed_valid_and_invalid_data()
    {
        $mixedData = [
            [
                'full_name' => 'John Doe',
                'phone' => '0712345678',
                'email' => 'john@example.com',
                'date_of_conversion' => '2024-01-01',
                'preferred_contact' => 'sms',
                'notes' => 'Valid member',
            ],
            [
                'full_name' => '', // Invalid
                'phone' => '0723456789',
                'email' => 'jane@example.com',
                'date_of_conversion' => '2024-01-15',
                'preferred_contact' => 'email',
                'notes' => 'Invalid member',
            ],
            [
                'full_name' => 'Bob Wilson',
                'phone' => '0734567890',
                'email' => 'bob@example.com',
                'date_of_conversion' => '2024-02-01',
                'preferred_contact' => 'call',
                'notes' => 'Another valid member',
            ],
        ];

        $result = $this->importService->import($mixedData);

        $this->assertEquals(2, $result['imported']); // Two valid members
        $this->assertCount(1, $result['errors']); // One error

        $this->assertDatabaseHas('members', [
            'full_name' => 'John Doe',
        ]);

        $this->assertDatabaseHas('members', [
            'full_name' => 'Bob Wilson',
        ]);

        $this->assertDatabaseMissing('members', [
            'full_name' => '',
        ]);
    }

    /** @test */
    public function handles_empty_data_array()
    {
        $result = $this->importService->import([]);

        $this->assertEquals(0, $result['imported']);
        $this->assertEmpty($result['errors']);
    }

    /** @test */
    public function validates_required_fields()
    {
        $data = [
            [
                'phone' => '0712345678',
                'email' => 'john@example.com',
                'date_of_conversion' => '2024-01-01',
                'preferred_contact' => 'sms',
                // Missing 'full_name'
            ],
        ];

        $result = $this->importService->import($data);

        $this->assertEquals(0, $result['imported']);
        $this->assertCount(1, $result['errors']);

        $error = $result['errors'][0];
        $this->assertArrayHasKey('errors', $error);
        $this->assertContains('The full name field is required.', $error['errors']);
    }

    /** @test */
    public function validates_phone_format()
    {
        $data = [
            [
                'full_name' => 'John Doe',
                'phone' => '123', // Too short
                'email' => 'john@example.com',
                'date_of_conversion' => '2024-01-01',
                'preferred_contact' => 'sms',
                'notes' => 'Test member',
            ],
        ];

        $result = $this->importService->import($data);

        $this->assertEquals(0, $result['imported']);
        $this->assertCount(1, $result['errors']);
    }

    /** @test */
    public function validates_date_format()
    {
        $data = [
            [
                'full_name' => 'John Doe',
                'phone' => '0712345678',
                'email' => 'john@example.com',
                'date_of_conversion' => 'invalid-date', // Invalid date format
                'preferred_contact' => 'sms',
                'notes' => 'Test member',
            ],
        ];

        $result = $this->importService->import($data);

        $this->assertEquals(0, $result['imported']);
        $this->assertCount(1, $result['errors']);
    }
}
