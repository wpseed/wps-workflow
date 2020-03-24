<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class PackagesTest extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'packages:test';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Test packages for development purposes';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $folder_upload = base_path(config('packages.upload'));
        if (is_link($folder_upload)) {
            $folder_upload = readlink($folder_upload);
        }

        $files_list = scandir($folder_upload);

        natsort($files_list);

        foreach ($files_list as $files_item) {
            $file_path = $folder_upload . '/' . $files_item;
            $file_ext = pathinfo($file_path, PATHINFO_EXTENSION);
            if (!is_file($file_path)) {
                continue;
            }
            $package = new \Max_WP_Package($file_path);
            $package_type = $package->get_type();
            $package_metadata = $package->get_metadata();
            $original_package_slug = preg_replace('/\s/', '', $package_metadata['slug']);
            $package_slug = $original_package_slug;
            var_dump($package_slug);
            if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $package_slug)) {
                $package_slug = preg_replace('/\s/', '', $package_metadata['text_domain']);
                $this->info('Uncorrect slug: ' . $original_package_slug . ' changed to: ' . $package_metadata['text_domain']);
            }
            $this->info('Package: ' . $package_slug . ', version: ' . $package_metadata['version'] . ', type: ' . $package_type);
            //var_dump($package_metadata);
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
}
