<?php

namespace App\View\Components;

use App\Models\Testimonial;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class TestimonialCarousel extends Component
{
    public $testimonials;
    public $showRatings;
    public $autoplay;
    public $interval;
    public $limit;

    /**
     * Create a new component instance.
     */
    public function __construct(
        bool $showRatings = true, 
        bool $autoplay = true, 
        int $interval = 5000, 
        int $limit = 10
    ) {
        $this->showRatings = $showRatings;
        $this->autoplay = $autoplay;
        $this->interval = $interval;
        $this->limit = $limit;
        
        $this->testimonials = $this->getTestimonials();
    }

    /**
     * Get featured testimonials for the carousel
     */
    private function getTestimonials()
    {
        $companyId = auth()->user()?->company_id;
        
        if (!$companyId) {
            return collect();
        }

        return Testimonial::where('company_id', $companyId)
            ->where('status', 'published')
            ->orderBy('is_featured', 'desc')
            ->orderBy('rating', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit($this->limit)
            ->get();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.testimonial-carousel');
    }
}
