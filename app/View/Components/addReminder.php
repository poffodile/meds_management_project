<?php

namespace App\View\Components;

use Closure;
use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

class addReminder extends Component
{
    /**
     * Create a new component instance.
     */
    public $reminderModalId;
    public $modalTitle;
    public $reminderformId;
    public $reminderId;
    public $reminderDate;
    public $reminderTime;
    public $reminderUser;
    public $hiddenForeignId;
    public $reminderNotification;
    public $reminderEmail;
    public $reminderSms;
    public $reminderTitle;
    public $reminderNotes;
    public $saveButtonId;
    public $reminderSaveUrl;
    public function __construct(
    string $reminderModalId = 'defaultModalId',
    string $reminderformId = 'defaultFormId',
    string $modalTitle = 'defaultModalTitle',
    string $reminderId = 'defaultReminderId',
    string $reminderDate = 'defaultReminderDate',
    string $reminderTime = 'defaultReminderTime',
    string $reminderUser = 'defaultReminderUser',
    string $hiddenForeignId = 'defaulthiddenForeignId',
    string $reminderNotification = 'defaulNotification',
    string $reminderEmail = 'defaulEmail',
    string $reminderSms = 'defaulSms',
    string $reminderTitle = 'defaulTitle',
    string $reminderNotes = 'defaulNotes',
    string $saveButtonId = 'defaultSaveButtonId',
    string $reminderSaveUrl = 'defaultReminderSaveUrl',)
    {
        $this->reminderModalId = $reminderModalId;
        $this->reminderformId = $reminderformId;
        $this->modalTitle = $modalTitle;
        $this->reminderId = $reminderId;
        $this->reminderDate = $reminderDate;
        $this->reminderTime = $reminderTime;
        $this->reminderUser = $reminderUser;
        $this->hiddenForeignId = $hiddenForeignId;
        $this->reminderNotification = $reminderNotification;
        $this->reminderEmail = $reminderEmail;
        $this->reminderSms = $reminderSms;
        $this->reminderTitle = $reminderTitle;
        $this->reminderNotes = $reminderNotes;
        $this->saveButtonId = $saveButtonId;
        $this->reminderSaveUrl = $reminderSaveUrl;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.add-reminder');
    }
}
