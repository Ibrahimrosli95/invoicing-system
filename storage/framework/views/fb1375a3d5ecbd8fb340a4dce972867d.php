<?php $__env->startSection('title', 'Audit Log Details'); ?>

<?php $__env->startSection('header'); ?>
<div class="bg-white border-b border-gray-200 px-6 py-4">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    <?php echo e(__('Audit Log Details')); ?>

                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    <?php echo e($auditLog->getEventDisplayName()); ?> on <?php echo e(class_basename($auditLog->auditable_type)); ?> #<?php echo e($auditLog->auditable_id); ?>

                </p>
            </div>
            <div class="flex space-x-3">
                <a href="<?php echo e(route('audit.index')); ?>"
                   class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm font-medium">
                    <i class="fas fa-arrow-left mr-2"></i>Back to List
                </a>
            </div>
        </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Audit Log Overview -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Audit Log Overview</h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <dl class="space-y-4">
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Event</dt>
                                            <dd class="mt-1">
                                                <span class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium
                                                    <?php echo e($auditLog->event === 'created' ? 'bg-green-100 text-green-800' : ''); ?>

                                                    <?php echo e($auditLog->event === 'updated' ? 'bg-blue-100 text-blue-800' : ''); ?>

                                                    <?php echo e($auditLog->event === 'deleted' ? 'bg-red-100 text-red-800' : ''); ?>

                                                    <?php echo e($auditLog->event === 'login' ? 'bg-indigo-100 text-indigo-800' : ''); ?>

                                                    <?php echo e(!in_array($auditLog->event, ['created', 'updated', 'deleted', 'login']) ? 'bg-gray-100 text-gray-800' : ''); ?>">
                                                    <?php echo e($auditLog->getEventDisplayName()); ?>

                                                </span>
                                            </dd>
                                        </div>

                                        <?php if($auditLog->getActionDisplayName()): ?>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Action</dt>
                                            <dd class="mt-1 text-sm text-gray-900"><?php echo e($auditLog->getActionDisplayName()); ?></dd>
                                        </div>
                                        <?php endif; ?>

                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Model</dt>
                                            <dd class="mt-1 text-sm text-gray-900">
                                                <?php echo e(class_basename($auditLog->auditable_type)); ?> #<?php echo e($auditLog->auditable_id); ?>

                                            </dd>
                                        </div>

                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Date & Time</dt>
                                            <dd class="mt-1 text-sm text-gray-900">
                                                <?php echo e($auditLog->created_at->format('F j, Y \a\t g:i A')); ?>

                                                <span class="text-gray-500">(<?php echo e($auditLog->created_at->diffForHumans()); ?>)</span>
                                            </dd>
                                        </div>
                                    </dl>
                                </div>

                                <div>
                                    <dl class="space-y-4">
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">User</dt>
                                            <dd class="mt-1">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-8 w-8">
                                                        <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                                                            <span class="text-sm font-medium text-gray-700">
                                                                <?php echo e(substr($auditLog->getUserDisplayName(), 0, 1)); ?>

                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="ml-3">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            <?php echo e($auditLog->getUserDisplayName()); ?>

                                                        </div>
                                                    </div>
                                                </div>
                                            </dd>
                                        </div>

                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">IP Address</dt>
                                            <dd class="mt-1 text-sm text-gray-900"><?php echo e($auditLog->ip_address ?: 'N/A'); ?></dd>
                                        </div>

                                        <?php if($auditLog->session_id): ?>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Session ID</dt>
                                            <dd class="mt-1 text-sm text-gray-900 font-mono"><?php echo e(substr($auditLog->session_id, 0, 8)); ?>...</dd>
                                        </div>
                                        <?php endif; ?>

                                        <?php if($auditLog->batch_id): ?>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Batch ID</dt>
                                            <dd class="mt-1 text-sm text-gray-900 font-mono"><?php echo e($auditLog->batch_id); ?></dd>
                                        </div>
                                        <?php endif; ?>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Changes Details -->
                    <?php if($auditLog->old_values || $auditLog->new_values): ?>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Changes Details</h3>

                            <?php
                                $changes = $auditLog->getChangedFields();
                            ?>

                            <?php if(count($changes) > 0): ?>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Field
                                                </th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Old Value
                                                </th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    New Value
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <?php $__currentLoopData = $changes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field => $change): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <tr>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                        <?php echo e(ucfirst(str_replace('_', ' ', $field))); ?>

                                                    </td>
                                                    <td class="px-6 py-4 text-sm text-gray-500">
                                                        <div class="max-w-xs truncate">
                                                            <?php if(is_null($change['old'])): ?>
                                                                <span class="text-gray-400 italic">null</span>
                                                            <?php elseif(is_bool($change['old'])): ?>
                                                                <span class="font-medium"><?php echo e($change['old'] ? 'true' : 'false'); ?></span>
                                                            <?php elseif(is_array($change['old'])): ?>
                                                                <span class="font-mono text-xs"><?php echo e(json_encode($change['old'])); ?></span>
                                                            <?php else: ?>
                                                                <span><?php echo e($change['old']); ?></span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-4 text-sm text-gray-900">
                                                        <div class="max-w-xs truncate">
                                                            <?php if(is_null($change['new'])): ?>
                                                                <span class="text-gray-400 italic">null</span>
                                                            <?php elseif(is_bool($change['new'])): ?>
                                                                <span class="font-medium"><?php echo e($change['new'] ? 'true' : 'false'); ?></span>
                                                            <?php elseif(is_array($change['new'])): ?>
                                                                <span class="font-mono text-xs"><?php echo e(json_encode($change['new'])); ?></span>
                                                            <?php else: ?>
                                                                <span class="font-medium"><?php echo e($change['new']); ?></span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-8">
                                    <i class="fas fa-info-circle text-gray-400 text-2xl mb-2"></i>
                                    <p class="text-gray-500">No field changes detected in this log entry.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Metadata -->
                    <?php if($auditLog->metadata): ?>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Additional Metadata</h3>

                            <div class="bg-gray-50 rounded-md p-4">
                                <pre class="text-sm text-gray-800 whitespace-pre-wrap"><?php echo e(json_encode($auditLog->metadata, JSON_PRETTY_PRINT)); ?></pre>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Request Information -->
                    <?php if($auditLog->url || $auditLog->method || $auditLog->user_agent): ?>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Request Information</h3>

                            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <?php if($auditLog->url): ?>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">URL</dt>
                                    <dd class="mt-1 text-sm text-gray-900 font-mono break-all"><?php echo e($auditLog->url); ?></dd>
                                </div>
                                <?php endif; ?>

                                <?php if($auditLog->method): ?>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">HTTP Method</dt>
                                    <dd class="mt-1">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            <?php echo e($auditLog->method === 'GET' ? 'bg-green-100 text-green-800' : ''); ?>

                                            <?php echo e($auditLog->method === 'POST' ? 'bg-blue-100 text-blue-800' : ''); ?>

                                            <?php echo e($auditLog->method === 'PUT' ? 'bg-yellow-100 text-yellow-800' : ''); ?>

                                            <?php echo e($auditLog->method === 'DELETE' ? 'bg-red-100 text-red-800' : ''); ?>

                                            <?php echo e(!in_array($auditLog->method, ['GET', 'POST', 'PUT', 'DELETE']) ? 'bg-gray-100 text-gray-800' : ''); ?>">
                                            <?php echo e($auditLog->method); ?>

                                        </span>
                                    </dd>
                                </div>
                                <?php endif; ?>

                                <?php if($auditLog->user_agent): ?>
                                <div class="md:col-span-2">
                                    <dt class="text-sm font-medium text-gray-500">User Agent</dt>
                                    <dd class="mt-1 text-sm text-gray-900 break-all"><?php echo e($auditLog->user_agent); ?></dd>
                                </div>
                                <?php endif; ?>
                            </dl>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Related Logs -->
                    <?php if($relatedLogs->count() > 0): ?>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Related Audit Logs</h3>

                            <div class="space-y-3">
                                <?php $__currentLoopData = $relatedLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $relatedLog): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="border border-gray-200 rounded-md p-3 hover:bg-gray-50">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                                <?php echo e($relatedLog->event === 'created' ? 'bg-green-100 text-green-800' : ''); ?>

                                                <?php echo e($relatedLog->event === 'updated' ? 'bg-blue-100 text-blue-800' : ''); ?>

                                                <?php echo e($relatedLog->event === 'deleted' ? 'bg-red-100 text-red-800' : ''); ?>

                                                <?php echo e(!in_array($relatedLog->event, ['created', 'updated', 'deleted']) ? 'bg-gray-100 text-gray-800' : ''); ?>">
                                                <?php echo e($relatedLog->getEventDisplayName()); ?>

                                            </span>
                                            <span class="text-xs text-gray-500">
                                                <?php echo e($relatedLog->created_at->diffForHumans()); ?>

                                            </span>
                                        </div>

                                        <div class="text-sm text-gray-900">
                                            <?php echo e($relatedLog->getUserDisplayName()); ?>

                                        </div>

                                        <?php if($relatedLog->getActionDisplayName()): ?>
                                            <div class="text-xs text-gray-600">
                                                <?php echo e($relatedLog->getActionDisplayName()); ?>

                                            </div>
                                        <?php endif; ?>

                                        <div class="mt-2 flex justify-between items-center">
                                            <span class="text-xs text-gray-500">
                                                <?php echo e(class_basename($relatedLog->auditable_type)); ?> #<?php echo e($relatedLog->auditable_id); ?>

                                            </span>
                                            <a href="<?php echo e(route('audit.show', $relatedLog)); ?>"
                                               class="text-xs text-indigo-600 hover:text-indigo-900">
                                                View →
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Quick Actions -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>

                            <div class="space-y-3">
                                <?php if($auditLog->auditable): ?>
                                    <a href="#" onclick="viewModel('<?php echo e(get_class($auditLog->auditable)); ?>', <?php echo e($auditLog->auditable_id); ?>)"
                                       class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium text-center block">
                                        <i class="fas fa-eye mr-2"></i>View Model
                                    </a>
                                <?php endif; ?>

                                <button onclick="loadModelAuditHistory('<?php echo e(get_class($auditLog->auditable)); ?>', <?php echo e($auditLog->auditable_id); ?>)"
                                        class="w-full bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                    <i class="fas fa-history mr-2"></i>View Full History
                                </button>

                                <?php if($auditLog->batch_id): ?>
                                    <a href="<?php echo e(route('audit.index', ['batch_id' => $auditLog->batch_id])); ?>"
                                       class="w-full bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-md text-sm font-medium text-center block">
                                        <i class="fas fa-layer-group mr-2"></i>View Batch
                                    </a>
                                <?php endif; ?>

                                <button onclick="exportAuditLog(<?php echo e($auditLog->id); ?>)"
                                        class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                    <i class="fas fa-download mr-2"></i>Export Log
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Audit Log ID -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Audit Log ID</h3>
                            <p class="text-sm text-gray-600 font-mono"><?php echo e($auditLog->id); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
    <script>
        function viewModel(modelType, modelId) {
            // This would need to be implemented based on your routing structure
            console.log('View model:', modelType, modelId);
            // Example: window.open('/models/' + btoa(modelType) + '/' + modelId, '_blank');
        }

        function loadModelAuditHistory(modelType, modelId) {
            const url = '<?php echo e(route("audit.model")); ?>';

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                },
                body: JSON.stringify({
                    model_type: modelType,
                    model_id: modelId
                })
            })
            .then(response => response.json())
            .then(data => {
                // Display modal with audit history
                showAuditHistoryModal(data.audit_logs);
            })
            .catch(error => {
                console.error('Error loading audit history:', error);
                alert('Failed to load audit history');
            });
        }

        function showAuditHistoryModal(auditLogs) {
            // Create and show modal with audit history
            // This would need a modal implementation
            console.log('Audit history:', auditLogs);
        }

        function exportAuditLog(logId) {
            const params = new URLSearchParams({
                log_id: logId
            });

            window.location.href = '<?php echo e(route("audit.export")); ?>?' + params.toString();
        }
    </script>
    <?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /mnt/d/Bina Invoicing System/resources/views/audit/show.blade.php ENDPATH**/ ?>