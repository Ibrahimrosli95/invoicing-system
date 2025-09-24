<?php if($users->isNotEmpty()): ?>
<div class="team-profiles-container" x-data="teamProfiles()">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"/>
                    </svg>
                    <?php if($team): ?>
                        <?php echo e($team->name); ?> Team
                    <?php else: ?>
                        Our Team
                    <?php endif; ?>
                    <?php if($featuredOnly): ?>
                        <span class="ml-2 text-xs text-yellow-600 bg-yellow-100 px-2 py-1 rounded-full">
                            ⭐ Featured Members
                        </span>
                    <?php endif; ?>
                </h3>
                <p class="text-sm text-gray-600 mt-1">
                    <?php echo e($users->count()); ?> team member<?php echo e($users->count() !== 1 ? 's' : ''); ?>

                    <?php if($team && $team->description): ?>
                        • <?php echo e($team->description); ?>

                    <?php endif; ?>
                </p>
            </div>

            <!-- Layout Toggle -->
            <?php if(in_array($layout, ['grid', 'row', 'card'])): ?>
            <div class="flex bg-gray-100 rounded-lg p-1">
                <button @click="layout = 'grid'" 
                        :class="layout === 'grid' ? 'bg-white shadow-sm' : 'hover:bg-gray-200'"
                        class="px-3 py-1 rounded-md text-xs font-medium transition-colors"
                        title="Grid View">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                </button>
                <button @click="layout = 'row'" 
                        :class="layout === 'row' ? 'bg-white shadow-sm' : 'hover:bg-gray-200'"
                        class="px-3 py-1 rounded-md text-xs font-medium transition-colors"
                        title="Row View">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                </button>
                <button @click="layout = 'card'" 
                        :class="layout === 'card' ? 'bg-white shadow-sm' : 'hover:bg-gray-200'"
                        class="px-3 py-1 rounded-md text-xs font-medium transition-colors"
                        title="Card View">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Grid Layout -->
    <div x-show="layout === 'grid'" 
         class="<?php echo e($layout === 'grid' ? 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6' : 'hidden'); ?>">
        <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
            $stats = $this->getUserStats($user);
            $roleColor = $this->getRoleColor($user);
        ?>
        
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow group relative">
            <!-- Featured Badge -->
            <?php if($user->is_featured): ?>
                <div class="absolute -top-2 -right-2">
                    <span class="bg-yellow-100 text-yellow-800 text-xs font-bold px-2 py-1 rounded-full border border-yellow-300">
                        ⭐ Featured
                    </span>
                </div>
            <?php endif; ?>

            <!-- Avatar -->
            <div class="text-center mb-4">
                <div class="relative inline-block">
                    <?php if($this->getAvatarUrl($user)): ?>
                        <img src="<?php echo e($this->getAvatarUrl($user)); ?>" 
                             alt="<?php echo e($user->name); ?>"
                             class="w-20 h-20 rounded-full object-cover border-4 border-gray-100">
                    <?php else: ?>
                        <div class="w-20 h-20 bg-<?php echo e($roleColor); ?>-100 rounded-full flex items-center justify-center border-4 border-gray-100">
                            <span class="text-2xl font-bold text-<?php echo e($roleColor); ?>-600">
                                <?php echo e($this->getUserInitials($user)); ?>

                            </span>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Online Status -->
                    <?php if($user->last_seen_at && $user->last_seen_at->diffInMinutes() < 15): ?>
                        <div class="absolute -bottom-1 -right-1">
                            <span class="w-6 h-6 bg-green-500 rounded-full border-2 border-white flex items-center justify-center">
                                <span class="w-2 h-2 bg-white rounded-full"></span>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
                
                <h4 class="font-semibold text-gray-900 text-lg group-hover:text-blue-600 transition-colors">
                    <?php echo e($user->name); ?>

                </h4>
                
                <div class="mt-1">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-<?php echo e($roleColor); ?>-100 text-<?php echo e($roleColor); ?>-800">
                        <?php echo e($this->getRoleDisplayName($user)); ?>

                    </span>
                </div>

                <?php if($this->getPrimaryTeam($user) && !$team): ?>
                    <p class="text-sm text-gray-600 mt-1"><?php echo e($this->getPrimaryTeam($user)->name); ?></p>
                <?php endif; ?>
            </div>

            <!-- Contact Info -->
            <?php if($showContact): ?>
                <div class="space-y-2 mb-4 text-sm text-gray-600">
                    <?php if($user->email): ?>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <a href="mailto:<?php echo e($user->email); ?>" class="hover:text-blue-600 truncate"><?php echo e($user->email); ?></a>
                        </div>
                    <?php endif; ?>
                    
                    <?php if($user->phone): ?>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            <a href="tel:<?php echo e($user->phone); ?>" class="hover:text-blue-600"><?php echo e($this->formatPhone($user->phone)); ?></a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Performance Stats -->
            <?php if($showStats && !empty($stats)): ?>
                <div class="border-t border-gray-100 pt-4">
                    <div class="grid grid-cols-2 gap-3 text-center">
                        <div>
                            <div class="text-xl font-bold text-gray-900"><?php echo e($stats['leads']); ?></div>
                            <div class="text-xs text-gray-500">Leads</div>
                        </div>
                        <div>
                            <div class="text-xl font-bold text-gray-900"><?php echo e($stats['quotations']); ?></div>
                            <div class="text-xs text-gray-500">Quotes</div>
                        </div>
                    </div>
                    
                    <?php if($stats['conversion_rate'] > 0): ?>
                        <div class="mt-3 text-center">
                            <div class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <?php echo e($stats['conversion_rate']); ?>% Success Rate
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <!-- Row Layout -->
    <div x-show="layout === 'row'" 
         class="<?php echo e($layout === 'row' ? 'space-y-4' : 'hidden'); ?>">
        <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
            $stats = $this->getUserStats($user);
            $roleColor = $this->getRoleColor($user);
        ?>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow group">
            <div class="flex items-center space-x-4">
                <!-- Avatar -->
                <div class="relative flex-shrink-0">
                    <?php if($this->getAvatarUrl($user)): ?>
                        <img src="<?php echo e($this->getAvatarUrl($user)); ?>" 
                             alt="<?php echo e($user->name); ?>"
                             class="w-16 h-16 rounded-full object-cover border-2 border-gray-100">
                    <?php else: ?>
                        <div class="w-16 h-16 bg-<?php echo e($roleColor); ?>-100 rounded-full flex items-center justify-center border-2 border-gray-100">
                            <span class="text-lg font-bold text-<?php echo e($roleColor); ?>-600">
                                <?php echo e($this->getUserInitials($user)); ?>

                            </span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if($user->is_featured): ?>
                        <div class="absolute -top-1 -right-1">
                            <span class="text-yellow-500 text-lg">⭐</span>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- User Info -->
                <div class="flex-grow">
                    <div class="flex items-start justify-between">
                        <div>
                            <h4 class="font-semibold text-gray-900 group-hover:text-blue-600 transition-colors">
                                <?php echo e($user->name); ?>

                            </h4>
                            <div class="flex items-center space-x-2 mt-1">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-<?php echo e($roleColor); ?>-100 text-<?php echo e($roleColor); ?>-800">
                                    <?php echo e($this->getRoleDisplayName($user)); ?>

                                </span>
                                <?php if($this->getPrimaryTeam($user) && !$team): ?>
                                    <span class="text-sm text-gray-500">• <?php echo e($this->getPrimaryTeam($user)->name); ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <?php if($showContact): ?>
                                <div class="flex items-center space-x-4 mt-2 text-sm text-gray-600">
                                    <?php if($user->email): ?>
                                        <a href="mailto:<?php echo e($user->email); ?>" class="hover:text-blue-600 flex items-center">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                            </svg>
                                            <?php echo e($user->email); ?>

                                        </a>
                                    <?php endif; ?>
                                    <?php if($user->phone): ?>
                                        <a href="tel:<?php echo e($user->phone); ?>" class="hover:text-blue-600 flex items-center">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                            </svg>
                                            <?php echo e($this->formatPhone($user->phone)); ?>

                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Stats -->
                        <?php if($showStats && !empty($stats)): ?>
                            <div class="flex items-center space-x-6 text-center">
                                <div>
                                    <div class="text-lg font-bold text-gray-900"><?php echo e($stats['leads']); ?></div>
                                    <div class="text-xs text-gray-500">Leads</div>
                                </div>
                                <div>
                                    <div class="text-lg font-bold text-gray-900"><?php echo e($stats['quotations']); ?></div>
                                    <div class="text-xs text-gray-500">Quotes</div>
                                </div>
                                <?php if($stats['conversion_rate'] > 0): ?>
                                    <div>
                                        <div class="text-lg font-bold text-green-600"><?php echo e($stats['conversion_rate']); ?>%</div>
                                        <div class="text-xs text-gray-500">Success</div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <!-- Card Layout -->
    <div x-show="layout === 'card'" 
         class="<?php echo e($layout === 'card' ? 'grid grid-cols-1 md:grid-cols-2 gap-6' : 'hidden'); ?>">
        <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
            $stats = $this->getUserStats($user);
            $roleColor = $this->getRoleColor($user);
        ?>
        
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow group">
            <!-- Header -->
            <div class="bg-gradient-to-r from-<?php echo e($roleColor); ?>-50 to-<?php echo e($roleColor); ?>-100 px-6 py-4 relative">
                <?php if($user->is_featured): ?>
                    <div class="absolute top-2 right-2">
                        <span class="text-yellow-500 text-lg">⭐</span>
                    </div>
                <?php endif; ?>
                
                <div class="flex items-center space-x-4">
                    <?php if($this->getAvatarUrl($user)): ?>
                        <img src="<?php echo e($this->getAvatarUrl($user)); ?>" 
                             alt="<?php echo e($user->name); ?>"
                             class="w-16 h-16 rounded-full object-cover border-2 border-white">
                    <?php else: ?>
                        <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center border-2 border-<?php echo e($roleColor); ?>-200">
                            <span class="text-xl font-bold text-<?php echo e($roleColor); ?>-600">
                                <?php echo e($this->getUserInitials($user)); ?>

                            </span>
                        </div>
                    <?php endif; ?>
                    
                    <div>
                        <h4 class="font-semibold text-gray-900 text-lg group-hover:text-blue-600 transition-colors">
                            <?php echo e($user->name); ?>

                        </h4>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-white text-<?php echo e($roleColor); ?>-800 border border-<?php echo e($roleColor); ?>-200">
                            <?php echo e($this->getRoleDisplayName($user)); ?>

                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Content -->
            <div class="px-6 py-4">
                <?php if($this->getPrimaryTeam($user) && !$team): ?>
                    <div class="mb-3">
                        <span class="text-sm font-medium text-gray-700">Team: </span>
                        <span class="text-sm text-gray-600"><?php echo e($this->getPrimaryTeam($user)->name); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if($showContact): ?>
                    <div class="space-y-2 mb-4">
                        <?php if($user->email): ?>
                            <div class="flex items-center text-sm">
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <a href="mailto:<?php echo e($user->email); ?>" class="text-gray-600 hover:text-blue-600 truncate"><?php echo e($user->email); ?></a>
                            </div>
                        <?php endif; ?>
                        
                        <?php if($user->phone): ?>
                            <div class="flex items-center text-sm">
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                <a href="tel:<?php echo e($user->phone); ?>" class="text-gray-600 hover:text-blue-600"><?php echo e($this->formatPhone($user->phone)); ?></a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <?php if($showStats && !empty($stats)): ?>
                    <div class="grid grid-cols-3 gap-4 text-center border-t border-gray-100 pt-4">
                        <div>
                            <div class="text-lg font-bold text-gray-900"><?php echo e($stats['leads']); ?></div>
                            <div class="text-xs text-gray-500">Leads</div>
                        </div>
                        <div>
                            <div class="text-lg font-bold text-gray-900"><?php echo e($stats['quotations']); ?></div>
                            <div class="text-xs text-gray-500">Quotes</div>
                        </div>
                        <div>
                            <?php if($stats['conversion_rate'] > 0): ?>
                                <div class="text-lg font-bold text-green-600"><?php echo e($stats['conversion_rate']); ?>%</div>
                                <div class="text-xs text-gray-500">Success</div>
                            <?php else: ?>
                                <div class="text-lg font-bold text-gray-400">—</div>
                                <div class="text-xs text-gray-500">Success</div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <!-- Compact Layout -->
    <?php if($layout === 'compact'): ?>
    <div class="flex flex-wrap gap-3">
        <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php $roleColor = $this->getRoleColor($user); ?>
        
        <div class="inline-flex items-center bg-white border border-gray-200 rounded-full px-4 py-2 hover:shadow-sm transition-shadow group">
            <?php if($this->getAvatarUrl($user)): ?>
                <img src="<?php echo e($this->getAvatarUrl($user)); ?>" 
                     alt="<?php echo e($user->name); ?>"
                     class="w-8 h-8 rounded-full object-cover mr-3">
            <?php else: ?>
                <div class="w-8 h-8 bg-<?php echo e($roleColor); ?>-100 rounded-full flex items-center justify-center mr-3">
                    <span class="text-sm font-bold text-<?php echo e($roleColor); ?>-600">
                        <?php echo e($this->getUserInitials($user)); ?>

                    </span>
                </div>
            <?php endif; ?>
            
            <div>
                <span class="font-medium text-gray-700 group-hover:text-blue-600"><?php echo e($user->name); ?></span>
                <?php if($user->is_featured): ?>
                    <span class="ml-1 text-yellow-500">⭐</span>
                <?php endif; ?>
                <div class="text-xs text-gray-500"><?php echo e($this->getRoleDisplayName($user)); ?></div>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <?php endif; ?>
</div>

<script>
function teamProfiles() {
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
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
        <h3 class="text-lg font-semibold text-gray-700 mb-2">No Team Members Available</h3>
        <p class="text-gray-600">Add some active team members to display their profiles here.</p>
    </div>
</div>
<?php endif; ?><?php /**PATH /mnt/d/Bina Invoicing System/resources/views/components/team-profiles.blade.php ENDPATH**/ ?>