<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ClearLog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'log:clear {name? : The name of the log file to delete}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear log files';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $logPath = storage_path('logs');
        $name = $this->argument('name');

        if ($name) {
            $logFile = $logPath . '/' . (str_ends_with($name, '.log') ? $name : $name . '.log');
            if (File::exists($logFile)) {
                File::delete($logFile);
                $this->info('Log file "' . $name . '" deleted successfully.');
            } else {
                $this->error('Log file "' . $name . '" not found.');
            }
        } else {
            foreach (File::glob($logPath . '/*.log') as $file) {
                File::delete($file);
            }

            $this->info('All log files cleared successfully.');
        }
    }
}
