<?php

/**
 * Diagnostic script to check spouse and family member data
 *
 * Usage: php debug-spouse.php <email>
 * Example: php debug-spouse.php angela@example.com
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

if ($argc < 2) {
    echo "Usage: php debug-spouse.php <email>\n";
    echo "Example: php debug-spouse.php angela@example.com\n";
    exit(1);
}

$email = $argv[1];

echo "=== SPOUSE & FAMILY DIAGNOSTIC REPORT ===\n\n";

// Find the user
$user = App\Models\User::where('email', $email)->first();

if (! $user) {
    echo "ERROR: User with email '{$email}' not found.\n";
    exit(1);
}

echo "USER INFORMATION:\n";
echo "  ID: {$user->id}\n";
echo "  Name: {$user->name}\n";
echo "  Email: {$user->email}\n";
echo '  Marital Status: '.($user->marital_status ?? 'NULL')."\n";
echo '  Spouse ID: '.($user->spouse_id ?? 'NULL')."\n\n";

// Check spouse
if ($user->spouse_id) {
    $spouse = App\Models\User::find($user->spouse_id);

    if ($spouse) {
        echo "SPOUSE INFORMATION:\n";
        echo "  ID: {$spouse->id}\n";
        echo "  Name: {$spouse->name}\n";
        echo "  Email: {$spouse->email}\n";
        echo '  Their spouse_id: '.($spouse->spouse_id ?? 'NULL')."\n";

        // Check if bidirectional
        if ($spouse->spouse_id == $user->id) {
            echo "  ✓ Bidirectional link: CORRECT\n";
        } else {
            echo "  ✗ Bidirectional link: BROKEN (their spouse_id={$spouse->spouse_id}, should be {$user->id})\n";
        }
    } else {
        echo "SPOUSE INFORMATION:\n";
        echo "  ✗ ERROR: Spouse with ID {$user->spouse_id} NOT FOUND in database\n";
    }
} else {
    echo "SPOUSE INFORMATION:\n";
    echo "  No spouse linked (spouse_id is NULL)\n";
}

echo "\n";

// Check family members
echo "FAMILY MEMBERS:\n";
$familyMembers = App\Models\FamilyMember::where('user_id', $user->id)->get();

if ($familyMembers->isEmpty()) {
    echo "  No family members found\n";
} else {
    foreach ($familyMembers as $member) {
        echo "  - {$member->name} (Relationship: {$member->relationship}";
        if ($member->date_of_birth) {
            echo ", DOB: {$member->date_of_birth}";
        }
        echo ")\n";

        // Check if family member has same name as user (BUG)
        if ($member->name === $user->name) {
            echo "    ⚠️  WARNING: Family member has same name as user - this is likely incorrect!\n";
        }

        // Check if relationship is 'spouse'
        if ($member->relationship === 'spouse') {
            // Check if this name matches the actual spouse
            if ($user->spouse_id) {
                $actualSpouse = App\Models\User::find($user->spouse_id);
                if ($actualSpouse && $member->name !== $actualSpouse->name) {
                    echo "    ✗ ERROR: Family member name '{$member->name}' does NOT match actual spouse name '{$actualSpouse->name}'\n";
                } elseif ($actualSpouse && $member->name === $actualSpouse->name) {
                    echo "    ✓ Correct: Matches actual spouse name\n";
                }
            }
        }
    }
}

echo "\n=== END DIAGNOSTIC REPORT ===\n";
