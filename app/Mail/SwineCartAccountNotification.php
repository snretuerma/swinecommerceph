<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Models\User;
use Carbon\Carbon;

class SwineCartAccountNotification extends Mailable
{
    use Queueable, SerializesModels;
    protected $user;
    protected $type;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, $type)
    {
         $this->user = $user;
         $this->type = $type;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.adminNotifications')
                    ->with([
                        'type'=>$this->type,
                        'user'=>$this->user
                    ]);
    }
}
