<?php

namespace App\View\Components;

use Closure;
use Illuminate\View\Component;
use Illuminate\Contracts\View\View;
use Auth;
use App\Models\AttachmentType;

class addAttachmentModal extends Component
{
    /**
     * Create a new component instance.
     */
    public $purchaseModalId;
    public $modalTitle;
    public $purchaseformId;
    public $refTitle;
    public $typeId;
    public $inputTitle;
    public $inputDescription;
    public $selectfileName;
    public $hiddenForeignId;
    public $saveButtonId;
    public $saveButtonUrl;
    public function __construct(
        string $purchaseModalId = 'defaultModalId',
        string $purchaseformId = 'defaultFormId',
        string $refTitle = 'defaultRefTitle',
        string $modalTitle = 'Add Attachment',
        string $typeId = 'defaultTypeId',
        string $inputTitle = 'defaultInputTitle',
        string $selectfileName = 'defaultSelectfileName',
        string $inputDescription = 'defaultInputDescription',
        string $hiddenForeignId = 'defaulthiddenForeignId',
        string $saveButtonId = 'defaultSaveButtonId',
        string $saveButtonUrl = 'defaultSaveButtonUrl',)
    {
        $this->purchaseModalId = $purchaseModalId;
        $this->purchaseformId = $purchaseformId;
        $this->refTitle = $refTitle;
        $this->modalTitle = $modalTitle;
        $this->typeId = $typeId;
        $this->inputTitle = $inputTitle;
        $this->inputDescription = $inputDescription;
        $this->selectfileName = $selectfileName;
        $this->hiddenForeignId = $hiddenForeignId;
        $this->saveButtonId = $saveButtonId;
        $this->saveButtonUrl = $saveButtonUrl;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        // $attachmentType = AttachmentType::getActiveAttachmentType(Auth::user()->home_id);
        // echo "<pre>";print_r($attachmentType);die;
        return view('components.add-attachment-modal');
    }
}
