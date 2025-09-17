<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $content['subject'] ?? 'Notification' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #374151;
            background-color: #f9fafb;
        }
        
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .email-header {
            background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%);
            color: white;
            padding: 30px 40px;
            text-align: center;
        }
        
        .company-logo {
            max-height: 50px;
            margin-bottom: 15px;
        }
        
        .company-name {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .company-tagline {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .email-content {
            padding: 40px;
        }
        
        .notification-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }
        
        .icon-success { background-color: #10B981; }
        .icon-warning { background-color: #F59E0B; }
        .icon-info { background-color: #3B82F6; }
        .icon-error { background-color: #EF4444; }
        
        .notification-title {
            font-size: 20px;
            font-weight: 600;
            text-align: center;
            margin-bottom: 15px;
            color: #1F2937;
        }
        
        .notification-message {
            font-size: 16px;
            margin-bottom: 25px;
            text-align: center;
            color: #6B7280;
        }
        
        .notification-details {
            background-color: #F9FAFB;
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
            border-left: 4px solid #2563EB;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            padding: 5px 0;
        }
        
        .detail-label {
            font-weight: 600;
            color: #4B5563;
            min-width: 120px;
        }
        
        .detail-value {
            color: #1F2937;
            flex: 1;
            text-align: right;
        }
        
        .action-button {
            display: inline-block;
            background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%);
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            text-align: center;
            margin: 20px auto;
            display: block;
            width: fit-content;
            transition: all 0.2s ease;
        }
        
        .action-button:hover {
            background: linear-gradient(135deg, #1D4ED8 0%, #1E40AF 100%);
            transform: translateY(-1px);
        }
        
        .secondary-actions {
            text-align: center;
            margin-top: 20px;
        }
        
        .secondary-link {
            color: #6B7280;
            text-decoration: none;
            font-size: 14px;
            margin: 0 10px;
        }
        
        .secondary-link:hover {
            color: #2563EB;
        }
        
        .email-footer {
            background-color: #F9FAFB;
            padding: 30px 40px;
            border-top: 1px solid #E5E7EB;
            text-align: center;
            color: #6B7280;
            font-size: 14px;
        }
        
        .company-contact {
            margin-bottom: 15px;
        }
        
        .contact-info {
            margin-bottom: 8px;
        }
        
        .unsubscribe {
            font-size: 12px;
            color: #9CA3AF;
            margin-top: 20px;
        }
        
        .unsubscribe a {
            color: #6B7280;
            text-decoration: none;
        }
        
        .unsubscribe a:hover {
            color: #2563EB;
        }
        
        /* Mobile responsiveness */
        @media (max-width: 600px) {
            .email-container {
                margin: 0;
                box-shadow: none;
            }
            
            .email-header,
            .email-content,
            .email-footer {
                padding: 20px;
            }
            
            .company-name {
                font-size: 20px;
            }
            
            .notification-title {
                font-size: 18px;
            }
            
            .detail-row {
                flex-direction: column;
            }
            
            .detail-value {
                text-align: left;
                margin-top: 2px;
            }
            
            .action-button {
                display: block;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            @if($company->logo_path)
                <img src="{{ asset('storage/' . $company->logo_path) }}" alt="{{ $company->name }}" class="company-logo">
            @endif
            <div class="company-name">{{ $company->name ?? 'Bina Group' }}</div>
            @if($company->tagline)
                <div class="company-tagline">{{ $company->tagline }}</div>
            @endif
        </div>

        <!-- Content -->
        <div class="email-content">
            <!-- Notification Icon -->
            <div class="notification-icon icon-{{ $content['type'] ?? 'info' }}">
                @switch($content['type'] ?? 'info')
                    @case('success')
                        ✓
                        @break
                    @case('warning')
                        ⚠
                        @break
                    @case('error')
                        ✕
                        @break
                    @default
                        ℹ
                @endswitch
            </div>

            <!-- Title and Message -->
            <h1 class="notification-title">{{ $content['title'] }}</h1>
            <p class="notification-message">{{ $content['message'] }}</p>

            <!-- Details (if provided) -->
            @if(isset($content['details']) && is_array($content['details']))
                <div class="notification-details">
                    @foreach($content['details'] as $label => $value)
                        <div class="detail-row">
                            <span class="detail-label">{{ $label }}:</span>
                            <span class="detail-value">{{ $value }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Action Button (if provided) -->
            @if(isset($content['action_url']) && isset($content['action_text']))
                <a href="{{ $content['action_url'] }}" class="action-button">
                    {{ $content['action_text'] }}
                </a>
            @endif

            <!-- Secondary Actions (if provided) -->
            @if(isset($content['secondary_actions']) && is_array($content['secondary_actions']))
                <div class="secondary-actions">
                    @foreach($content['secondary_actions'] as $action)
                        <a href="{{ $action['url'] }}" class="secondary-link">{{ $action['text'] }}</a>
                    @endforeach
                </div>
            @endif

            <!-- Additional Content (if provided) -->
            @if(isset($content['body']))
                <div style="margin-top: 30px;">
                    {!! $content['body'] !!}
                </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <div class="company-contact">
                <div class="contact-info">
                    <strong>{{ $company->name ?? 'Bina Group' }}</strong>
                </div>
                @if($company->address)
                    <div class="contact-info">{{ $company->address }}</div>
                @endif
                <div class="contact-info">
                    @if($company->phone)
                        Phone: {{ $company->phone }}
                    @endif
                    @if($company->email)
                        • Email: {{ $company->email }}
                    @endif
                </div>
                @if($company->website)
                    <div class="contact-info">
                        <a href="{{ $company->website }}" style="color: #6B7280;">{{ $company->website }}</a>
                    </div>
                @endif
            </div>

            <div class="unsubscribe">
                <p>
                    You received this notification because you're subscribed to updates from {{ $company->name ?? 'our system' }}.
                    <br>
                    <a href="{{ route('profile.edit') }}#notifications">Manage notification preferences</a>
                    •
                    <a href="#">Unsubscribe from all notifications</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>