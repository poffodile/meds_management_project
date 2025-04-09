<?php

namespace App\View\Components;

use Closure;
use Illuminate\View\Component;
use Illuminate\Contracts\View\View;
use Auth;
use App\User;
use App\Models\Task_type;
use App\Models\Week;

class NewTaskModal extends Component
{
    /**
     * Create a new component instance.
     */
    public $modalId;
    public $modalTitle;
    public $formId;
    public $taskCustomerId;
    public $taskId;
    public $foriegnId;
    public $userId;
    public $taskTitle;
    public $taskTypeId;
    public $taskStartDate;
    public $taskStartTime;
    public $taskEndDate;
    public $taskEndTime;
    public $notifyDate;
    public $notifyTime;
    public $taskNotesText;
    public $modalLabelTitle;
    public $saveButtonId;
    public $saveNewTaskUrl;
    public $completeNewTaskUrl;
    public function __construct(
        string $modalId = 'defaultModalId',
        string $modalTitle = 'Add Attachment',
        string $formId = 'defaultFormId',
        string $taskCustomerId = 'defaultCustomerId',
        string $taskId = 'defaultTaskId',
        string $foriegnId = 'defaultForiegnId',
        string $userId = 'defaultUserId',
        string $taskTitle = 'defaultTaskTitle',
        string $taskTypeId = 'defaultTaskTypeId',
        string $taskStartDate = 'defaultTaskStartDate',
        string $taskStartTime = 'defaultTaskStartTime',
        string $taskEndDate = 'defaultTaskEndDate',
        string $taskEndTime = 'defaultTaskEndTime',
        string $notifyDate = 'defaultNotifyDate',
        string $notifyTime = 'defaultNotifyTime',
        string $taskNotesText = 'defaultTaskNotesText',
        string $modalLabelTitle = 'defaultModalLabelTitle',
        string $saveButtonId = 'defaultSaveButtonId',
        string $saveNewTaskUrl = 'defaultSaveNewTaskUrl',
        string $completeNewTaskUrl = 'defaultCompleteNewTaskUrl',
        )
    {
        $this->modalId = $modalId;
        $this->modalTitle = $modalTitle;
        $this->formId = $formId;
        $this->taskCustomerId = $taskCustomerId;
        $this->taskId = $taskId;
        $this->foriegnId = $foriegnId;
        $this->userId = $userId;
        $this->taskTitle = $taskTitle;
        $this->taskTypeId = $taskTypeId;
        $this->taskStartDate = $taskStartDate;
        $this->taskStartTime = $taskStartTime;
        $this->taskEndDate = $taskEndDate;
        $this->taskEndTime = $taskEndTime;
        $this->notifyDate = $notifyDate;
        $this->notifyTime = $notifyTime;
        $this->taskNotesText = $taskNotesText;
        $this->modalLabelTitle = $modalLabelTitle;
        $this->saveButtonId = $saveButtonId;
        $this->saveNewTaskUrl = $saveNewTaskUrl;
        $this->completeNewTaskUrl = $completeNewTaskUrl;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $home_id = Auth::user()->home_id;
        $data['users'] = User::getHomeUsers($home_id);
        $data['task_type']=Task_type::getAllTask_type($home_id);
        $data['weeks'] = Week::getWeeklist();
        return view('components.new-task-modal',$data);
    }
}
