<?php

namespace App\View\Components;

use Closure;
use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

class purchaseSearchModal extends Component
{
    /**
     * Create a new component instance.
     */
    public $searchModalId;
    public $modalTitle;
    public $searchformId;
    public function __construct(
        string $searchModalId = 'defaultModalId',
        string $modalTitle = 'defaultTitle',
        string $searchformId = 'defaultFormId',
    )
    {
        $this->searchModalId = $searchModalId;
        $this->modalTitle = $modalTitle;
        $this->searchformId = $searchformId;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.purchase-search-modal');
    }
}
