<?php $__env->startSection('title', 'Audit Trail'); ?>

<?php $__env->startSection('header'); ?>
<div class="bg-white border-b border-gray-200 px-6 py-4">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <?php echo e(__('Audit Trail')); ?>

            </h2>
            <div class="flex space-x-3">
                <a href="<?php echo e(route('audit.dashboard')); ?>"
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    <i class="fas fa-chart-bar mr-2"></i>Dashboard
                </a>
                <button onclick="exportAuditLogs()"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    <i class="fas fa-download mr-2"></i>Export CSV
                </button>
            </div>
        </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filter Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Filter Audit Logs</h3>

                    <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Date Range -->
                        <div>
                            <label for="date_from" class="block text-sm font-medium text-gray-700">From Date</label>
                            <input type="date" name="date_from" id="date_from"
                                   value="<?php echo e(request('date_from')); ?>"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>

                        <div>
                            <label for="date_to" class="block text-sm font-medium text-gray-700">To Date</label>
                            <input type="date" name="date_to" id="date_to"
                                   value="<?php echo e(request('date_to')); ?>"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>

                        <!-- User Filter -->
                        <div>
                            <label for="user_id" class="block text-sm font-medium text-gray-700">User</label>
                            <select name="user_id" id="user_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">All Users</option>
                                <?php $__currentLoopData = $filterOptions['users']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($user->id); ?>" <?php echo e(request('user_id') == $user->id ? 'selected' : ''); ?>>
                                        <?php echo e($user->name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <!-- Event Filter -->
                        <div>
                            <label for="event" class="block text-sm font-medium text-gray-700">Event</label>
                            <select name="event" id="event"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">All Events</option>
                                <?php $__currentLoopData = $filterOptions['events']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($value); ?>" <?php echo e(request('event') == $value ? 'selected' : ''); ?>>
                                        <?php echo e($label); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <!-- Action Filter -->
                        <div>
                            <label for="action" class="block text-sm font-medium text-gray-700">Action</label>
                            <select name="action" id="action"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">All Actions</option>
                                <?php $__currentLoopData = $filterOptions['actions']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($value); ?>" <?php echo e(request('action') == $value ? 'selected' : ''); ?>>
                                        <?php echo e($label); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <!-- Model Type Filter -->
                        <div>
                            <label for="model_type" class="block text-sm font-medium text-gray-700">Model Type</label>
                            <select name="model_type" id="model_type"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">All Models</option>
                                <?php $__currentLoopData = $filterOptions['model_types']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $modelType): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($modelType['value']); ?>" <?php echo e(request('model_type') == $modelType['value'] ? 'selected' : ''); ?>>
                                        <?php echo e($modelType['label']); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <!-- Search -->
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                            <input type="text" name="search" id="search"
                                   value="<?php echo e(request('search')); ?>"
                                   placeholder="Search users, models, events..."
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>

                        <!-- Filter Actions -->
                        <div class="flex items-end space-x-2">
                            <button type="submit"
                                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-filter mr-2"></i>Filter
                            </button>
                            <a href="<?php echo e(route('audit.index')); ?>"
                               class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-times mr-2"></i>Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-list-alt text-gray-400 text-2xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Logs</dt>
                                    <dd class="text-lg font-medium text-gray-900"><?php echo e(number_format($stats['total_logs'])); ?></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-users text-gray-400 text-2xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Active Users</dt>
                                    <dd class="text-lg font-medium text-gray-900"><?php echo e($stats['total_users']); ?></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-database text-gray-400 text-2xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Models</dt>
                                    <dd class="text-lg font-medium text-gray-900"><?php echo e($stats['total_models']); ?></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-calendar text-gray-400 text-2xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Date Range</dt>
                                    <dd class="text-sm font-medium text-gray-900">
                                        <?php if($stats['date_range']['from'] && $stats['date_range']['to']): ?>
                                            <?php echo e(\Carbon\Carbon::parse($stats['date_range']['from'])->format('M j')); ?> -
                                            <?php echo e(\Carbon\Carbon::parse($stats['date_range']['to'])->format('M j, Y')); ?>

                                        <?php else: ?>
                                            No data
                                        <?php endif; ?>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Audit Logs Table -->
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        Audit Log Entries
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">
                        Complete audit trail of all system activities and changes.
                    </p>
                </div>

                <?php if($auditLogs->count() > 0): ?>
                    <!-- Desktop Table -->
                    <div class="hidden md:block">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Date/Time
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        User
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Event
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Model
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Changes
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        IP Address
                                    </th>
                                    <th class="relative px-6 py-3">
                                        <span class="sr-only">Actions</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php $__currentLoopData = $auditLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <div><?php echo e($log->created_at->format('M j, Y')); ?></div>
                                            <div class="text-gray-500"><?php echo e($log->created_at->format('g:i A')); ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-8 w-8">
                                                    <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                                                        <span class="text-sm font-medium text-gray-700">
                                                            <?php echo e(substr($log->getUserDisplayName(), 0, 1)); ?>

                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="ml-3">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        <?php echo e($log->getUserDisplayName()); ?>

                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex flex-col">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    <?php echo e($log->event === 'created' ? 'bg-green-100 text-green-800' : ''); ?>

                                                    <?php echo e($log->event === 'updated' ? 'bg-blue-100 text-blue-800' : ''); ?>

                                                    <?php echo e($log->event === 'deleted' ? 'bg-red-100 text-red-800' : ''); ?>

                                                    <?php echo e($log->event === 'login' ? 'bg-indigo-100 text-indigo-800' : ''); ?>

                                                    <?php echo e(!in_array($log->event, ['created', 'updated', 'deleted', 'login']) ? 'bg-gray-100 text-gray-800' : ''); ?>">
                                                    <?php echo e($log->getEventDisplayName()); ?>

                                                </span>
                                                <?php if($log->getActionDisplayName()): ?>
                                                    <span class="text-xs text-gray-500 mt-1"><?php echo e($log->getActionDisplayName()); ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <div><?php echo e(class_basename($log->auditable_type)); ?></div>
                                            <div class="text-gray-500">#<?php echo e($log->auditable_id); ?></div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500 max-w-xs">
                                            <div class="truncate">
                                                <?php echo e($log->getChangesSummary() ?: 'No changes recorded'); ?>

                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo e($log->ip_address ?: 'N/A'); ?>

                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="<?php echo e(route('audit.show', $log)); ?>"
                                               class="text-indigo-600 hover:text-indigo-900">
                                                View Details
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Cards -->
                    <div class="md:hidden">
                        <div class="space-y-4 p-4">
                            <?php $__currentLoopData = $auditLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="bg-white border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center space-x-2">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                <?php echo e($log->event === 'created' ? 'bg-green-100 text-green-800' : ''); ?>

                                                <?php echo e($log->event === 'updated' ? 'bg-blue-100 text-blue-800' : ''); ?>

                                                <?php echo e($log->event === 'deleted' ? 'bg-red-100 text-red-800' : ''); ?>

                                                <?php echo e($log->event === 'login' ? 'bg-indigo-100 text-indigo-800' : ''); ?>

                                                <?php echo e(!in_array($log->event, ['created', 'updated', 'deleted', 'login']) ? 'bg-gray-100 text-gray-800' : ''); ?>">
                                                <?php echo e($log->getEventDisplayName()); ?>

                                            </span>
                                            <?php if($log->getActionDisplayName()): ?>
                                                <span class="text-xs text-gray-500"><?php echo e($log->getActionDisplayName()); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <span class="text-xs text-gray-500"><?php echo e($log->created_at->diffForHumans()); ?></span>
                                    </div>

                                    <div class="text-sm text-gray-900 mb-2">
                                        <strong><?php echo e($log->getUserDisplayName()); ?></strong> modified
                                        <strong><?php echo e(class_basename($log->auditable_type)); ?> #<?php echo e($log->auditable_id); ?></strong>
                                    </div>

                                    <?php if($log->getChangesSummary()): ?>
                                        <div class="text-sm text-gray-600 mb-2">
                                            Changes: <?php echo e($log->getChangesSummary()); ?>

                                        </div>
                                    <?php endif; ?>

                                    <div class="flex justify-between items-center text-xs text-gray-500">
                                        <span>IP: <?php echo e($log->ip_address ?: 'N/A'); ?></span>
                                        <a href="<?php echo e(route('audit.show', $log)); ?>"
                                           class="text-indigo-600 hover:text-indigo-900 font-medium">
                                            View Details →
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                        <?php echo e($auditLogs->links()); ?>

                    </div>
                <?php else: ?>
                    <div class="text-center py-12">
                        <i class="fas fa-search text-gray-400 text-4xl mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No audit logs found</h3>
                        <p class="text-gray-500">Try adjusting your filters to see more results.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
    <script>
        function exportAuditLogs() {
            // Get current filter parameters
            const params = new URLSearchParams(window.location.search);
            const exportUrl = '<?php echo e(route("audit.export")); ?>?' + params.toString();

            // Download the export
            window.location.href = exportUrl;
        }
    </script>
    <?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /mnt/d/Bina Invoicing System/resources/views/audit/index.blade.php ENDPATH**/ ?>