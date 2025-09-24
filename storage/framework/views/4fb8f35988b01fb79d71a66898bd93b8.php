<?php $__env->startSection('title', 'Create Assessment'); ?>

<?php $__env->startSection('header'); ?>
<div class="bg-white border-b border-gray-200 px-6 py-4">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <?php echo e(__('Create Assessment')); ?>

        </h2>
        <div class="mt-2 sm:mt-0">
            <a href="<?php echo e(route('assessments.index')); ?>" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to List
            </a>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
     <?php $__env->endSlot(); ?>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <form action="<?php echo e(route('assessments.store')); ?>" method="POST" class="space-y-6" x-data="assessmentForm()">
                <?php echo csrf_field(); ?>

                <!-- Assessment Type & Service -->
                <div class="bg-white shadow-sm sm:rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Assessment Type</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <label for="service_type" class="block text-sm font-medium text-gray-700">Service Type *</label>
                                <select name="service_type" id="service_type" x-model="formData.service_type" @change="loadServiceTemplates()" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                    <option value="">Select Service Type</option>
                                    <option value="waterproofing">Waterproofing</option>
                                    <option value="painting">Painting Works</option>
                                    <option value="sports_court">Sports Court Flooring</option>
                                    <option value="industrial">Industrial Flooring</option>
                                </select>
                                <?php $__errorArgs = ['service_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div>
                                <label for="property_type" class="block text-sm font-medium text-gray-700">Property Type *</label>
                                <select name="property_type" id="property_type" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                    <option value="">Select Property Type</option>
                                    <option value="residential">Residential</option>
                                    <option value="commercial">Commercial</option>
                                    <option value="industrial">Industrial</option>
                                    <option value="institutional">Institutional</option>
                                </select>
                                <?php $__errorArgs = ['property_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div>
                                <label for="urgency_level" class="block text-sm font-medium text-gray-700">Urgency Level *</label>
                                <select name="urgency_level" id="urgency_level" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                    <option value="medium">Medium</option>
                                    <option value="low">Low</option>
                                    <option value="high">High</option>
                                    <option value="emergency">Emergency</option>
                                </select>
                                <?php $__errorArgs = ['urgency_level'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div x-show="templates.length > 0">
                                <label for="service_template_id" class="block text-sm font-medium text-gray-700">Assessment Template</label>
                                <select name="service_template_id" id="service_template_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                    <option value="">Select Template (Optional)</option>
                                    <template x-for="template in templates" :key="template.id">
                                        <option :value="template.id" x-text="template.name"></option>
                                    </template>
                                </select>
                                <?php $__errorArgs = ['service_template_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Client Information -->
                <div class="bg-white shadow-sm sm:rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Client Information</h3>

                        <?php if(request('lead_id')): ?>
                        <input type="hidden" name="lead_id" value="<?php echo e(request('lead_id')); ?>">
                        <div class="mb-4 p-3 bg-blue-50 rounded-md">
                            <p class="text-sm text-blue-700">
                                <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Creating assessment from existing lead. Client information will be pre-filled.
                            </p>
                        </div>
                        <?php endif; ?>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <label for="client_name" class="block text-sm font-medium text-gray-700">Client Name *</label>
                                <input type="text" name="client_name" id="client_name" required value="<?php echo e(old('client_name', $lead->customer_name ?? '')); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                <?php $__errorArgs = ['client_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div>
                                <label for="client_phone" class="block text-sm font-medium text-gray-700">Phone Number *</label>
                                <input type="tel" name="client_phone" id="client_phone" required value="<?php echo e(old('client_phone', $lead->phone ?? '')); ?>" placeholder="012-345-6789" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                <?php $__errorArgs = ['client_phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="sm:col-span-2">
                                <label for="client_email" class="block text-sm font-medium text-gray-700">Email Address</label>
                                <input type="email" name="client_email" id="client_email" value="<?php echo e(old('client_email', $lead->email ?? '')); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                <?php $__errorArgs = ['client_email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Location Information -->
                <div class="bg-white shadow-sm sm:rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Location Details</h3>
                        <div class="space-y-6">
                            <div>
                                <label for="location_address" class="block text-sm font-medium text-gray-700">Address *</label>
                                <textarea name="location_address" id="location_address" rows="2" required placeholder="Full address including unit/floor number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"><?php echo e(old('location_address', $lead->address ?? '')); ?></textarea>
                                <?php $__errorArgs = ['location_address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                                <div>
                                    <label for="location_city" class="block text-sm font-medium text-gray-700">City *</label>
                                    <input type="text" name="location_city" id="location_city" required value="<?php echo e(old('location_city')); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                    <?php $__errorArgs = ['location_city'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>

                                <div>
                                    <label for="location_state" class="block text-sm font-medium text-gray-700">State *</label>
                                    <select name="location_state" id="location_state" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                        <option value="">Select State</option>
                                        <option value="Johor" <?php echo e(old('location_state') === 'Johor' ? 'selected' : ''); ?>>Johor</option>
                                        <option value="Kedah" <?php echo e(old('location_state') === 'Kedah' ? 'selected' : ''); ?>>Kedah</option>
                                        <option value="Kelantan" <?php echo e(old('location_state') === 'Kelantan' ? 'selected' : ''); ?>>Kelantan</option>
                                        <option value="Kuala Lumpur" <?php echo e(old('location_state') === 'Kuala Lumpur' ? 'selected' : ''); ?>>Kuala Lumpur</option>
                                        <option value="Labuan" <?php echo e(old('location_state') === 'Labuan' ? 'selected' : ''); ?>>Labuan</option>
                                        <option value="Melaka" <?php echo e(old('location_state') === 'Melaka' ? 'selected' : ''); ?>>Melaka</option>
                                        <option value="Negeri Sembilan" <?php echo e(old('location_state') === 'Negeri Sembilan' ? 'selected' : ''); ?>>Negeri Sembilan</option>
                                        <option value="Pahang" <?php echo e(old('location_state') === 'Pahang' ? 'selected' : ''); ?>>Pahang</option>
                                        <option value="Penang" <?php echo e(old('location_state') === 'Penang' ? 'selected' : ''); ?>>Penang</option>
                                        <option value="Perak" <?php echo e(old('location_state') === 'Perak' ? 'selected' : ''); ?>>Perak</option>
                                        <option value="Perlis" <?php echo e(old('location_state') === 'Perlis' ? 'selected' : ''); ?>>Perlis</option>
                                        <option value="Putrajaya" <?php echo e(old('location_state') === 'Putrajaya' ? 'selected' : ''); ?>>Putrajaya</option>
                                        <option value="Sabah" <?php echo e(old('location_state') === 'Sabah' ? 'selected' : ''); ?>>Sabah</option>
                                        <option value="Sarawak" <?php echo e(old('location_state') === 'Sarawak' ? 'selected' : ''); ?>>Sarawak</option>
                                        <option value="Selangor" <?php echo e(old('location_state') === 'Selangor' ? 'selected' : ''); ?>>Selangor</option>
                                        <option value="Terengganu" <?php echo e(old('location_state') === 'Terengganu' ? 'selected' : ''); ?>>Terengganu</option>
                                    </select>
                                    <?php $__errorArgs = ['location_state'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>

                                <div>
                                    <label for="location_postal_code" class="block text-sm font-medium text-gray-700">Postal Code *</label>
                                    <input type="text" name="location_postal_code" id="location_postal_code" required value="<?php echo e(old('location_postal_code')); ?>" placeholder="12345" pattern="[0-9]{5}" maxlength="5" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                    <?php $__errorArgs = ['location_postal_code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>

                            <div>
                                <label for="location_coordinates" class="block text-sm font-medium text-gray-700">GPS Coordinates</label>
                                <input type="text" name="location_coordinates" id="location_coordinates" value="<?php echo e(old('location_coordinates')); ?>" placeholder="3.1390,101.6869" pattern="^-?[0-9]+\.?[0-9]*,-?[0-9]+\.?[0-9]*$" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                <p class="mt-1 text-sm text-gray-500">Optional: Latitude,Longitude format (e.g., 3.1390,101.6869)</p>
                                <?php $__errorArgs = ['location_coordinates'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Assessment Schedule -->
                <div class="bg-white shadow-sm sm:rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Assessment Schedule</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <label for="assessment_date" class="block text-sm font-medium text-gray-700">Assessment Date *</label>
                                <input type="date" name="assessment_date" id="assessment_date" required value="<?php echo e(old('assessment_date', now()->addDay()->format('Y-m-d'))); ?>" min="<?php echo e(now()->format('Y-m-d')); ?>" max="<?php echo e(now()->addMonths(6)->format('Y-m-d')); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                <?php $__errorArgs = ['assessment_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div>
                                <label for="estimated_duration" class="block text-sm font-medium text-gray-700">Estimated Duration (minutes) *</label>
                                <select name="estimated_duration" id="estimated_duration" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                    <option value="30" <?php echo e(old('estimated_duration') == '30' ? 'selected' : ''); ?>>30 minutes</option>
                                    <option value="60" <?php echo e(old('estimated_duration', '60') == '60' ? 'selected' : ''); ?>>1 hour</option>
                                    <option value="90" <?php echo e(old('estimated_duration') == '90' ? 'selected' : ''); ?>>1.5 hours</option>
                                    <option value="120" <?php echo e(old('estimated_duration') == '120' ? 'selected' : ''); ?>>2 hours</option>
                                    <option value="180" <?php echo e(old('estimated_duration') == '180' ? 'selected' : ''); ?>>3 hours</option>
                                    <option value="240" <?php echo e(old('estimated_duration') == '240' ? 'selected' : ''); ?>>4 hours</option>
                                    <option value="480" <?php echo e(old('estimated_duration') == '480' ? 'selected' : ''); ?>>8 hours</option>
                                </select>
                                <?php $__errorArgs = ['estimated_duration'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Area Information -->
                <div class="bg-white shadow-sm sm:rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Area Information</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <label for="total_area" class="block text-sm font-medium text-gray-700">Total Area</label>
                                <input type="number" name="total_area" id="total_area" value="<?php echo e(old('total_area')); ?>" step="0.01" min="0.1" max="999999.99" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                <?php $__errorArgs = ['total_area'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div>
                                <label for="area_unit" class="block text-sm font-medium text-gray-700">Area Unit</label>
                                <select name="area_unit" id="area_unit" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                    <option value="">Select Unit</option>
                                    <option value="sqft" <?php echo e(old('area_unit', 'sqft') === 'sqft' ? 'selected' : ''); ?>>Square Feet (sqft)</option>
                                    <option value="sqm" <?php echo e(old('area_unit') === 'sqm' ? 'selected' : ''); ?>>Square Meters (sqm)</option>
                                    <option value="acres" <?php echo e(old('area_unit') === 'acres' ? 'selected' : ''); ?>>Acres</option>
                                    <option value="hectares" <?php echo e(old('area_unit') === 'hectares' ? 'selected' : ''); ?>>Hectares</option>
                                </select>
                                <?php $__errorArgs = ['area_unit'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Details -->
                <div class="bg-white shadow-sm sm:rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Additional Details</h3>
                        <div class="space-y-6">
                            <div>
                                <label for="special_requirements" class="block text-sm font-medium text-gray-700">Special Requirements</label>
                                <textarea name="special_requirements" id="special_requirements" rows="3" placeholder="Any special requirements, equipment needs, or access considerations..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"><?php echo e(old('special_requirements')); ?></textarea>
                                <?php $__errorArgs = ['special_requirements'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div>
                                <label for="client_requirements" class="block text-sm font-medium text-gray-700">Client Requirements</label>
                                <textarea name="client_requirements" id="client_requirements" rows="3" placeholder="Specific client requirements or expectations..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"><?php echo e(old('client_requirements', $lead->requirements ?? '')); ?></textarea>
                                <?php $__errorArgs = ['client_requirements'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div>
                                <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                                <textarea name="notes" id="notes" rows="3" placeholder="Additional notes about this assessment..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"><?php echo e(old('notes')); ?></textarea>
                                <?php $__errorArgs = ['notes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-end space-x-3">
                    <a href="<?php echo e(route('assessments.index')); ?>" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Cancel
                    </a>
                    <button type="submit" name="status" value="draft" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                        Save as Draft
                    </button>
                    <button type="submit" name="status" value="scheduled" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Schedule Assessment
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function assessmentForm() {
            return {
                formData: {
                    service_type: '<?php echo e(old('service_type')); ?>'
                },
                templates: [],

                async loadServiceTemplates() {
                    if (!this.formData.service_type) {
                        this.templates = [];
                        return;
                    }

                    try {
                        const response = await fetch(`/api/service-templates?service_type=${this.formData.service_type}`);
                        const data = await response.json();
                        this.templates = data.templates || [];
                    } catch (error) {
                        console.error('Error loading templates:', error);
                        this.templates = [];
                    }
                }
            }
        }
    </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /mnt/d/Bina Invoicing System/resources/views/assessments/create.blade.php ENDPATH**/ ?>