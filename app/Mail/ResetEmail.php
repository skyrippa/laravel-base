<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $token;
    public $name;
    public $user;
    public $sender;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct (User $user, $token)
    {
        $this->token  = $token;
        $this->name   = $user->name;
        $this->user   = $user;
        $this->sender = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build ()
    {
        return $this->subject('Alterar senha')->view('emails.users.reset_email');
    }
}
