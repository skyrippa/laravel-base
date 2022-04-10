<?php

namespace App\Jobs\Email;

use App\Mail\NewUserEmail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class NewUserJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $user;
    public $password;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct (User $user, $password)
    {
        $this->user     = $user;
        $this->password = $password;
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
            ->send(new NewUserEmail($this->user, $this->password));
    }
}
