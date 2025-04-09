<?php

namespace App\View\Components;

use Closure;
use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

class CreditNoteAllocate extends Component
{
    /**
     * Create a new component instance.
     */
    public $allocateModalId;
    public $modalTitle;
    public $allocateformId;
    public $foreignId;
    public $allocateId;
    public $modalSubTitle;
    public $fieldsetTitle;
    public $saveButtonId;
    public $saveUrl;
    public function __construct(
        string $allocateModalId = 'defaultModalId',
        string $modalTitle = 'defaultTitle',
        string $allocateformId = 'defaultAllocateFormId',
        string $foreignId = 'defaultForeignId',
        string $allocateId = 'defaultAllocateId',
        string $modalSubTitle = 'defaultSubTitle',
        string $fieldsetTitle = 'defaultFelidsetTitle',
        string $saveButtonId = 'defaultSaveButtonId',
        string $saveUrl = 'defaultSaveUrl',
    )
    {
        $this->allocateModalId = $allocateModalId;
        $this->modalTitle = $modalTitle;
        $this->allocateformId = $allocateformId;
        $this->foreignId = $foreignId;
        $this->allocateId = $allocateId;
        $this->modalSubTitle = $modalSubTitle;
        $this->fieldsetTitle = $fieldsetTitle;
        $this->saveButtonId = $saveButtonId;
        $this->saveUrl = $saveUrl;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.credit-note-allocate');
    }
}
