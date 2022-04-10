<?php

namespace App\Jobs\Email;

use App\Mail\ResetEmail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class PasswordRecoveryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $password;
    public $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct (User $user, $password)
    {
        $this->password = $password;
        $this->user     = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle ()
    {
        Mail::to($this->user)
            ->locale('pt-BR')
            ->send(new ResetEmail($this->user, $this->password));

    }
}
