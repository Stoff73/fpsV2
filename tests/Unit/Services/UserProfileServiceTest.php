<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\Household;
use App\Models\FamilyMember;
use App\Models\Property;
use App\Models\Investment\InvestmentAccount;
use App\Services\UserProfile\UserProfileService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new UserProfileService();

    // Create a household
    $this->household = Household::factory()->create();

    // Create a test user
    $this->user = User::factory()->create([
        'household_id' => $this->household->id,
        'name' => 'Test User',
        'email' => 'test@example.com',
        'date_of_birth' => '1985-05-15',
        'gender' => 'male',
        'marital_status' => 'single',
        'occupation' => 'Software Engineer',
        'annual_employment_income' => 75000.00,
        'annual_rental_income' => 12000.00,
        'annual_dividend_income' => 3000.00,
    ]);

    // Create family members
    $this->familyMember = FamilyMember::factory()->create([
        'user_id' => $this->user->id,
        'relationship' => 'child',
        'name' => 'Test Child',
    ]);

    // Create assets
    $this->property = Property::factory()->create([
        'user_id' => $this->user->id,
        'current_value' => 500000.00,
        'ownership_percentage' => 100.00,
    ]);

    $this->investment = InvestmentAccount::factory()->create([
        'user_id' => $this->user->id,
        'current_value' => 50000.00,
        'ownership_percentage' => 100.00,
    ]);
});

describe('getCompleteProfile', function () {
    test('returns complete user profile with all sections', function () {
        $profile = $this->service->getCompleteProfile($this->user);

        expect($profile)->toBeArray();
        expect($profile)->toHaveKeys([
            'personal_info',
            'income_occupation',
            'family_members',
            'assets_summary',
            'liabilities_summary',
        ]);
    });

    test('returns correct personal information', function () {
        $profile = $this->service->getCompleteProfile($this->user);

        expect($profile['personal_info'])->toHaveKeys([
            'id',
            'name',
            'email',
            'date_of_birth',
            'gender',
            'marital_status',
        ]);

        expect($profile['personal_info']['name'])->toBe('Test User');
        expect($profile['personal_info']['email'])->toBe('test@example.com');
    });

    test('returns correct income and occupation data', function () {
        $profile = $this->service->getCompleteProfile($this->user);

        expect($profile['income_occupation'])->toHaveKeys([
            'occupation',
            'annual_employment_income',
            'annual_rental_income',
            'annual_dividend_income',
            'total_annual_income',
        ]);

        expect($profile['income_occupation']['occupation'])->toBe('Software Engineer');
        expect($profile['income_occupation']['annual_employment_income'])->toBe('75000.00');
        expect($profile['income_occupation']['total_annual_income'])->toBe(90000.00);
    });

    test('calculates total annual income correctly', function () {
        $profile = $this->service->getCompleteProfile($this->user);

        $expectedTotal =
            75000.00 + // employment
            0.00 +     // self-employment
            12000.00 + // rental
            3000.00 +  // dividend
            0.00;      // other

        expect($profile['income_occupation']['total_annual_income'])->toBe($expectedTotal);
    });

    test('includes family members in profile', function () {
        $profile = $this->service->getCompleteProfile($this->user);

        expect($profile['family_members'])->toBeInstanceOf(\Illuminate\Database\Eloquent\Collection::class);
        expect($profile['family_members'])->toHaveCount(1);
        expect($profile['family_members'][0]->name)->toBe('Test Child');
    });

    test('returns empty collection when user has no family members', function () {
        // Delete family members
        FamilyMember::where('user_id', $this->user->id)->delete();

        $profile = $this->service->getCompleteProfile($this->user);

        expect($profile['family_members'])->toBeInstanceOf(\Illuminate\Database\Eloquent\Collection::class);
        expect($profile['family_members'])->toHaveCount(0);
    });

    test('includes assets summary in profile', function () {
        $profile = $this->service->getCompleteProfile($this->user);

        expect($profile['assets_summary'])->toBeArray();
        expect($profile['assets_summary'])->toHaveKeys([
            'cash',
            'investments',
            'properties',
            'business_interests',
            'chattels',
            'pensions',
            'total',
        ]);
    });

    test('calculates assets summary correctly', function () {
        $profile = $this->service->getCompleteProfile($this->user);

        expect($profile['assets_summary']['properties'])->toBe(500000.00);
        expect($profile['assets_summary']['investments'])->toBe(50000.00);
        expect($profile['assets_summary']['total'])->toBeGreaterThan(0);
    });
});

describe('updatePersonalInfo', function () {
    test('updates personal information successfully', function () {
        $updateData = [
            'name' => 'Updated Name',
            'date_of_birth' => '1990-01-01',
            'gender' => 'female',
            'marital_status' => 'married',
            'city' => 'Manchester',
        ];

        $updatedUser = $this->service->updatePersonalInfo($this->user, $updateData);

        expect($updatedUser->name)->toBe('Updated Name');
        expect($updatedUser->date_of_birth->format('Y-m-d'))->toBe('1990-01-01');
        expect($updatedUser->city)->toBe('Manchester');
    });

    test('persists updated information to database', function () {
        $updateData = [
            'name' => 'Database Test User',
            'city' => 'Birmingham',
        ];

        $this->service->updatePersonalInfo($this->user, $updateData);

        // Refresh user from database
        $this->user->refresh();

        expect($this->user->name)->toBe('Database Test User');
        expect($this->user->city)->toBe('Birmingham');
    });

    test('returns updated User model', function () {
        $updateData = ['name' => 'Test'];

        $result = $this->service->updatePersonalInfo($this->user, $updateData);

        expect($result)->toBeInstanceOf(User::class);
        expect($result->id)->toBe($this->user->id);
    });
});

describe('updateIncomeOccupation', function () {
    test('updates income and occupation information successfully', function () {
        $updateData = [
            'occupation' => 'Senior Developer',
            'employer' => 'New Company Ltd',
            'annual_employment_income' => 95000.00,
            'annual_rental_income' => 15000.00,
        ];

        $updatedUser = $this->service->updateIncomeOccupation($this->user, $updateData);

        expect($updatedUser->occupation)->toBe('Senior Developer');
        expect($updatedUser->employer)->toBe('New Company Ltd');
        expect($updatedUser->annual_employment_income)->toBe('95000.00');
    });

    test('persists updated income information to database', function () {
        $updateData = [
            'annual_employment_income' => 100000.00,
            'annual_self_employment_income' => 20000.00,
        ];

        $this->service->updateIncomeOccupation($this->user, $updateData);

        // Refresh user from database
        $this->user->refresh();

        expect($this->user->annual_employment_income)->toBe('100000.00');
        expect($this->user->annual_self_employment_income)->toBe('20000.00');
    });

    test('returns updated User model', function () {
        $updateData = ['occupation' => 'Test'];

        $result = $this->service->updateIncomeOccupation($this->user, $updateData);

        expect($result)->toBeInstanceOf(User::class);
        expect($result->id)->toBe($this->user->id);
    });
});
