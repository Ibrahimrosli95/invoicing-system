<?php

// Temporary debug script to check templates
// Run: php debug_templates.php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ServiceTemplate;
use App\Models\User;

echo "=== Service Template Debug ===\n\n";

// Get all templates (no filters)
$allTemplates = ServiceTemplate::with('category')->get();
echo "Total templates in database: " . $allTemplates->count() . "\n\n";

if ($allTemplates->count() > 0) {
    foreach ($allTemplates as $template) {
        echo "Template ID: {$template->id}\n";
        echo "  Name: {$template->name}\n";
        echo "  Company ID: {$template->company_id}\n";
        echo "  Category ID: " . ($template->category_id ?? 'NULL') . "\n";
        echo "  Category Name: " . ($template->category->name ?? 'N/A') . "\n";
        echo "  Is Active: " . ($template->is_active ? 'Yes' : 'No') . "\n";
        echo "  Applicable Teams: " . json_encode($template->applicable_teams) . "\n";
        echo "  Created By: {$template->created_by}\n";
        echo "  Created At: {$template->created_at}\n";
        echo "\n";
    }
}

// Check if user can see templates
echo "\n=== User Access Check ===\n\n";
$user = User::where('email', 'admin@binagroup.com')->first();
if ($user) {
    echo "User: {$user->name} (ID: {$user->id})\n";
    echo "Company ID: {$user->company_id}\n";
    echo "Roles: " . $user->roles->pluck('name')->implode(', ') . "\n";
    echo "Teams: " . $user->teams->pluck('name')->implode(', ') . "\n\n";
    
    // Test the query
    $visibleTemplates = ServiceTemplate::forCompany()->forUserTeams()->get();
    echo "Templates visible to this user: {$visibleTemplates->count()}\n";
    
    if ($visibleTemplates->count() > 0) {
        foreach ($visibleTemplates as $template) {
            echo "  - {$template->name}\n";
        }
    }
}

echo "\n=== Done ===\n";
