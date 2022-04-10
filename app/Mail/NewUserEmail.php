<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewUserEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $password;
    public $name;
    public $user;
    public $sender;

    /**
     * Create a new message instance.
     *
     * @param User $user
     * @param $password
     */
    public function __construct (User $user, $password)
    {
        $this->password = $password;
        $this->name     = $user->name;
        $this->user     = $user;
        $this->sender   = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build ()
    {
        return $this->subject('Bem vindo à Empresa')->view('emails.users.new_email');
    }
}
