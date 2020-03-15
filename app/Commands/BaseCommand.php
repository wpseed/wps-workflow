<?php

namespace App\Commands;

use App\Helpers\BitbucketHelper;
use App\Helpers\HasuraHelper;
use Composer\Package\Version\VersionParser;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use PhpZip\ZipFile;
use Symfony\Component\Filesystem\Filesystem;

class BaseCommand extends Command
{
    protected $filesystem;
    protected $zip;
    protected $bitbucket;
    protected $hasura;
    protected $version_parser;

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'base';

    public function __construct() {
        parent::__construct();
        $this->filesystem = new Filesystem();
        $this->zip = new ZipFile();
        $this->bitbucket = new BitbucketHelper();
        $this->hasura = new HasuraHelper();
        $this->version_parser = new VersionParser();
    }
}
