<?php

namespace App\View\Components;

use Closure;
use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

class PurchaseOrderEmail extends Component
{
    /**
     * Create a new component instance.
     */
    public $emailModalId;
    public $modalTitle;
    public $emailformId;
    public $foreignId;
    public $emailId;
    public $toField;
    public $ccField;
    public $subject;
    public $selectBoxsubject;
    public $body;
    public $saveButtonId;
    public $saveUrl;
    public function __construct(
    string $emailModalId = 'defaultModalId',
    string $modalTitle = 'defaultTitle',
    string $emailformId = 'defaultRejectFormId',
    string $foreignId = 'defaultForeignId',
    string $emailId = 'defaultRejectId',
    string $toField = 'defaultRadioYes',
    string $ccField = 'defaultRadioNo',
    string $subject = 'defaultRadioNo',
    string $selectBox = 'defaultRadioNo',
    string $body = 'defaultRadioNo',
    string $saveButtonId = 'defaultSaveButtonId',
    string $saveUrl = 'defaultSaveUrl',
    )
    {
        $this->emailModalId = $emailModalId;
        $this->modalTitle = $modalTitle;
        $this->emailformId = $emailformId;
        $this->foreignId = $foreignId;
        $this->emailId = $emailId;
        $this->toField = $toField;
        $this->ccField = $ccField;
        $this->subject = $subject;
        $this->selectBox = $selectBox;
        $this->body = $body;
        $this->saveButtonId = $saveButtonId;
        $this->saveUrl = $saveUrl;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.purchase-order-email');
    }
}
