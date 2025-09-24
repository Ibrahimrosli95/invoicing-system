<?php $__env->startSection('content'); ?>
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <div class="mx-auto h-12 w-12 text-center">
                <svg class="h-12 w-12 text-blue-600 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Two-Factor Authentication
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Enter the 6-digit code from your authenticator app or use a recovery code
            </p>
        </div>

        <form class="mt-8 space-y-6" method="POST" action="<?php echo e(route('two-factor.verify')); ?>">
            <?php echo csrf_field(); ?>

            <?php if(session('error')): ?>
                <div class="bg-red-50 border border-red-200 rounded-md p-4">
                    <div class="flex">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <p class="ml-3 text-sm text-red-700"><?php echo e(session('error')); ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <div class="rounded-md shadow-sm -space-y-px">
                <div>
                    <label for="code" class="sr-only">Verification Code</label>
                    <input id="code" name="code" type="text" autocomplete="one-time-code" required
                           class="appearance-none rounded-md relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 text-center text-lg tracking-widest"
                           placeholder="000000 or recovery code"
                           maxlength="8">
                    <?php $__errorArgs = ['code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="mt-2 text-sm text-red-600"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>

            <div>
                <button type="submit" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-blue-500 group-hover:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </span>
                    Verify
                </button>
            </div>

            <div class="text-center">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-gray-50 text-gray-500">Need help?</span>
                    </div>
                </div>

                <div class="mt-4 space-y-2 text-sm">
                    <div class="text-gray-600">
                        <strong>Authenticator Code:</strong> Enter the 6-digit code from your authenticator app
                    </div>
                    <div class="text-gray-600">
                        <strong>Recovery Code:</strong> Enter one of your 8-character recovery codes
                    </div>
                </div>

                <div class="mt-6">
                    <a href="<?php echo e(route('login')); ?>" class="font-medium text-blue-600 hover:text-blue-500">
                        ← Back to login
                    </a>
                </div>
            </div>
        </form>

        <div class="mt-8 bg-gray-50 rounded-lg p-4">
            <div class="flex items-start">
                <svg class="h-5 w-5 text-yellow-400 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <div class="text-sm text-gray-700">
                    <p class="font-medium">Security Notice</p>
                    <p class="mt-1">If you're having trouble accessing your authenticator app or have lost your recovery codes, contact your system administrator for assistance.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const codeInput = document.getElementById('code');

    // Auto-focus the input
    codeInput.focus();

    // Format input based on length
    codeInput.addEventListener('input', function(e) {
        const value = this.value.trim();

        // If it's 6 digits, assume it's a TOTP code and only allow numbers
        if (value.length <= 6) {
            this.value = value.replace(/\D/g, '');
        }
        // If it's longer, assume it's a recovery code and allow alphanumeric
        else {
            this.value = value.replace(/[^A-Za-z0-9]/g, '').toUpperCase();
        }
    });

    // Auto-submit when TOTP code is complete
    codeInput.addEventListener('keyup', function(e) {
        if (this.value.length === 6 && /^\d{6}$/.test(this.value)) {
            // Small delay to allow user to see the complete code
            setTimeout(() => {
                this.form.submit();
            }, 500);
        }
    });

    // Handle paste events
    codeInput.addEventListener('paste', function(e) {
        e.preventDefault();
        const paste = (e.clipboardData || window.clipboardData).getData('text');
        const cleaned = paste.replace(/\s/g, '');

        if (/^\d{6}$/.test(cleaned)) {
            // It's a TOTP code
            this.value = cleaned;
            setTimeout(() => {
                this.form.submit();
            }, 500);
        } else if (/^[A-Za-z0-9]{8}$/.test(cleaned)) {
            // It's a recovery code
            this.value = cleaned.toUpperCase();
        }
    });
});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.guest', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /mnt/d/Bina Invoicing System/resources/views/auth/two-factor/verify.blade.php ENDPATH**/ ?>