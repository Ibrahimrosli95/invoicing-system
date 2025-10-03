<?php
// Quick diagnostic script
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Get the first invoice
$invoice = \App\Models\Invoice::first();

if ($invoice) {
    echo "Invoice ID: {$invoice->id}\n";
    echo "Created By: " . var_export($invoice->created_by, true) . " (type: " . gettype($invoice->created_by) . ")\n";
    echo "Created By User: " . ($invoice->createdBy ? $invoice->createdBy->name : 'NULL') . "\n";
    echo "\n";
    echo "Current Auth ID (if run as web): " . (auth()->check() ? auth()->id() : 'Not authenticated') . "\n";
    echo "\n";
    echo "First user ID: " . \App\Models\User::first()->id . " (type: " . gettype(\App\Models\User::first()->id) . ")\n";
} else {
    echo "No invoices found\n";
}
