<?php

namespace Modules\Frontend\View\Components\Section;

use Illuminate\View\Component;
use Illuminate\View\View;

class ClinicSection extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view/contents that represent the component.
     */
    public function render(): View|string
    {
        return view('frontend::components.section/clinic_section');
    }
}
