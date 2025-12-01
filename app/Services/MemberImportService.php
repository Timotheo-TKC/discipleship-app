<?php

namespace App\Services;

use App\Models\Member;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class MemberImportService
{
    /**
     * Import members from array data (for testing)
     */
    public function import(array $data): array
    {
        $importedCount = 0;
        $errors = [];

        DB::beginTransaction();

        try {
            foreach ($data as $row) {
                $validator = Validator::make($row, [
                    'full_name' => ['required', 'string', 'max:255'],
                    'phone' => ['required', 'string', 'min:10', 'max:20', 'unique:members,phone'],
                    'email' => ['required', 'string', 'email', 'max:255', 'unique:members,email', 'unique:users,email'],
                    'date_of_conversion' => ['required', 'date_format:Y-m-d'],
                    'preferred_contact' => ['required', 'string', 'in:email,call'],
                    'notes' => ['nullable', 'string'],
                ]);

                if ($validator->fails()) {
                    $errors[] = [
                        'row' => $row,
                        'errors' => $validator->errors()->all(),
                    ];

                    continue;
                }

                $defaultPassword = env('DEFAULT_SHARED_PASSWORD', 'password');
                
                // Create User account for the member with default password
                $user = User::create([
                    'name' => $row['full_name'],
                    'email' => $row['email'],
                    'phone' => $row['phone'] ?? null,
                    'role' => User::ROLE_MEMBER,
                    'password' => Hash::make($defaultPassword),
                    'email_verified_at' => now(),
                ]);

                // Create Member profile linked to the User account
                Member::create([
                    'user_id' => $user->id,
                    'full_name' => $row['full_name'],
                    'phone' => $row['phone'],
                    'email' => $row['email'],
                    'date_of_conversion' => $row['date_of_conversion'],
                    'preferred_contact' => $row['preferred_contact'],
                    'notes' => $row['notes'] ?? null,
                ]);

                $importedCount++;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $errors[] = ['general' => $e->getMessage()];
        }

        return [
            'imported' => $importedCount,
            'errors' => $errors,
        ];
    }

    /**
     * Import members from CSV file
     */
    public function importFromCsv(UploadedFile $file, User $importedBy): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => [],
            'skipped' => 0,
        ];

        $csvData = $this->parseCsvFile($file);

        if (empty($csvData)) {
            throw new \Exception('CSV file is empty or invalid.');
        }

        DB::transaction(function () use ($csvData, $importedBy, &$results) {
            foreach ($csvData as $rowIndex => $row) {
                try {
                    $this->validateCsvRow($row, $rowIndex + 2); // +2 for header and 0-based index

                    $member = $this->createMemberFromRow($row);
                    $results['success']++;

                } catch (ValidationException $e) {
                    $results['failed']++;
                    $results['errors'][] = [
                        'row' => $rowIndex + 2,
                        'data' => $row,
                        'errors' => $e->errors(),
                    ];
                } catch (\Exception $e) {
                    $results['failed']++;
                    $results['errors'][] = [
                        'row' => $rowIndex + 2,
                        'data' => $row,
                        'errors' => ['general' => [$e->getMessage()]],
                    ];
                }
            }
        });

        return $results;
    }

    /**
     * Parse CSV file and return array of rows
     */
    private function parseCsvFile(UploadedFile $file): array
    {
        $csvData = [];
        $handle = fopen($file->getPathname(), 'r');

        if (! $handle) {
            throw new \Exception('Could not read CSV file.');
        }

        // Read header row
        $headers = fgetcsv($handle);
        if (! $headers) {
            fclose($handle);

            throw new \Exception('CSV file appears to be empty.');
        }

        // Validate headers
        $expectedHeaders = ['full_name', 'phone', 'email', 'date_of_conversion', 'preferred_contact', 'notes'];
        $headerDiff = array_diff($expectedHeaders, $headers);
        if (! empty($headerDiff)) {
            fclose($handle);

            throw new \Exception('CSV headers are invalid. Expected: ' . implode(', ', $expectedHeaders));
        }

        // Read data rows
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) === count($headers)) {
                $csvData[] = array_combine($headers, $row);
            }
        }

        fclose($handle);

        return $csvData;
    }

    /**
     * Validate a single CSV row
     */
    private function validateCsvRow(array $row, int $rowNumber): void
    {
        $validator = Validator::make($row, [
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => [
                'required',
                'string',
                'regex:/^(\+254|0)[0-9]{9}$/',
                'unique:members,phone',
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:members,email',
                'unique:users,email',
            ],
            'date_of_conversion' => ['required', 'date', 'before_or_equal:today'],
            'preferred_contact' => ['required', 'in:email,call'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'phone.regex' => 'The phone number must be a valid Kenyan phone number.',
            'date_of_conversion.before_or_equal' => 'The conversion date cannot be in the future.',
            'preferred_contact.in' => 'Preferred contact must be email or call.',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Create a member from CSV row data
     */
    private function createMemberFromRow(array $row): Member
    {
        $email = trim($row['email']);
        $phone = $this->normalizePhoneNumber($row['phone']);
        $defaultPassword = env('DEFAULT_SHARED_PASSWORD', 'password');
        
        // Create User account for the member with default password
        $user = User::create([
            'name' => trim($row['full_name']),
            'email' => $email,
            'phone' => $phone,
            'role' => User::ROLE_MEMBER,
            'password' => Hash::make($defaultPassword),
            'email_verified_at' => now(), // Auto-verify email for imported members
        ]);

        // Create Member profile linked to the User account
        return Member::create([
            'user_id' => $user->id,
            'full_name' => trim($row['full_name']),
            'phone' => $phone,
            'email' => $email,
            'date_of_conversion' => $row['date_of_conversion'],
            'preferred_contact' => $row['preferred_contact'],
            'notes' => ! empty($row['notes']) ? trim($row['notes']) : null,
        ]);
    }

    /**
     * Normalize phone number to standard format
     */
    private function normalizePhoneNumber(string $phone): string
    {
        // Remove any spaces or special characters
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        // Convert to +254 format
        if (str_starts_with($phone, '0')) {
            $phone = '+254' . substr($phone, 1);
        } elseif (str_starts_with($phone, '254')) {
            $phone = '+' . $phone;
        }

        return $phone;
    }

    /**
     * Get CSV template for download
     */
    public function getCsvTemplate(): string
    {
        $headers = ['full_name', 'phone', 'email', 'date_of_conversion', 'preferred_contact', 'notes'];
        $sample = [
            'John Doe',
            '+254712345678',
            'john@example.com',
            '2024-01-15',
            'sms',
            'New member from outreach program',
        ];

        $csv = implode(',', $headers) . "\n";
        $csv .= implode(',', $sample) . "\n";

        return $csv;
    }
}
