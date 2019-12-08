<?php

namespace App\Commands;

use App\Models\Plugin;
use App\Models\PluginVersion;
use Composer\Package\Version\VersionParser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PackagesBuild extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'packages:build';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Build packages.json from database';

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
     * @return mixed
     */
    public function handle()
    {
        $plugin_model = new Plugin();
        $plugins = $plugin_model->all();

        $packages_content = [];

        foreach ($plugins as $plugin) {
            $plugin_slug = $plugin->slug;
            $plugin_package_name = config('bitbucket.accounts.plugins') . '/' . $plugin_slug;
            $plugin_versions = $plugin->plugin_versions;

            $plugin_content = [];
            $uid = 1;

            foreach ($plugin_versions as $plugin_version) {
                $normalized_version = $this->version_parser->normalize($plugin_version->version);
                $version_content = [
                    'name' => $plugin_package_name,
                    'type' => config('bitbucket.accounts.plugins'),
                    'version' => $plugin_version->version,
                    'version_normalized' => $normalized_version,
                    'uid' => $uid++,
                    'dist' => [
                        'type' => 'zip',
                        'url' => 'https://bitbucket.org/' . $plugin_package_name . '/get/v' . $plugin_version->version . '.zip'
                    ],
                    'require' => [
                        'composer/installers' => '~1.0'
                    ]
                ];
                $plugin_content[$plugin_version->version] = $version_content;
            }
            $packages_content[$plugin_package_name] = $plugin_content;
        }

        $content = json_encode([ 'packages' => $packages_content], JSON_PRETTY_PRINT);

        $folder_result = base_path(config('packages.result'));

        File::put($folder_result . '/' . 'packages.json', $content);
    }
}
