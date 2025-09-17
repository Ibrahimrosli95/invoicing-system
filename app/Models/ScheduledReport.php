<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ScheduledReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'user_id',
        'name',
        'description',
        'report_type',
        'configuration',
        'frequency',
        'schedule_config',
        'send_time',
        'recipients',
        'is_active',
        'last_run_at',
        'next_run_at',
        'last_run_result',
        'run_count',
    ];

    protected $casts = [
        'configuration' => 'array',
        'schedule_config' => 'array',
        'recipients' => 'array',
        'last_run_result' => 'array',
        'is_active' => 'boolean',
        'last_run_at' => 'datetime',
        'next_run_at' => 'datetime',
        'send_time' => 'datetime:H:i',
    ];

    // Relationships
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeForCompany($query)
    {
        return $query->where('company_id', auth()->user()->company_id);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDueForExecution($query)
    {
        return $query->active()
            ->where('next_run_at', '<=', now());
    }

    // Business Logic Methods
    public function calculateNextRunTime()
    {
        $now = now();
        $sendTime = Carbon::createFromFormat('H:i', $this->send_time->format('H:i'));
        
        switch ($this->frequency) {
            case 'daily':
                $next = $now->copy()->setTime($sendTime->hour, $sendTime->minute);
                if ($next <= $now) {
                    $next->addDay();
                }
                break;
                
            case 'weekly':
                $dayOfWeek = $this->schedule_config['day_of_week'] ?? 1; // Monday default
                $next = $now->copy()->next($dayOfWeek)->setTime($sendTime->hour, $sendTime->minute);
                break;
                
            case 'monthly':
                $dayOfMonth = $this->schedule_config['day_of_month'] ?? 1;
                $next = $now->copy()->day($dayOfMonth)->setTime($sendTime->hour, $sendTime->minute);
                if ($next <= $now) {
                    $next->addMonth();
                }
                break;
                
            case 'quarterly':
                $month = $this->schedule_config['month_of_quarter'] ?? 1;
                $dayOfMonth = $this->schedule_config['day_of_month'] ?? 1;
                
                $currentQuarter = ceil($now->month / 3);
                $nextQuarter = $currentQuarter + 1;
                if ($nextQuarter > 4) {
                    $nextQuarter = 1;
                    $year = $now->year + 1;
                } else {
                    $year = $now->year;
                }
                
                $quarterStartMonth = ($nextQuarter - 1) * 3 + 1;
                $targetMonth = $quarterStartMonth + $month - 1;
                
                $next = Carbon::create($year, $targetMonth, $dayOfMonth, $sendTime->hour, $sendTime->minute);
                break;
                
            case 'yearly':
                $month = $this->schedule_config['month'] ?? 1;
                $dayOfMonth = $this->schedule_config['day_of_month'] ?? 1;
                $next = $now->copy()->month($month)->day($dayOfMonth)->setTime($sendTime->hour, $sendTime->minute);
                if ($next <= $now) {
                    $next->addYear();
                }
                break;
                
            default:
                $next = $now->copy()->addDay(); // Default to daily
        }
        
        return $next;
    }

    public function updateNextRunTime()
    {
        $this->update(['next_run_at' => $this->calculateNextRunTime()]);
    }

    public function markAsExecuted($success = true, $result = null)
    {
        $this->update([
            'last_run_at' => now(),
            'run_count' => $this->run_count + 1,
            'last_run_result' => [
                'success' => $success,
                'executed_at' => now()->toISOString(),
                'result' => $result,
            ],
        ]);
        
        $this->updateNextRunTime();
    }

    public function getStatusAttribute()
    {
        if (!$this->is_active) {
            return 'inactive';
        }
        
        if ($this->next_run_at && $this->next_run_at <= now()) {
            return 'due';
        }
        
        return 'scheduled';
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'inactive' => 'gray',
            'due' => 'red',
            'scheduled' => 'green',
            default => 'gray',
        };
    }

    public function getFrequencyDisplayAttribute()
    {
        return match($this->frequency) {
            'daily' => 'Daily',
            'weekly' => 'Weekly',
            'monthly' => 'Monthly',
            'quarterly' => 'Quarterly',
            'yearly' => 'Yearly',
            default => ucfirst($this->frequency),
        };
    }

    public function getRecipientsCountAttribute()
    {
        return count($this->recipients ?? []);
    }

    public function hasValidConfiguration()
    {
        return !empty($this->configuration) && 
               !empty($this->recipients) && 
               !empty($this->frequency) && 
               !empty($this->send_time);
    }
}