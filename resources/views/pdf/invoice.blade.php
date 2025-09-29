<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Custom PDF-specific styles */
        .page {
            width: 210mm;
            min-height: 297mm;
            padding: 15mm;
            margin: 0 auto;
            position: relative;
        }

        /* Watermark for draft invoices */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 120px;
            font-weight: bold;
            color: rgba(239, 68, 68, 0.1);
            z-index: -1;
            user-select: none;
            pointer-events: none;
        }

        /* Overdue watermark for overdue invoices */
        .overdue-watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 100px;
            font-weight: bold;
            color: rgba(220, 38, 38, 0.2);
            z-index: -1;
            user-select: none;
            pointer-events: none;
        }

        /* Print styles */
        @media print {
            .page {
                margin: 0;
                box-shadow: none;
            }
        }
    </style>
</head>
<body class="bg-white font-sans text-gray-900">
    <!-- Watermark for draft invoices -->
    @if($invoice->status === 'DRAFT')
        <div class="watermark">DRAFT</div>
    @endif

    <!-- Watermark for overdue invoices -->
    @if($invoice->status === 'OVERDUE')
        <div class="overdue-watermark">OVERDUE</div>
    @endif

    <div class="page">
        <!-- Invoice Document using the shared partial -->
        @include('invoices.partials.document', [
            'invoice' => $invoice,
            'settings' => [
                'optional_sections' => [
                    'show_company_logo' => true,
                    'show_shipping' => !empty($invoice->shipping_info),
                    'show_payment_instructions' => !empty($invoice->payment_instructions),
                    'show_signatures' => false // Signatures not needed in PDF
                ]
            ],
            'mode' => 'pdf'
        ])

        <!-- PDF-specific sections not in shared partial -->

        <!-- Payment History -->
        @if($invoice->paymentRecords->count() > 0)
        <div class="mt-8 border-t border-gray-200 pt-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Payment History</h3>
            @foreach($invoice->paymentRecords as $payment)
                <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-3 rounded-r">
                    <div class="flex justify-between items-center mb-1">
                        <span class="font-bold text-green-800">RM {{ number_format($payment->amount, 2) }}</span>
                        <span class="text-sm text-gray-600">{{ $payment->payment_method }} • {{ $payment->payment_date->format('d/m/Y') }}</span>
                    </div>
                    @if($payment->reference_number || $payment->receipt_number)
                        <div class="text-sm text-gray-600 font-mono">
                            @if($payment->reference_number)Ref: {{ $payment->reference_number }} • @endif
                            @if($payment->receipt_number)Receipt: {{ $payment->receipt_number }}@endif
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
        @endif

        <!-- Social Proof Section -->
        @php
            $pdfService = app(\App\Services\PDFService::class);
            $proofs = $pdfService->getProofsForPDF($invoice, 'invoice');
        @endphp

        @if($proofs->isNotEmpty())
            <div class="mt-8 border-t border-gray-200 pt-6" style="page-break-inside: avoid;">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ $pdfService->getProofSectionTitle('invoice') }}</h3>

                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                    <!-- Proof Grid -->
                    <div class="grid grid-cols-3 gap-3 mb-4">
                        @foreach($proofs->take(6) as $proof)
                            <div class="bg-white p-3 rounded border border-gray-200">
                                <!-- Proof Header -->
                                <div class="flex items-center mb-2">
                                    @if($proof->is_featured)
                                        <span class="bg-yellow-400 text-white text-xs px-2 py-1 rounded-full mr-2 font-semibold">★</span>
                                    @endif
                                    <span class="bg-blue-500 text-white text-xs px-2 py-1 rounded-full font-semibold uppercase">
                                        {{ $proof->type_label }}
                                    </span>
                                </div>

                                <!-- Proof Title -->
                                <div class="text-sm font-semibold text-gray-900 mb-1 leading-tight">
                                    {{ Str::limit($proof->title, 35) }}
                                </div>

                                <!-- Proof Description -->
                                @if($proof->description)
                                    <div class="text-xs text-gray-600 mb-2 leading-tight">
                                        {{ Str::limit($proof->description, 60) }}
                                    </div>
                                @endif

                                <!-- Proof Stats -->
                                @if($proof->views_count > 0 || $proof->conversion_impact)
                                    <div class="text-xs text-gray-500 flex justify-between">
                                        @if($proof->views_count > 0)
                                            <span>{{ number_format($proof->views_count) }} views</span>
                                        @endif
                                        @if($proof->conversion_impact)
                                            <span class="bg-green-500 text-white px-1 py-0.5 rounded text-xs font-semibold">
                                                {{ $proof->conversion_impact }}%
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <!-- Additional Proofs Summary -->
                    @if($proofs->count() > 6)
                        <div class="text-center p-2 bg-white rounded border border-gray-200 mb-3">
                            <div class="text-sm text-gray-600">
                                + {{ $proofs->count() - 6 }} more {{ Str::plural('credential', $proofs->count() - 6) }} available
                            </div>
                        </div>
                    @endif

                    <!-- Proof Summary -->
                    @php
                        $proofAnalytics = $pdfService->getProofAnalytics($proofs);
                    @endphp

                    <div class="pt-3 border-t border-gray-300 text-center">
                        <div class="text-sm text-gray-600">
                            <strong>{{ $proofAnalytics['total_proofs'] }}</strong> credentials •
                            @if($proofAnalytics['featured_count'] > 0)
                                <strong>{{ $proofAnalytics['featured_count'] }}</strong> featured •
                            @endif
                            @if($proofAnalytics['average_impact'])
                                <strong>{{ number_format($proofAnalytics['average_impact'], 1) }}%</strong> avg. impact
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Footer -->
        <div class="fixed bottom-0 left-0 right-0 border-t border-gray-200 p-4 bg-white text-center text-sm text-gray-500">
            <div class="flex justify-between items-center">
                <span>{{ $invoice->company->name ?? 'Bina Group' }} - Invoice {{ $invoice->number }}</span>
                <span>Generated on {{ now()->format('d/m/Y H:i') }}</span>
            </div>
        </div>
    </div>
</body>
</html>