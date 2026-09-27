<?php

namespace Modules\Frontend\View\Components\Card;

use Illuminate\View\Component;
use Illuminate\View\View;

class CategoryCard extends Component
{
    /**
     * Create a new component instance.
     */
    public $category;
    public function __construct($category)
    {
        $this->category = $category;
    }

    /**
     * Get the view/contents that represent the component.
     */
    public function render(): View|string
    {
        return view('frontend::components.card/category_card');
    }
}
