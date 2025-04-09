<?php

namespace App\View\Components;

use Closure;
use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

class BulkRecordPayment extends Component
{
    /**
     * Create a new component instance.
     */
    public $bulkRecordPaymentModalId;
    public $modalTitle;
    public $bulkRecordPaymentformId;
    public $bulkRecordPaymentId;
    public $saveButtonId;
    public function __construct(
        string $bulkRecordPaymentModalId = 'defaultModalId',
        string $bulkRecordPaymentformId = 'defaultFormId',
        string $modalTitle = 'defaultModalTitle',
        string $bulkRecordPaymentId = 'defaultReminderId',
        string $saveButtonId = 'defaultSaveButtonId',
    )
    {
        $this->bulkRecordPaymentModalId = $bulkRecordPaymentModalId;
        $this->bulkRecordPaymentformId = $bulkRecordPaymentformId;
        $this->modalTitle = $modalTitle;
        $this->bulkRecordPaymentId = $bulkRecordPaymentId;
        $this->saveButtonId = $saveButtonId;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.bulk-record-payment');
    }
}
