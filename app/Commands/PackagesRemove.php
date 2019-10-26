<?php

namespace App\Commands;

use App\Models\Plugin;
use App\Models\Theme;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class PackagesRemove extends BaseCommand
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'packages:remove {slug} {type=plugin}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Remove package with given slug';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $package_slug = $this->argument('slug');
        $package_type = $this->argument('type');

        $bitbucket_account_plugins = config('bitbucket.accounts.plugins');
        $bitbucket_account_themes = config('bitbucket.accounts.themes');

        $bitbucket_account = ('plugin' === $package_type) ? $bitbucket_account_plugins : $bitbucket_account_themes;

        $this->bitbucket->delete($bitbucket_account, $package_slug);

        $package_id = $this->db_package_get($package_slug, $package_type);
        if ($package_id) {
            $this->db_package_remove($package_slug, $package_type);
        }
    }

    /**
     * Define the command's schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }

    public function db_package_get($package_slug, $package_type)
    {

        $package = ('plugin' === $package_type) ? new Plugin() : new Theme();

        $package_current = $package->where('slug', $package_slug)->first();

        if ($package_current) {
            return $package_current->id;
        }

        return false;
    }

    public function db_package_remove($package_slug, $package_type)
    {
        $package = ('plugin' === $package_type) ? new Plugin() : new Theme();
        $package_current = $package->where('slug', $package_slug)->first();
        $package_current->delete();
        return true;
    }
}
