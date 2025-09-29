<?php

use Illuminate\Support\Facades\Route;
use App\Services\PDFService;

// Temporary test route to diagnose PDF generation
Route::get('/test-pdf-generation', function () {
    try {
        echo "<h1>Testing PDF Generation</h1>";

        // Test 1: DomPDF
        echo "<h2>1. Testing DomPDF directly:</h2>";
        $dompdf = new \Dompdf\Dompdf();
        $options = new \Dompdf\Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultPaperSize', 'A4');
        $dompdf->setOptions($options);

        $html = '<!DOCTYPE html><html><head><title>Test</title></head><body><h1>Test PDF</h1><p>This is a test PDF document.</p></body></html>';
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $output = $dompdf->output();
        echo "✓ DomPDF works! Generated: " . strlen($output) . " bytes<br>";

        // Test 2: Browsershot
        echo "<h2>2. Testing Browsershot:</h2>";
        $browsershot = \Spatie\Browsershot\Browsershot::html('<h1>Test</h1><p>Testing Browsershot</p>')
            ->format('A4')
            ->margins(15, 15, 15, 15)
            ->showBackground()
            ->waitUntilNetworkIdle()
            ->timeout(60);

        // Find Chrome path
        $chromePaths = [
            '/home/ibrahim/.cache/puppeteer/chrome/linux-140.0.7339.80/chrome-linux64/chrome',
            '/usr/bin/google-chrome',
            '/usr/bin/chromium-browser',
            '/usr/bin/chromium',
            '/snap/bin/chromium'
        ];

        $chromeFound = false;
        foreach ($chromePaths as $path) {
            if (file_exists($path)) {
                echo "✓ Found Chrome at: $path<br>";
                $browsershot->setChromePath($path);
                $chromeFound = true;
                break;
            }
        }

        if (!$chromeFound) {
            echo "! No Chrome found in standard paths<br>";
        }

        $pdf = $browsershot->pdf();
        echo "✓ Browsershot works! Generated: " . strlen($pdf) . " bytes<br>";

        // Test 3: PDFService
        echo "<h2>3. Testing PDFService with real invoice template:</h2>";
        $invoice = \App\Models\Invoice::first();
        if ($invoice) {
            $pdfService = new PDFService();
            $path = $pdfService->generateInvoicePDF($invoice);
            echo "✓ PDFService works! Generated PDF at: $path<br>";
        } else {
            echo "! No invoices found to test with<br>";
        }

        echo "<br><strong>All tests completed successfully!</strong>";

    } catch (\Exception $e) {
        echo "<br><strong>❌ Error:</strong> " . $e->getMessage() . "<br>";
        echo "<strong>File:</strong> " . $e->getFile() . "<br>";
        echo "<strong>Line:</strong> " . $e->getLine() . "<br>";
        echo "<strong>Trace:</strong><pre>" . $e->getTraceAsString() . "</pre>";
    }
});