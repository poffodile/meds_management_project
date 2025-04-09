<?php

namespace App\View\Components;

use Closure;
use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

class BulkInvoiceReceived extends Component
{
    /**
     * Create a new component instance.
     */
    public $bulInvoiceModalId;
    public $modalTitle;
    public $bulInvoiceformId;
    public $bulInvoiceId;
    public $saveButtonId;
    public function __construct(
        string $bulInvoiceModalId = 'defaultModalId',
        string $bulInvoiceformId = 'defaultFormId',
        string $modalTitle = 'defaultModalTitle',
        string $bulInvoiceId = 'defaultReminderId',
        string $saveButtonId = 'defaultSaveButtonId',
    )
    {
        $this->bulInvoiceModalId = $bulInvoiceModalId;
        $this->bulInvoiceformId = $bulInvoiceformId;
        $this->modalTitle = $modalTitle;
        $this->bulInvoiceId = $bulInvoiceId;
        $this->saveButtonId = $saveButtonId;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.bulk-invoice-received');
    }
}
