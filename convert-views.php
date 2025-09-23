<?php

/**
 * Mass conversion script for Blade views
 * Converts from <x-app-layout> to @extends('layouts.app') format
 */

function convertView($filePath) {
    if (!file_exists($filePath)) {
        echo "File not found: $filePath\n";
        return false;
    }

    $content = file_get_contents($filePath);
    $originalContent = $content;

    // Skip if already converted
    if (strpos($content, '@extends(\'layouts.app\')') !== false) {
        echo "Already converted: $filePath\n";
        return true;
    }

    // Skip if not using x-app-layout
    if (strpos($content, '<x-app-layout>') === false) {
        echo "Not x-app-layout format: $filePath\n";
        return true;
    }

    // Extract title from filename or header
    $fileName = basename($filePath, '.blade.php');
    $title = ucwords(str_replace(['-', '_'], ' ', $fileName));

    // Extract header content
    $headerPattern = '/<x-slot name="header">(.*?)<\/x-slot>/s';
    $headerContent = '';
    if (preg_match($headerPattern, $content, $matches)) {
        $headerContent = trim($matches[1]);
        // Clean up the header content (remove extra divs if simple)
        $headerContent = preg_replace('/^\s*<h2[^>]*>(.*?)<\/h2>\s*$/s', '$1', $headerContent);
    }

    // Extract main content (everything between header slot and closing tag)
    $mainPattern = '/(<x-app-layout>.*?<\/x-slot>)(.*?)(<\/x-app-layout>)/s';
    $mainContent = '';
    if (preg_match($mainPattern, $content, $matches)) {
        $mainContent = trim($matches[2]);
    } else {
        // Fallback: extract everything after x-app-layout opening
        $startPos = strpos($content, '<x-app-layout>');
        $endPos = strrpos($content, '</x-app-layout>');
        if ($startPos !== false && $endPos !== false) {
            $mainContent = substr($content, $startPos + strlen('<x-app-layout>'), $endPos - $startPos - strlen('<x-app-layout>'));
            // Remove header slot from main content
            $mainContent = preg_replace('/<x-slot name="header">.*?<\/x-slot>/s', '', $mainContent);
            $mainContent = trim($mainContent);
        }
    }

    // Build new content
    $newContent = "@extends('layouts.app')\n\n";
    $newContent .= "@section('title', '$title')\n\n";

    if (!empty($headerContent)) {
        $newContent .= "@section('header')\n";
        $newContent .= "$headerContent\n";
        $newContent .= "@endsection\n\n";
    }

    $newContent .= "@section('content')\n";
    if (!empty($mainContent)) {
        // Clean up main content
        $mainContent = trim($mainContent);
        // Add proper container if content doesn't have it
        if (strpos($mainContent, 'max-w-7xl') === false && strpos($mainContent, 'py-') === false) {
            $mainContent = "<div class=\"max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8\">\n    $mainContent\n</div>";
        }
        $newContent .= "$mainContent\n";
    }
    $newContent .= "@endsection\n";

    // Write the converted content
    $result = file_put_contents($filePath, $newContent);

    if ($result !== false) {
        echo "✅ Converted: $filePath\n";
        return true;
    } else {
        echo "❌ Failed to write: $filePath\n";
        // Restore original content
        file_put_contents($filePath, $originalContent);
        return false;
    }
}

// List of files to convert
$filesToConvert = [
    'resources/views/dashboard.blade.php',
    'resources/views/dashboard/executive.blade.php',
    'resources/views/dashboard/finance.blade.php',
    'resources/views/dashboard/individual.blade.php',
    'resources/views/dashboard/team.blade.php',
    'resources/views/leads/index.blade.php',
    'resources/views/leads/create.blade.php',
    'resources/views/leads/edit.blade.php',
    'resources/views/leads/show.blade.php',
    'resources/views/leads/kanban.blade.php',
    'resources/views/quotations/index.blade.php',
    'resources/views/quotations/create.blade.php',
    'resources/views/quotations/edit.blade.php',
    'resources/views/quotations/show.blade.php',
    'resources/views/invoices/index.blade.php',
    'resources/views/invoices/create.blade.php',
    'resources/views/invoices/show.blade.php',
    'resources/views/invoices/payment.blade.php',
    'resources/views/teams/index.blade.php',
    'resources/views/teams/create.blade.php',
    'resources/views/teams/edit.blade.php',
    'resources/views/teams/show.blade.php',
    'resources/views/teams/members.blade.php',
    'resources/views/teams/settings.blade.php',
    'resources/views/organization/index.blade.php',
    'resources/views/organization/chart.blade.php',
    'resources/views/users/index.blade.php',
    'resources/views/users/show.blade.php',
    'resources/views/users/profile.blade.php',
    'resources/views/company/show.blade.php',
    'resources/views/company/edit.blade.php',
    'resources/views/pricing/index.blade.php',
    'resources/views/pricing/segments/index.blade.php',
    'resources/views/pricing/tiers/manage.blade.php',
    'resources/views/reports/index.blade.php',
    'resources/views/reports/builder.blade.php',
    'resources/views/reports/results.blade.php',
    'resources/views/notifications/preferences/index.blade.php',
    'resources/views/assessments/index.blade.php',
    'resources/views/assessments/create.blade.php',
    'resources/views/assessments/show.blade.php',
    'resources/views/proofs/index.blade.php',
    'resources/views/proofs/create.blade.php',
    'resources/views/proofs/edit.blade.php',
    'resources/views/proofs/show.blade.php',
    'resources/views/proofs/proof-pack.blade.php',
    'resources/views/audit/index.blade.php',
    'resources/views/audit/show.blade.php',
    'resources/views/audit/dashboard.blade.php',
    'resources/views/profile/edit.blade.php',
];

echo "🚀 Starting mass conversion of " . count($filesToConvert) . " files...\n\n";

$converted = 0;
$failed = 0;

foreach ($filesToConvert as $file) {
    if (convertView($file)) {
        $converted++;
    } else {
        $failed++;
    }
}

echo "\n📊 Conversion Summary:\n";
echo "✅ Successfully converted: $converted files\n";
echo "❌ Failed conversions: $failed files\n";
echo "🎉 Done!\n";

if ($failed === 0) {
    echo "\n🗑️  You can now delete the component files:\n";
    echo "- resources/views/components/app-layout.blade.php\n";
    echo "- resources/views/layouts/sidebar-navigation-component.blade.php\n";
}
?>