<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WorkflowNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $workflowName;
    public string $emailSubject;
    public string $messageBody;

    public function __construct(string $workflowName, string $emailSubject, string $messageBody)
    {
        $this->workflowName = $workflowName;
        $this->emailSubject = $emailSubject;
        $this->messageBody = $messageBody;
    }

    public function build()
    {
        return $this->subject($this->emailSubject)
            ->view('emails.workflow_notification');
    }
}
