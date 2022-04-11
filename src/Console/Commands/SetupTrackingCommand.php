<?php

namespace Akhan619\LaravelSesTracking\Console\Commands;

use Akhan619\LaravelSesTracking\App\SetupManager;
use Symfony\Component\Console\Style\SymfonyStyle;
use Illuminate\Console\Command;

class SetupTrackingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'SesTracking:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup the SES and SNS infrastructure for email events.';

    /**
     * The instance of the SymfonyStyle class.
     *
     * @var SymfonyStyle
     */
    public $io;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->io = new SymfonyStyle($this->input, $this->output);
        SetupManager::create($this);
    }
}
