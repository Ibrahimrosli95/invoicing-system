<?php $__env->startSection('title', 'Company Settings'); ?>

<?php $__env->startSection('header'); ?>
<div class="bg-white border-b border-gray-200 px-6 py-4">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <?php echo e(__('Company Settings')); ?>

            </h2>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $company)): ?>
                <a href="<?php echo e(route('company.edit')); ?>" 
                   class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out">
                    <?php echo e(__('Edit Settings')); ?>

                </a>
            <?php endif; ?>
        </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Company Information -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex items-center mb-6">
                        <!-- Company Logo -->
                        <div class="flex-shrink-0 mr-6">
                            <?php if($company->logo): ?>
                                <img src="<?php echo e(Storage::url($company->logo)); ?>" 
                                     alt="<?php echo e($company->name); ?> Logo"
                                     class="h-16 w-16 object-cover rounded-lg border border-gray-200">
                            <?php else: ?>
                                <div class="h-16 w-16 bg-gray-100 rounded-lg border border-gray-200 flex items-center justify-center">
                                    <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H3m2 0h3M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Company Details -->
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900"><?php echo e($company->name); ?></h3>
                            <?php if($company->tagline): ?>
                                <p class="text-gray-600 mt-1"><?php echo e($company->tagline); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Company Information Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        
                        <!-- Contact Information -->
                        <div>
                            <h4 class="font-medium text-gray-900 mb-3">Contact Information</h4>
                            <dl class="space-y-2">
                                <?php if($company->email): ?>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                                        <dd class="text-sm text-gray-900"><?php echo e($company->email); ?></dd>
                                    </div>
                                <?php endif; ?>
                                <?php if($company->phone): ?>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Phone</dt>
                                        <dd class="text-sm text-gray-900"><?php echo e($company->phone); ?></dd>
                                    </div>
                                <?php endif; ?>
                                <?php if($company->website): ?>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Website</dt>
                                        <dd class="text-sm text-gray-900">
                                            <a href="<?php echo e($company->website); ?>" target="_blank" class="text-blue-600 hover:text-blue-800">
                                                <?php echo e($company->website); ?>

                                            </a>
                                        </dd>
                                    </div>
                                <?php endif; ?>
                            </dl>
                        </div>

                        <!-- Address Information -->
                        <div>
                            <h4 class="font-medium text-gray-900 mb-3">Address</h4>
                            <?php if($company->address): ?>
                                <address class="text-sm text-gray-900 not-italic">
                                    <?php echo e($company->address); ?><br>
                                    <?php if($company->city): ?><?php echo e($company->city); ?><?php endif; ?>
                                    <?php if($company->state && $company->city): ?>, <?php endif; ?>
                                    <?php if($company->state): ?><?php echo e($company->state); ?><?php endif; ?>
                                    <?php if($company->postal_code): ?> <?php echo e($company->postal_code); ?><?php endif; ?><br>
                                    <?php if($company->country): ?><?php echo e($company->country); ?><?php endif; ?>
                                </address>
                            <?php else: ?>
                                <p class="text-sm text-gray-500">No address specified</p>
                            <?php endif; ?>
                        </div>

                        <!-- Business Information -->
                        <div>
                            <h4 class="font-medium text-gray-900 mb-3">Business Information</h4>
                            <dl class="space-y-2">
                                <?php if($company->registration_number): ?>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Registration Number</dt>
                                        <dd class="text-sm text-gray-900"><?php echo e($company->registration_number); ?></dd>
                                    </div>
                                <?php endif; ?>
                                <?php if($company->tax_number): ?>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Tax Number</dt>
                                        <dd class="text-sm text-gray-900"><?php echo e($company->tax_number); ?></dd>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Timezone</dt>
                                    <dd class="text-sm text-gray-900"><?php echo e($company->timezone ?? 'UTC'); ?></dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Currency</dt>
                                    <dd class="text-sm text-gray-900"><?php echo e($company->currency ?? 'USD'); ?></dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Branding & Customization -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Branding & Customization</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Color Scheme -->
                        <div>
                            <h4 class="font-medium text-gray-900 mb-3">Color Scheme</h4>
                            <div class="flex space-x-4">
                                <?php if($company->primary_color): ?>
                                    <div class="flex items-center space-x-2">
                                        <div class="w-8 h-8 rounded border border-gray-200" 
                                             style="background-color: <?php echo e($company->primary_color); ?>"></div>
                                        <span class="text-sm text-gray-600">Primary: <?php echo e($company->primary_color); ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if($company->secondary_color): ?>
                                    <div class="flex items-center space-x-2">
                                        <div class="w-8 h-8 rounded border border-gray-200" 
                                             style="background-color: <?php echo e($company->secondary_color); ?>"></div>
                                        <span class="text-sm text-gray-600">Secondary: <?php echo e($company->secondary_color); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php if(!$company->primary_color && !$company->secondary_color): ?>
                                <p class="text-sm text-gray-500">Default color scheme</p>
                            <?php endif; ?>
                        </div>

                        <!-- Document Settings -->
                        <div>
                            <h4 class="font-medium text-gray-900 mb-3">Document Settings</h4>
                            <dl class="space-y-2">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Date Format</dt>
                                    <dd class="text-sm text-gray-900"><?php echo e($company->date_format ?? 'Y-m-d'); ?></dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Number Format</dt>
                                    <dd class="text-sm text-gray-900"><?php echo e($company->number_format ?? 'Default'); ?></dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Information -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">System Information</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Total Users</dt>
                            <dd class="text-2xl font-semibold text-gray-900"><?php echo e($company->users->count()); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Total Teams</dt>
                            <dd class="text-2xl font-semibold text-gray-900"><?php echo e($company->teams->count()); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Created</dt>
                            <dd class="text-sm text-gray-900"><?php echo e($company->created_at->format('M j, Y')); ?></dd>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /mnt/d/Bina Invoicing System/resources/views/company/show.blade.php ENDPATH**/ ?>