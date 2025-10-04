<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF Generation Error</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="max-w-2xl w-full">
            <div class="bg-white shadow-lg rounded-lg p-8">
                <!-- Error Icon -->
                <div class="flex items-center justify-center w-16 h-16 mx-auto mb-6 rounded-full bg-red-100">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>

                <!-- Error Title -->
                <h1 class="text-2xl font-bold text-gray-900 text-center mb-4">
                    PDF Generation Failed
                </h1>

                <!-- Error Message -->
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                    <p class="text-sm text-red-800 font-medium mb-2">Error Details:</p>
                    <p class="text-sm text-red-700 font-mono break-all">{{ $error }}</p>
                </div>

                <!-- Common Solutions -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <p class="text-sm font-medium text-blue-900 mb-3">Common Solutions:</p>
                    <ul class="text-sm text-blue-800 space-y-2">
                        <li class="flex items-start">
                            <svg class="w-4 h-4 mt-0.5 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span><strong>Chrome/Chromium not installed:</strong> Contact system administrator to install Puppeteer or Chrome</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-4 h-4 mt-0.5 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span><strong>Missing data:</strong> Ensure quotation has all required fields (company details, items, etc.)</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-4 h-4 mt-0.5 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span><strong>Template error:</strong> Check Laravel logs for detailed template rendering errors</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-4 h-4 mt-0.5 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span><strong>Permissions issue:</strong> Verify storage directory write permissions</span>
                        </li>
                    </ul>
                </div>

                <!-- Technical Details -->
                @if(isset($quotation))
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6">
                        <p class="text-sm font-medium text-gray-700 mb-2">Quotation Details:</p>
                        <dl class="text-sm text-gray-600 space-y-1">
                            <div class="flex">
                                <dt class="w-32 font-medium">Number:</dt>
                                <dd>{{ $quotation->number }}</dd>
                            </div>
                            <div class="flex">
                                <dt class="w-32 font-medium">Customer:</dt>
                                <dd>{{ $quotation->customer_name }}</dd>
                            </div>
                            <div class="flex">
                                <dt class="w-32 font-medium">Status:</dt>
                                <dd>{{ $quotation->status }}</dd>
                            </div>
                        </dl>
                    </div>
                @endif

                <!-- Action Buttons -->
                <div class="flex gap-3">
                    <a href="{{ route('quotations.show', $quotation ?? 1) }}"
                       class="flex-1 bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-lg text-center transition-colors">
                        Back to Quotation
                    </a>
                    <button onclick="window.location.reload()"
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                        Try Again
                    </button>
                </div>

                <!-- Administrator Note -->
                <p class="text-xs text-gray-500 text-center mt-6">
                    Error has been logged. If the problem persists, please contact your system administrator.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
