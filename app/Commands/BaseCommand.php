<?php

namespace App\Commands;

use App\Helpers\BitbucketHelper;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use PhpZip\ZipFile;
use Symfony\Component\Filesystem\Filesystem;

class BaseCommand extends Command
{
    protected $filesystem;
    protected $zip;
    protected $bitbucket;

    public function __construct() {
        parent::__construct();
        $this->filesystem = new Filesystem();
        $this->zip = new ZipFile();
        $this->bitbucket = new BitbucketHelper();
    }
}
