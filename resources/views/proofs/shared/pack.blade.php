<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} - Proof Pack</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .proof-card:hover { transform: translateY(-2px); }
        .transition-transform { transition: transform 0.2s ease-in-out; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $title }}</h1>
                    @if($description)
                        <p class="text-gray-600 mt-1">{{ $description }}</p>
                    @endif
                    @if($recipient_name)
                        <p class="text-sm text-blue-600 mt-2">Shared with {{ $recipient_name }}</p>
                    @endif
                </div>
                
                <div class="flex items-center space-x-4">
                    <div class="text-sm text-gray-500">
                        <div>{{ $proofs->count() }} proof{{ $proofs->count() !== 1 ? 's' : '' }}</div>
                        <div>Expires {{ $expires_at->diffForHumans() }}</div>
                    </div>
                    
                    <!-- Download Button -->
                    <a href="{{ route('proofs.shared-pack.download', ['token' => request()->route('token')]) }}" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Download PDF
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Proof Pack Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" x-data="{ selectedProof: null }">
            @foreach($proofs as $proof)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden proof-card transition-transform hover:shadow-md"
                 @click="selectedProof = selectedProof === '{{ $proof->uuid }}' ? null : '{{ $proof->uuid }}'">
                
                <!-- Proof Header -->
                <div class="p-6 border-b border-gray-100">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="font-semibold text-gray-900 text-lg">{{ $proof->title }}</h3>
                            @if($proof->description)
                                <p class="text-gray-600 text-sm mt-1">{{ $proof->description }}</p>
                            @endif
                        </div>
                        
                        <!-- Proof Type Badge -->
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                                   {{ $proof->getTypeBadgeColor() }}">
                            {{ $proof->getTypeDisplayName() }}
                        </span>
                    </div>
                    
                    <!-- Meta Info -->
                    <div class="flex items-center justify-between mt-4 text-sm text-gray-500">
                        <span>{{ $proof->assets->count() }} asset{{ $proof->assets->count() !== 1 ? 's' : '' }}</span>
                        <span>Impact: {{ $proof->impact_score }}/100</span>
                    </div>
                </div>
                
                <!-- Assets Preview -->
                @if($proof->assets->isNotEmpty())
                    <div class="p-4" x-show="selectedProof === '{{ $proof->uuid }}'" x-transition>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            @foreach($proof->assets->take(6) as $asset)
                                <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden group cursor-pointer">
                                    @if($asset->file_type === 'image' && $asset->thumbnail_path)
                                        <img src="{{ Storage::url($asset->thumbnail_path) }}" 
                                             alt="{{ $asset->title }}"
                                             class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                                    @elseif($asset->file_type === 'video')
                                        <div class="w-full h-full flex items-center justify-center bg-gray-200">
                                            <svg class="w-8 h-8 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                    @else
                                        <div class="w-full h-full flex items-center justify-center bg-gray-200">
                                            <svg class="w-8 h-8 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                            
                            @if($proof->assets->count() > 6)
                                <div class="aspect-square bg-gray-100 rounded-lg flex items-center justify-center">
                                    <span class="text-sm font-medium text-gray-600">
                                        +{{ $proof->assets->count() - 6 }} more
                                    </span>
                                </div>
                            @endif
                        </div>
                        
                        @if($proof->assets->count() > 0)
                            <div class="mt-4 text-center">
                                <button @click="selectedProof = null" 
                                        class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                    Collapse Assets
                                </button>
                            </div>
                        @endif
                    </div>
                @endif
                
                <!-- Expand/Collapse Button -->
                @if($proof->assets->isNotEmpty())
                    <div class="px-6 py-3 bg-gray-50 border-t border-gray-100">
                        <button class="w-full text-sm font-medium text-gray-600 hover:text-gray-800 flex items-center justify-center"
                                x-show="selectedProof !== '{{ $proof->uuid }}'">
                            <span>View Assets</span>
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                    </div>
                @endif
            </div>
            @endforeach
        </div>
        
        @if($proofs->isEmpty())
            <div class="text-center py-12">
                <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No Proofs Available</h3>
                <p class="text-gray-600">The proof pack you're looking for is not available or has been removed.</p>
            </div>
        @endif
    </div>

    <!-- Footer -->
    <div class="bg-white border-t mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between text-sm text-gray-500">
                <div>
                    <p>Shared on {{ $created_at->format('M j, Y \a\t g:i A') }}</p>
                    <p class="mt-1">This link expires {{ $expires_at->diffForHumans() }}</p>
                </div>
                
                <div class="text-right">
                    <p class="font-medium text-gray-700">Powered by Bina Invoicing System</p>
                    <p class="mt-1">Professional proof management</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Warning Notice -->
    @if($expires_at->diffInHours() < 24)
        <div class="fixed bottom-4 right-4 bg-yellow-100 border border-yellow-300 rounded-lg p-4 shadow-lg max-w-sm">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-yellow-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <p class="text-sm font-medium text-yellow-800">Link expiring soon</p>
                    <p class="text-xs text-yellow-700">Download now - expires {{ $expires_at->diffForHumans() }}</p>
                </div>
            </div>
        </div>
    @endif
</body>
</html>