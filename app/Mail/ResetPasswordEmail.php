<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPasswordEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $user;
    public $sender;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct (User $user)
    {
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
        return $this->subject('Redefinição de senha')->view('emails.users.reset_password_email');
    }
}
