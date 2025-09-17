@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h1 class="text-2xl font-bold text-gray-900">Two-Factor Authentication</h1>
                <p class="mt-1 text-sm text-gray-600">Secure your account with two-factor authentication</p>
            </div>

            <div class="p-6 space-y-6">
                @if(session('success'))
                    <div class="bg-green-50 border border-green-200 rounded-md p-4">
                        <div class="flex">
                            <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <p class="ml-3 text-sm text-green-700">{{ session('success') }}</p>
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-50 border border-red-200 rounded-md p-4">
                        <div class="flex">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            <p class="ml-3 text-sm text-red-700">{{ session('error') }}</p>
                        </div>
                    </div>
                @endif

                @if(!auth()->user()->two_factor_enabled)
                    <!-- Setup 2FA -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                        <h2 class="text-lg font-semibold text-blue-900 mb-4">Enable Two-Factor Authentication</h2>
                        <p class="text-blue-700 mb-4">Scan the QR code below with your authenticator app to set up two-factor authentication.</p>

                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="font-medium text-gray-900 mb-2">QR Code</h3>
                                <div class="bg-white p-4 rounded-lg border text-center">
                                    @if(str_starts_with($qrCodeImage, 'data:image'))
                                        <img src="{{ $qrCodeImage }}" alt="QR Code" class="mx-auto">
                                    @else
                                        <p class="text-sm text-gray-600">Fallback URL: <code class="bg-gray-100 px-2 py-1 rounded">{{ $qrCodeImage }}</code></p>
                                    @endif
                                </div>

                                <div class="mt-4">
                                    <h4 class="font-medium text-gray-900 mb-2">Manual Entry Key</h4>
                                    <div class="bg-gray-100 p-3 rounded border font-mono text-sm break-all">
                                        {{ $secret }}
                                    </div>
                                    <button onclick="navigator.clipboard.writeText('{{ $secret }}')" class="mt-2 text-sm text-blue-600 hover:text-blue-800">
                                        Copy to clipboard
                                    </button>
                                </div>
                            </div>

                            <div>
                                <h3 class="font-medium text-gray-900 mb-4">Instructions</h3>
                                <ol class="list-decimal list-inside space-y-2 text-sm text-gray-700">
                                    <li>Download and install an authenticator app:
                                        <ul class="mt-1 ml-4 list-disc list-inside text-xs">
                                            <li>Google Authenticator</li>
                                            <li>Microsoft Authenticator</li>
                                            <li>Authy</li>
                                            <li>1Password</li>
                                        </ul>
                                    </li>
                                    <li>Scan the QR code or enter the key manually</li>
                                    <li>Enter the 6-digit code from your app below</li>
                                    <li>Enter your password to confirm</li>
                                </ol>

                                <form method="POST" action="{{ route('two-factor.enable') }}" class="mt-6 space-y-4">
                                    @csrf
                                    <div>
                                        <label for="code" class="block text-sm font-medium text-gray-700">Verification Code</label>
                                        <input type="text" name="code" id="code" maxlength="6" pattern="[0-9]{6}"
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                               placeholder="000000" required>
                                        @error('code')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="password" class="block text-sm font-medium text-gray-700">Current Password</label>
                                        <input type="password" name="password" id="password"
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                               required>
                                        @error('password')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                        Enable Two-Factor Authentication
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @else
                    <!-- 2FA Enabled -->
                    <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                        <div class="flex items-center mb-4">
                            <svg class="h-6 w-6 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <h2 class="text-lg font-semibold text-green-900">Two-Factor Authentication Enabled</h2>
                        </div>
                        <p class="text-green-700 mb-4">Your account is protected with two-factor authentication.</p>
                        <p class="text-sm text-green-600 mb-4">Enabled on {{ auth()->user()->two_factor_enabled_at?->format('M j, Y \a\t g:i A') }}</p>

                        <div class="flex space-x-4">
                            <button onclick="showRecoveryCodes()" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                                View Recovery Codes
                            </button>
                            <button onclick="showRegenerateForm()" class="bg-yellow-600 text-white px-4 py-2 rounded-md hover:bg-yellow-700">
                                Regenerate Recovery Codes
                            </button>
                            <button onclick="showDisableForm()" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">
                                Disable 2FA
                            </button>
                        </div>
                    </div>

                    <!-- Recovery Codes Display -->
                    @if(session('two_factor_recovery_codes') || $backupCodes)
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                            <div class="flex items-center mb-4">
                                <svg class="h-6 w-6 text-yellow-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                <h3 class="text-lg font-semibold text-yellow-900">Recovery Codes</h3>
                            </div>
                            <p class="text-yellow-700 mb-4">Save these recovery codes in a safe place. Each code can only be used once.</p>

                            <div class="bg-white border rounded-lg p-4 mb-4">
                                <div class="grid grid-cols-2 gap-2 font-mono text-sm">
                                    @foreach(session('two_factor_recovery_codes', $backupCodes ?? []) as $code)
                                        <div class="bg-gray-50 p-2 rounded text-center">{{ $code }}</div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="flex space-x-4">
                                <button onclick="printCodes()" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                                    Print Codes
                                </button>
                                <a href="{{ route('two-factor.download-recovery-codes') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                                    Download as Text File
                                </a>
                            </div>
                        </div>
                    @endif

                    <!-- Hidden Forms -->
                    <div id="regenerateForm" class="hidden bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-yellow-900 mb-4">Regenerate Recovery Codes</h3>
                        <p class="text-yellow-700 mb-4">This will invalidate all existing recovery codes and generate new ones.</p>

                        <form method="POST" action="{{ route('two-factor.regenerate-recovery-codes') }}">
                            @csrf
                            <div class="mb-4">
                                <label for="regen_password" class="block text-sm font-medium text-gray-700">Current Password</label>
                                <input type="password" name="password" id="regen_password"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500"
                                       required>
                            </div>
                            <div class="flex space-x-3">
                                <button type="submit" class="bg-yellow-600 text-white px-4 py-2 rounded-md hover:bg-yellow-700">
                                    Regenerate Codes
                                </button>
                                <button type="button" onclick="hideRegenerateForm()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>

                    <div id="disableForm" class="hidden bg-red-50 border border-red-200 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-red-900 mb-4">Disable Two-Factor Authentication</h3>
                        <p class="text-red-700 mb-4">This will remove two-factor authentication from your account. You can re-enable it at any time.</p>

                        <form method="POST" action="{{ route('two-factor.disable') }}">
                            @csrf
                            <div class="grid md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label for="disable_password" class="block text-sm font-medium text-gray-700">Current Password</label>
                                    <input type="password" name="password" id="disable_password"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500"
                                           required>
                                </div>
                                <div>
                                    <label for="disable_code" class="block text-sm font-medium text-gray-700">Verification Code or Recovery Code</label>
                                    <input type="text" name="code" id="disable_code"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500"
                                           placeholder="6-digit code or 8-character recovery code" required>
                                </div>
                            </div>
                            <div class="flex space-x-3">
                                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">
                                    Disable Two-Factor Authentication
                                </button>
                                <button type="button" onclick="hideDisableForm()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
function showRecoveryCodes() {
    // Recovery codes are already displayed if available
    const codesSection = document.querySelector('.bg-yellow-50');
    if (codesSection) {
        codesSection.scrollIntoView({ behavior: 'smooth' });
    }
}

function showRegenerateForm() {
    document.getElementById('regenerateForm').classList.remove('hidden');
    document.getElementById('disableForm').classList.add('hidden');
}

function hideRegenerateForm() {
    document.getElementById('regenerateForm').classList.add('hidden');
}

function showDisableForm() {
    document.getElementById('disableForm').classList.remove('hidden');
    document.getElementById('regenerateForm').classList.add('hidden');
}

function hideDisableForm() {
    document.getElementById('disableForm').classList.add('hidden');
}

function printCodes() {
    const codes = @json(session('two_factor_recovery_codes', $backupCodes ?? []));
    let printContent = `
        <html>
        <head>
            <title>Two-Factor Authentication Recovery Codes</title>
            <style>
                body { font-family: Arial, sans-serif; padding: 20px; }
                .header { text-align: center; margin-bottom: 30px; }
                .codes { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin: 20px 0; }
                .code { padding: 10px; border: 1px solid #ccc; text-align: center; font-family: monospace; }
                .warning { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>Two-Factor Authentication Recovery Codes</h1>
                <p>Account: ${@json(auth()->user()->email)}</p>
                <p>Generated: ${new Date().toLocaleString()}</p>
            </div>
            <div class="warning">
                <strong>Important:</strong> Keep these codes safe and secure. Each code can only be used once.
            </div>
            <div class="codes">
    `;

    codes.forEach(code => {
        printContent += `<div class="code">${code}</div>`;
    });

    printContent += `
            </div>
            <div class="warning">
                Store these codes in a safe place separate from your device. If you lose access to your authenticator app, you can use these codes to regain access to your account.
            </div>
        </body>
        </html>
    `;

    const printWindow = window.open('', '_blank');
    printWindow.document.write(printContent);
    printWindow.document.close();
    printWindow.print();
}

// Auto-format verification code input
document.addEventListener('DOMContentLoaded', function() {
    const codeInputs = document.querySelectorAll('input[name="code"]');
    codeInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            // Remove non-numeric characters for 6-digit codes
            if (this.maxLength === 6) {
                this.value = this.value.replace(/\D/g, '');
            }
        });
    });
});
</script>
@endsection