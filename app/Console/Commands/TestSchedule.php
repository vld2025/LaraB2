<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestSchedule extends Command
{
    protected $signature = 'test:schedule';
    protected $description = 'Test schedule';

    public function handle()
    {
        $this->info('Schedule test funziona!');
    }
}
