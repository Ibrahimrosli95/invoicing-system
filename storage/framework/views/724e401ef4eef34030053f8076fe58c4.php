<?php if($certifications->isNotEmpty()): ?>
<div class="certification-badges-container" x-data="certificationBadges()">
    <!-- Header -->
    <div class="mb-6">
        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
            <svg class="w-5 h-5 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            Professional Certifications
            <?php if($showVerification): ?>
                <span class="ml-2 text-xs text-green-600 bg-green-100 px-2 py-1 rounded-full">
                    Verified Credentials
                </span>
            <?php endif; ?>
        </h3>
        <p class="text-sm text-gray-600 mt-1">
            <?php echo e($certifications->count()); ?> active certification<?php echo e($certifications->count() !== 1 ? 's' : ''); ?>

        </p>
    </div>

    <!-- Layout Toggle (for grid/row layouts) -->
    <?php if(in_array($layout, ['grid', 'row'])): ?>
    <div class="flex justify-end mb-4">
        <div class="flex bg-gray-100 rounded-lg p-1">
            <button @click="layout = 'grid'" 
                    :class="layout === 'grid' ? 'bg-white shadow-sm' : 'hover:bg-gray-200'"
                    class="px-3 py-1 rounded-md text-xs font-medium transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                </svg>
            </button>
            <button @click="layout = 'row'" 
                    :class="layout === 'row' ? 'bg-white shadow-sm' : 'hover:bg-gray-200'"
                    class="px-3 py-1 rounded-md text-xs font-medium transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                </svg>
            </button>
        </div>
    </div>
    <?php endif; ?>

    <!-- Grid Layout -->
    <div x-show="layout === 'grid'" 
         class="<?php echo e($layout === 'grid' ? 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4' : 'hidden'); ?>">
        <?php $__currentLoopData = $certifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $certification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition-shadow p-4 group">
            <!-- Featured Badge -->
            <?php if($certification->is_featured): ?>
                <div class="absolute -top-2 -right-2">
                    <span class="bg-yellow-100 text-yellow-800 text-xs font-bold px-2 py-1 rounded-full border border-yellow-300">
                        ⭐ Featured
                    </span>
                </div>
            <?php endif; ?>

            <!-- Certification Badge -->
            <div class="relative">
                <?php if($certification->badge_image_path): ?>
                    <img src="<?php echo e(Storage::url($certification->badge_image_path)); ?>" 
                         alt="<?php echo e($certification->name); ?>"
                         class="w-16 h-16 mx-auto rounded-lg object-cover">
                <?php else: ?>
                    <div class="w-16 h-16 mx-auto bg-<?php echo e($this->getBadgeColor($certification)); ?>-100 rounded-lg flex items-center justify-center">
                        <svg class="w-8 h-8 text-<?php echo e($this->getBadgeColor($certification)); ?>-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                <?php endif; ?>

                <!-- Status Badge -->
                <div class="absolute -top-1 -right-1">
                    <span class="w-4 h-4 bg-<?php echo e($this->getBadgeColor($certification)); ?>-500 rounded-full border-2 border-white block"></span>
                </div>
            </div>

            <!-- Certification Info -->
            <div class="mt-4 text-center">
                <h4 class="font-semibold text-gray-900 text-sm group-hover:text-blue-600 transition-colors">
                    <?php echo e($certification->name); ?>

                </h4>
                <p class="text-xs text-gray-600 mt-1"><?php echo e($certification->issuing_authority); ?></p>
                
                <?php if($showVerification && $certification->verification_status): ?>
                    <div class="flex items-center justify-center mt-2">
                        <svg class="w-4 h-4 <?php echo e($this->getVerificationColor($certification)); ?>" fill="currentColor" viewBox="0 0 20 20">
                            <?php if($this->getVerificationIcon($certification) === 'check-circle'): ?>
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            <?php elseif($this->getVerificationIcon($certification) === 'clock'): ?>
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                            <?php elseif($this->getVerificationIcon($certification) === 'x-circle'): ?>
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            <?php else: ?>
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                            <?php endif; ?>
                        </svg>
                        <span class="text-xs <?php echo e($this->getVerificationColor($certification)); ?> ml-1 capitalize">
                            <?php echo e($certification->verification_status); ?>

                        </span>
                    </div>
                <?php endif; ?>

                <?php if($showExpiration && $certification->expiry_date): ?>
                    <div class="mt-2">
                        <?php
                            $daysUntilExpiry = now()->diffInDays($certification->expiry_date, false);
                        ?>
                        
                        <?php if($daysUntilExpiry < 0): ?>
                            <span class="text-xs text-red-600 bg-red-100 px-2 py-1 rounded-full">
                                Expired <?php echo e(abs($daysUntilExpiry)); ?> day<?php echo e(abs($daysUntilExpiry) !== 1 ? 's' : ''); ?> ago
                            </span>
                        <?php elseif($daysUntilExpiry <= 30): ?>
                            <span class="text-xs text-yellow-600 bg-yellow-100 px-2 py-1 rounded-full">
                                Expires in <?php echo e($daysUntilExpiry); ?> day<?php echo e($daysUntilExpiry !== 1 ? 's' : ''); ?>

                            </span>
                        <?php else: ?>
                            <span class="text-xs text-green-600 bg-green-100 px-2 py-1 rounded-full">
                                Valid until <?php echo e($certification->expiry_date->format('M j, Y')); ?>

                            </span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Actions -->
            <div class="mt-4 flex justify-center space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                <?php if($certification->certificate_file_path): ?>
                    <a href="<?php echo e(route('certifications.download', $certification)); ?>" 
                       class="text-blue-600 hover:text-blue-800">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </a>
                <?php endif; ?>
                
                <?php if($certification->verification_url): ?>
                    <a href="<?php echo e($certification->verification_url); ?>" 
                       target="_blank" 
                       class="text-green-600 hover:text-green-800">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <!-- Row Layout -->
    <div x-show="layout === 'row'" 
         class="<?php echo e($layout === 'row' ? 'space-y-3' : 'hidden'); ?>">
        <?php $__currentLoopData = $certifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $certification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition-shadow p-4 flex items-center space-x-4 group">
            <!-- Badge Image -->
            <div class="relative flex-shrink-0">
                <?php if($certification->badge_image_path): ?>
                    <img src="<?php echo e(Storage::url($certification->badge_image_path)); ?>" 
                         alt="<?php echo e($certification->name); ?>"
                         class="w-12 h-12 rounded-lg object-cover">
                <?php else: ?>
                    <div class="w-12 h-12 bg-<?php echo e($this->getBadgeColor($certification)); ?>-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-<?php echo e($this->getBadgeColor($certification)); ?>-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                <?php endif; ?>
                <span class="absolute -top-1 -right-1 w-4 h-4 bg-<?php echo e($this->getBadgeColor($certification)); ?>-500 rounded-full border-2 border-white"></span>
            </div>

            <!-- Certification Details -->
            <div class="flex-grow">
                <div class="flex items-start justify-between">
                    <div>
                        <h4 class="font-semibold text-gray-900 group-hover:text-blue-600 transition-colors">
                            <?php echo e($certification->name); ?>

                            <?php if($certification->is_featured): ?>
                                <span class="ml-2 text-xs text-yellow-600">⭐</span>
                            <?php endif; ?>
                        </h4>
                        <p class="text-sm text-gray-600"><?php echo e($certification->issuing_authority); ?></p>
                        <p class="text-xs text-gray-500 mt-1">Issued: <?php echo e($certification->issue_date->format('M j, Y')); ?></p>
                    </div>
                    
                    <!-- Status Badges -->
                    <div class="flex items-center space-x-2">
                        <?php if($showVerification && $certification->verification_status): ?>
                            <span class="inline-flex items-center text-xs <?php echo e($this->getVerificationColor($certification)); ?>">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <?php if($this->getVerificationIcon($certification) === 'check-circle'): ?>
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    <?php elseif($this->getVerificationIcon($certification) === 'clock'): ?>
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                    <?php else: ?>
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    <?php endif; ?>
                                </svg>
                                <?php echo e(ucfirst($certification->verification_status)); ?>

                            </span>
                        <?php endif; ?>
                        
                        <?php if($showExpiration && $certification->expiry_date): ?>
                            <?php
                                $daysUntilExpiry = now()->diffInDays($certification->expiry_date, false);
                            ?>
                            
                            <?php if($daysUntilExpiry < 0): ?>
                                <span class="text-xs text-red-600 bg-red-100 px-2 py-1 rounded-full">
                                    Expired
                                </span>
                            <?php elseif($daysUntilExpiry <= 30): ?>
                                <span class="text-xs text-yellow-600 bg-yellow-100 px-2 py-1 rounded-full">
                                    <?php echo e($daysUntilExpiry); ?>d left
                                </span>
                            <?php else: ?>
                                <span class="text-xs text-green-600 bg-green-100 px-2 py-1 rounded-full">
                                    Valid
                                </span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                <?php if($certification->certificate_file_path): ?>
                    <a href="<?php echo e(route('certifications.download', $certification)); ?>" 
                       class="text-blue-600 hover:text-blue-800 p-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </a>
                <?php endif; ?>
                
                <?php if($certification->verification_url): ?>
                    <a href="<?php echo e($certification->verification_url); ?>" 
                       target="_blank" 
                       class="text-green-600 hover:text-green-800 p-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <!-- Compact Layout -->
    <?php if($layout === 'compact'): ?>
    <div class="flex flex-wrap gap-2">
        <?php $__currentLoopData = $certifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $certification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="inline-flex items-center bg-white border border-gray-200 rounded-full px-3 py-2 hover:shadow-sm transition-shadow group">
            <?php if($certification->badge_image_path): ?>
                <img src="<?php echo e(Storage::url($certification->badge_image_path)); ?>" 
                     alt="<?php echo e($certification->name); ?>"
                     class="w-6 h-6 rounded-full object-cover mr-2">
            <?php else: ?>
                <div class="w-6 h-6 bg-<?php echo e($this->getBadgeColor($certification)); ?>-100 rounded-full flex items-center justify-center mr-2">
                    <span class="w-2 h-2 bg-<?php echo e($this->getBadgeColor($certification)); ?>-500 rounded-full"></span>
                </div>
            <?php endif; ?>
            
            <span class="text-sm font-medium text-gray-700 group-hover:text-blue-600">
                <?php echo e($certification->name); ?>

            </span>
            
            <?php if($showVerification && $certification->verification_status === 'verified'): ?>
                <svg class="w-4 h-4 text-green-600 ml-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
            <?php endif; ?>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <?php endif; ?>
</div>

<script>
function certificationBadges() {
    return {
        layout: '<?php echo e($layout); ?>',
        
        init() {
            // Initialize any interactive features
        }
    }
}
</script>

<?php else: ?>
<div class="max-w-4xl mx-auto bg-gray-50 rounded-lg shadow p-8 text-center">
    <div class="text-gray-500">
        <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
        </svg>
        <h3 class="text-lg font-semibold text-gray-700 mb-2">No Certifications Available</h3>
        <p class="text-gray-600">Add some active certifications to display them here.</p>
    </div>
</div>
<?php endif; ?><?php /**PATH /mnt/d/Bina Invoicing System/resources/views/components/certification-badges.blade.php ENDPATH**/ ?>