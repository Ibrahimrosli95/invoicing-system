<?php if($testimonials->isNotEmpty()): ?>
<div x-data="testimonialCarousel()" 
     x-init="init(<?php echo e($autoplay ? 'true' : 'false'); ?>, <?php echo e($interval); ?>)"
     class="relative max-w-4xl mx-auto bg-white rounded-lg shadow-lg overflow-hidden">
    
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
        <h3 class="text-xl font-semibold text-white flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>
            Customer Testimonials
        </h3>
        <p class="text-blue-100 text-sm mt-1"><?php echo e($testimonials->count()); ?> verified reviews</p>
    </div>

    <!-- Carousel Container -->
    <div class="relative h-80 overflow-hidden">
        <?php $__currentLoopData = $testimonials; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $testimonial): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div x-show="activeSlide === <?php echo e($index); ?>"
             x-transition:enter="transform transition ease-in-out duration-500"
             x-transition:enter-start="translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transform transition ease-in-out duration-500"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="-translate-x-full"
             class="absolute inset-0 p-8 flex flex-col justify-center">
            
            <!-- Quote -->
            <blockquote class="text-lg text-gray-700 italic leading-relaxed mb-6 text-center">
                "<?php echo e($testimonial->review); ?>"
            </blockquote>
            
            <!-- Customer Info -->
            <div class="text-center">
                <div class="flex items-center justify-center space-x-4 mb-3">
                    <?php if($testimonial->customer_photo_path): ?>
                        <img src="<?php echo e(Storage::url($testimonial->customer_photo_path)); ?>" 
                             alt="<?php echo e($testimonial->customer_name); ?>"
                             class="w-12 h-12 rounded-full object-cover border-2 border-gray-200">
                    <?php else: ?>
                        <div class="w-12 h-12 bg-gray-300 rounded-full flex items-center justify-center">
                            <span class="text-gray-600 font-medium text-lg">
                                <?php echo e(substr($testimonial->customer_name, 0, 1)); ?>

                            </span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="text-left">
                        <div class="font-semibold text-gray-900"><?php echo e($testimonial->customer_name); ?></div>
                        <?php if($testimonial->company_name): ?>
                            <div class="text-sm text-gray-600"><?php echo e($testimonial->company_name); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if($showRatings && $testimonial->rating): ?>
                    <div class="flex items-center justify-center space-x-1 mb-2">
                        <?php for($i = 1; $i <= 5; $i++): ?>
                            <svg class="w-5 h-5 <?php echo e($i <= $testimonial->rating ? 'text-yellow-400' : 'text-gray-300'); ?>" 
                                 fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        <?php endfor; ?>
                        <span class="ml-2 text-sm text-gray-600">(<?php echo e($testimonial->rating); ?>/5)</span>
                    </div>
                <?php endif; ?>
                
                <?php if($testimonial->service_provided): ?>
                    <div class="text-sm text-blue-600 bg-blue-50 px-3 py-1 rounded-full inline-block">
                        <?php echo e($testimonial->service_provided); ?>

                    </div>
                <?php endif; ?>
                
                <?php if($testimonial->is_featured): ?>
                    <div class="mt-2">
                        <span class="bg-yellow-100 text-yellow-800 text-xs font-semibold px-2 py-1 rounded-full">
                            ⭐ Featured Review
                        </span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <!-- Navigation -->
    <?php if($testimonials->count() > 1): ?>
    <div class="absolute inset-y-0 left-0 flex items-center">
        <button @click="previousSlide()" 
                class="ml-4 bg-white/80 hover:bg-white text-gray-800 rounded-full p-2 shadow-md transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </button>
    </div>
    
    <div class="absolute inset-y-0 right-0 flex items-center">
        <button @click="nextSlide()" 
                class="mr-4 bg-white/80 hover:bg-white text-gray-800 rounded-full p-2 shadow-md transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </button>
    </div>

    <!-- Indicators -->
    <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2">
        <div class="flex space-x-2">
            <?php $__currentLoopData = $testimonials; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $testimonial): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <button @click="activeSlide = <?php echo e($index); ?>" 
                        :class="activeSlide === <?php echo e($index); ?> ? 'bg-blue-600' : 'bg-white/60'"
                        class="w-3 h-3 rounded-full transition-colors hover:bg-blue-400">
                </button>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>

    <!-- Autoplay Controls -->
    <?php if($autoplay): ?>
    <div class="absolute top-4 right-4">
        <button @click="toggleAutoplay()" 
                :title="autoplayEnabled ? 'Pause autoplay' : 'Start autoplay'"
                class="bg-white/80 hover:bg-white text-gray-800 rounded-full p-2 shadow-md transition-colors">
            <svg x-show="autoplayEnabled" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM7 8a1 1 0 012 0v4a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v4a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <svg x-show="!autoplayEnabled" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/>
            </svg>
        </button>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<script>
function testimonialCarousel() {
    return {
        activeSlide: 0,
        totalSlides: <?php echo e($testimonials->count()); ?>,
        autoplayEnabled: false,
        interval: null,
        
        init(autoplay, intervalTime) {
            this.autoplayEnabled = autoplay;
            if (autoplay && this.totalSlides > 1) {
                this.startAutoplay(intervalTime);
            }
        },
        
        nextSlide() {
            this.activeSlide = (this.activeSlide + 1) % this.totalSlides;
        },
        
        previousSlide() {
            this.activeSlide = this.activeSlide === 0 ? this.totalSlides - 1 : this.activeSlide - 1;
        },
        
        startAutoplay(intervalTime) {
            if (this.interval) clearInterval(this.interval);
            this.interval = setInterval(() => {
                if (this.autoplayEnabled) {
                    this.nextSlide();
                }
            }, intervalTime);
        },
        
        toggleAutoplay() {
            this.autoplayEnabled = !this.autoplayEnabled;
            if (this.autoplayEnabled) {
                this.startAutoplay(<?php echo e($interval); ?>);
            } else {
                clearInterval(this.interval);
            }
        }
    }
}
</script>
<?php else: ?>
<div class="max-w-4xl mx-auto bg-gray-50 rounded-lg shadow p-8 text-center">
    <div class="text-gray-500">
        <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10m0 0V6a2 2 0 00-2-2H9a2 2 0 00-2 2v2m10 0v10a2 2 0 01-2 2H9a2 2 0 01-2-2V8m10 0H7"/>
        </svg>
        <h3 class="text-lg font-semibold text-gray-700 mb-2">No Testimonials Available</h3>
        <p class="text-gray-600">Add some published testimonials to display them in this carousel.</p>
    </div>
</div>
<?php endif; ?><?php /**PATH /mnt/d/Bina Invoicing System/resources/views/components/testimonial-carousel.blade.php ENDPATH**/ ?>