<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class RefreshApp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Re-run migrations, seeds and demo seeds, and passport:install. WARNING: use ONLY in DEV';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $output = new \Symfony\Component\Console\Output\ConsoleOutput();
        $output->writeln("<info>Cleaning database, rerunning migrations, seeders and passport credentials</info>");

        Artisan::call('migrate:fresh', [], $output);
        Artisan::call('db:seed', [], $output);
        Artisan::call('db:seed', ['--class' => 'DemoSeeder', '-v' => 'vvv'], $output);
        Artisan::call('passport:install', [], $output);

        $output->writeln("<info>App fully refreshed! Now you can test and break things as you wish \o/</info>");
        return 0;
    }
}
