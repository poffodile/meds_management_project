<?php

namespace App\View\Components;

use Closure;
use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

class AddInvoiceModal extends Component
{
    /**
     * Create a new component instance.
     */
    public $invoiceModalId;
    public $modalTitle;
    public $invoiceformId;
    public $invoiceId;
    public $purchaseOrder;
    public $inputInvoiceSupplierName;
    public $inputInvoiceRef;
    public $invoiceNetAmount;
    public $invoiceVatId;
    public $invoiceVatAmount;
    public $invoiceGrossAmount;
    public $invoiceDate;
    public $invoiceDueDate;
    public $invoiceNotes;
    public $invoiceAttachemnt;
    public $saveButtonId;
    public function __construct(
    string $invoiceModalId = 'defaultModalId',
    string $modalTitle = 'defaultTitle',
    string $invoiceformId = 'defaultInvoiceFormId',
    string $invoiceId = 'defaultInvoiceId',
    string $purchaseOrder = 'defaultPurchaseOrder',
    string $inputInvoiceSupplierName = 'defaultInvoiceSupplierName',
    string $inputInvoiceRef = 'defaultInvoiceRef',
    string $invoiceNetAmount = 'defaultNetAmount',
    string $invoiceVatId = 'defaultVatId',
    string $invoiceVatAmount = 'defaultVatAmount',
    string $invoiceGrossAmount = 'defaultGrossAmount',
    string $invoiceDate = 'defaultDate',
    string $invoiceDueDate = 'defaultDueDate',
    string $invoiceNotes = 'defaultNotes',
    string $invoiceAttachemnt = 'defaultAttachemnt',
    string $saveButtonId = 'defaultSaveButtonId',)
    {
        $this->invoiceModalId = $invoiceModalId;
        $this->modalTitle = $modalTitle;
        $this->invoiceformId = $invoiceformId;
        $this->invoiceId = $invoiceId;
        $this->purchaseOrder = $purchaseOrder;
        $this->inputInvoiceSupplierName = $inputInvoiceSupplierName;
        $this->inputInvoiceRef = $inputInvoiceRef;
        $this->invoiceNetAmount = $invoiceNetAmount;
        $this->invoiceVatId = $invoiceVatId;
        $this->invoiceVatAmount = $invoiceVatAmount;
        $this->invoiceVatAmount = $invoiceVatAmount;
        $this->invoiceGrossAmount = $invoiceGrossAmount;
        $this->invoiceGrossAmount = $invoiceGrossAmount;
        $this->invoiceDate = $invoiceDate;
        $this->invoiceDate = $invoiceDate;
        $this->invoiceDueDate = $invoiceDueDate;
        $this->invoiceDueDate = $invoiceDueDate;
        $this->invoiceNotes = $invoiceNotes;
        $this->invoiceNotes = $invoiceNotes;
        $this->invoiceAttachemnt = $invoiceAttachemnt;
        $this->saveButtonId = $saveButtonId;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.add-invoice-modal');
    }
}
