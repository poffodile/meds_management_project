<?php

namespace App\View\Components;

use Closure;
use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

class PaymentTypeModal extends Component
{
    /**
     * Create a new component instance.
     */
    public $paymentTypeModalId;
    public $modalTitle;
    public $paymentTypeformId;
    public $paymentTypeId;
    public $inputPaymentType;
    public $radioYes;
    public $radioNo;
    public $selectStatus;
    public $saveButtonId;
    public function __construct(
    string $paymentTypeModalId = 'defaultModalId',
    string $modalTitle = 'defaultTitle',
    string $paymentTypeformId = 'defaultPaymentTypeformId',
    string $paymentTypeId = 'defaultPaymentTypeId',
    string $inputPaymentType = 'defaultPaymentType',
    string $selectStatus = 'defaultStatus',
    string $radioYes = 'defaultRadioYes',
    string $radioNo = 'defaultRadioNo',
    string $saveButtonId = 'defaultSaveButtonId',)
    {
        $this->paymentTypeModalId = $paymentTypeModalId;
        $this->modalTitle = $modalTitle;
        $this->paymentTypeformId = $paymentTypeformId;
        $this->paymentTypeId = $paymentTypeId;
        $this->inputPaymentType = $inputPaymentType;
        $this->radioYes = $radioYes;
        $this->radioYes = $radioYes;
        $this->radioNo = $radioNo;
        $this->selectStatus = $selectStatus;
        $this->saveButtonId = $saveButtonId;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.payment-type-modal');
    }
}
