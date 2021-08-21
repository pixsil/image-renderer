<?php 
    
// version 1
    
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanStorageCacheCommmand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:clean-storage-cache {disk?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete all the cache folders for a given disk';

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
        // if disk is set
        $disk = $this->argument('disk') ?? config('filesystems.default');

        // fall back to default disk
        $allDirs = Storage::disk($disk)->allDirectories();

        //
        $matchingDirs = preg_grep('/^.*\/\d*\/\w*\/cache$/', $allDirs);

        //
        foreach ($matchingDirs as $matchingDir) {
            Storage::disk($disk)->deleteDirectory($matchingDir);
        }

        return 0;
    }
}
