<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeSubscriber extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:subscriber {name : The name of the subscriber to create}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add a new subscriber class file to app/Listeners directory';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $argName = $this->argument('name');
        $name = str_ends_with($argName, 'Subscriber') ? $argName : $argName . 'Subscriber';
        $fileName = $name . '.php';
        $filePath = app_path('Listeners/' . $fileName);

        if (File::exists($filePath)) {
            $this->error('Subscriber class already exists!');
            return;
        }

        $stub = File::get(base_path('stubs/subscriber.stub'));
        $stub = str_replace('{{ name }}', $name, $stub);

        File::put($filePath, $stub);

        $this->info('Subscriber class created successfully in app/Listeners.');
    }
}
