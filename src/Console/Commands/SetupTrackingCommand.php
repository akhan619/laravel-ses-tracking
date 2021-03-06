<?php

namespace Akhan619\LaravelSesTracking\Console\Commands;

use Akhan619\LaravelSesTracking\App\SetupManager;
use Illuminate\Console\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

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

    /**
     * Get the SymfonyStyle instance.
     *
     * @return SymfonyStyle
     */
    public function getIo()
    {
        return $this->io;
    }
}
