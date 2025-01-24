<?php

namespace App\View\Components;

use Closure;
use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

class PurchaseOrderReject extends Component
{
    /**
     * Create a new component instance.
     */
    public $rejectModalId;
    public $modalTitle;
    public $rejectformId;
    public $rejectId;
    public $inputRejectMessage;
    public $radioYes;
    public $radioNo;
    public $rejectNotifyWho;
    public $rejectNotification;
    public $rejectSms;
    public $rejectEmail;
    public $saveButtonId;
    public function __construct(
    string $rejectModalId = 'defaultModalId',
    string $modalTitle = 'defaultTitle',
    string $rejectformId = 'defaultRejectFormId',
    string $rejectId = 'defaultRejectId',
    string $inputRejectMessage = 'defaultRejectMessage',
    string $rejectNotifyWho = 'defaultRejectNotifyWho',
    string $rejectNotification = 'defaultRejectNotification',
    string $rejectSms = 'defaultRejectSms',
    string $rejectEmail = 'defaultRejectEmail',
    string $radioYes = 'defaultRadioYes',
    string $radioNo = 'defaultRadioNo',
    string $saveButtonId = 'defaultSaveButtonId',
    )
    {
        $this->rejectModalId = $rejectModalId;
        $this->modalTitle = $modalTitle;
        $this->rejectformId = $rejectformId;
        $this->rejectId = $rejectId;
        $this->inputRejectMessage = $inputRejectMessage;
        $this->radioYes = $radioYes;
        $this->radioYes = $radioYes;
        $this->radioNo = $radioNo;
        $this->rejectNotifyWho = $rejectNotifyWho;
        $this->rejectNotifyWho = $rejectNotifyWho;
        $this->rejectNotification = $rejectNotification;
        $this->rejectNotification = $rejectNotification;
        $this->rejectSms = $rejectSms;
        $this->rejectSms = $rejectSms;
        $this->rejectEmail = $rejectEmail;
        $this->saveButtonId = $saveButtonId;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.purchase-order-reject');
    }
}
