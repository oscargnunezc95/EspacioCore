<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class AppLayout extends Component
{
    public function __construct(
        public ?string $metaTitle = null,
        public ?string $metaDescription = null,
        public ?string $canonicalUrl = null,
        public ?string $ogType = 'website',
        public string $metaRobots = 'index, follow',
    ) {}

    public function render(): View
    {
        return view('layouts.app');
    }
}
