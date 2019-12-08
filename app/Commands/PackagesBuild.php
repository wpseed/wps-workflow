<?php

namespace App\Commands;

use App\Models\Plugin;
use App\Models\PluginVersion;
use App\Models\Theme;
use App\Models\ThemeVersion;
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

        $theme_model = new Theme();
        $themes = $theme_model->all();

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
<<<<<<< HEAD
                    'type' => config('bitbucket.accounts.plugins'),
=======
                    'type' => env('COMPOSER_PLUGINS_TYPE', 'wpseed-plugin'),
>>>>>>> github/master
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

        foreach ($themes as $theme) {
            $theme_slug = $theme->slug;
            $theme_package_name = config('bitbucket.accounts.themes') . '/' . $theme_slug;
            $theme_versions = $theme->theme_versions;

            $theme_content = [];
            $uid = 1;

            foreach ($theme_versions as $ptheme_version) {
                $version_parser = new VersionParser();
                $normalized_version = $version_parser->normalize($theme_version->version);
                $version_content = [
                    'name' => $theme_package_name,
                    'type' => env('COMPOSER_THEMES_TYPE', 'wpseed-theme'),
                    'version' => $theme_version->version,
                    'version_normalized' => $normalized_version,
                    'uid' => $uid++,
                    'dist' => [
                        'type' => 'zip',
                        'url' => 'https://bitbucket.org/' . $theme_package_name . '/get/v' . $theme_version->version . '.zip'
                    ],
                    'source' => [
                        'type' => 'git',
                        'url' => 'https://bitbucket.org/' . $theme_package_name . '.git',
                        'reference' => 'tags/' . $theme_version->version
                    ],
                    'require' => [
                        'composer/installers' => '~1.0'
                    ]
                ];
                $theme_content[$theme_version->version] = $version_content;
            }
            $packages_content[$theme_package_name] = $theme_content;
        }

        $content = json_encode([ 'packages' => $packages_content], JSON_PRETTY_PRINT);

        $folder_result = base_path(config('packages.result'));

        File::put($folder_result . '/' . 'packages.json', $content);
    }
}
