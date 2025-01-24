<?php

namespace App\View\Components;

use Closure;
use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

class VatTaxRate extends Component
{
    /**
     * Create a new component instance.
     */
    public $modalId;
    public $modalTitle;
    public $formId;
    public $id;
    public $name;
    public $taxRate;
    public $taxCode;
    public $expDate;
    public $status;
    public $saveButtonId;
    public function __construct(
        string $modalId = 'defaultModalId',
        string $modalTitle = 'Add Attachment',
        string $formId = 'defaultFormId',
        string $id = 'defaultId',
        string $name = 'defaultName',
        string $taxRate = 'defaultTaxRate',
        string $taxCode = 'defaultTaxCode',
        string $expDate = 'defaultExpDate',
        string $status = 'defaultStatus',
        string $saveButtonId = 'defaultSaveButtonId',)
    {
        $this->modalId = $modalId;
        $this->modalTitle = $modalTitle;
        $this->formId = $formId;
        $this->id = $id;
        $this->name = $name;
        $this->taxRate = $taxRate;
        $this->taxCode = $taxCode;
        $this->expDate = $expDate;
        $this->status = $status;
        $this->saveButtonId = $saveButtonId;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.vat-tax-rate');
    }
}
