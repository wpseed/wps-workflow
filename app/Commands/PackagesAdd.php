<?php

namespace App\Commands;

use App\Models\Plugin;
use App\Models\PluginVersion;
use App\Models\Theme;
use App\Theme\ThemeVersion;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use Cz\Git\GitRepository;

class PackagesAdd extends BaseCommand
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'packages:add {--check}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Add packages(plugins and themes) to core database';

    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $bitbucket_account_plugins = config('bitbucket.accounts.plugins');
        $bitbucket_account_themes = config('bitbucket.accounts.themes');

        $this->info('Task: Add packages(plugins and themes) to core database started...');

        $folder_upload = base_path(config('packages.upload'));
        if (is_link($folder_upload)) {
            $folder_upload = readlink($folder_upload);
        }

        $files_list = scandir($folder_upload);

        natsort($files_list);

        $this->filesystem->remove(base_path(config('packages.extract')));

        foreach ($files_list as $files_item) {
            $file_path = $folder_upload . '/' . $files_item;
            $file_ext = pathinfo($file_path, PATHINFO_EXTENSION);
            if (! is_file($file_path)) {
                continue;
            }
            $this->info('Try add package from archive: ' . $files_item);
            if ('zip' !== $file_ext) {
                $this->error('File Error: File extension is not a zip');
                //$this->filesystem->remove($file_path);
                continue;
            }
            $package = new \Max_WP_Package($file_path);
            $package_type = $package->get_type();
            $package_metadata = $package->get_metadata();
            $package_slug = preg_replace('/\s/', '', $package_metadata['slug']);

            $bitbucket_account = ('plugin' === $package_type) ? $bitbucket_account_plugins : $bitbucket_account_themes;
            $this->info('Package: ' . $package_slug . ', version: ' . $package_metadata['version'] . ', type: ' . $package_type);
            $file_hash_sha256 = hash_file('sha256', $file_path);
            $file_hash_sha1 = hash_file('sha1', $file_path);
            $file_hash_md5 = hash_file('md5', $file_path);

            $extract_path = base_path(config('packages.extract') . '/' . $file_hash_sha256);
            $this->filesystem->mkdir($extract_path, 0755);

            $this->bitbucket->create($bitbucket_account, $package_slug, substr($package_metadata['description'], 0, 755));

            try {
                $git_repo = GitRepository::init($extract_path . '/' . $package_slug);
                $git_repo->addRemote(
                    'origin',
                    'git@bitbucket.org:' . $bitbucket_account . '/' . $package_slug . '.git'
                );
                $git_repo->fetch(null, ['--all']);
                $git_repo_tags = $git_repo->getTags() ? $git_repo->getTags() : [];
            } catch (\Throwable $t) {
                $this->error('Repository Fatal Error: ' . $t->getMessage());
            }

            if (in_array('v' . $package_metadata['version'], $git_repo_tags)) {
                $this->error('Repository Error: Package version exists in repository');
                continue;
            }

            if ($this->option('check') && !$this->db_package_version_check($package_slug, $package_type, $package_metadata['version'])) {
                $this->error('Database Error: Package version smaller then exists versions');
                continue;
            }

            try {
                if (! empty($git_repo_tags)) {
                    $git_repo->checkout('master');
                    $git_repo->pull();
                }
                $zipFile = $this->zip->openFile($file_path);
                $zipFile->extractTo($extract_path);
                $git_repo->addAllChanges();
                $git_repo->commit('v' . $package_metadata['version']);
                $git_repo->createTag(
                    'v' . $package_metadata['version'],
                    ['-m' => 'v' . $package_metadata['version']]
                );
                $git_repo->push('origin master');
                $git_repo->push('origin master --tags');
                $this->info('Package version added to repository');
            } catch (\Throwable $t) {
                $this->error('Repository Fatal Error:' . $t->getMessage());
            } finally {
                $zipFile->close();
            }

            $this->db_package_insert($package_metadata['name'], $package_slug, $package_type, $package_metadata['description']);
            $this->db_package_version_insert($package_slug, $package_type, $package_metadata['version'], $file_hash_sha256, $file_hash_sha1, $file_hash_md5);
            $this->info('Package version added to database');
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

    public function db_package_version_check($slug, $type, $version)
    {
        $package = ('plugin' === $type) ? new Plugin() : new Theme();
        $package_current = $package->where('slug', $slug)->first();
        if (!$package_current) {
            return true;
        }
        $package_versions =  ('plugin' === $type) ? $package_current->plugin_versions : $package_current->theme_versions;
        $package_versions_array = $package_versions->sortByDesc('version')->values()->map(function ($item, $key) {
            return $item['version'];
        });
        return(($version > $package_versions_array[0]));
    }

    public function db_package_insert($name, $slug, $type, $description)
    {
        $package = ('plugin' === $type) ? new Plugin() : new Theme();

        $package_current = $package->where('slug', $slug)->first();

        if ($package_current) {
            $this->error('Database Error: Package already exists');
            return false;
        }

        $package->name = $name;
        $package->slug = $slug;
        $package->description = $description;

        $package->save();
        $this->info('Database: Package successfully added to database');
        return true;
    }

    public function db_package_version_insert($slug, $type, $version, $hash_sha256, $hash_sha1, $hash_md5)
    {
        $package = ('plugin' === $type) ? new Plugin() : new Theme();
        $package_version = ('plugin' === $type) ? new PluginVersion() : new ThemeVersion();

        $package_current = $package->where('slug', $slug)->first();
        $package_current_id = $package_current->id;
        if ('plugin' === $type) {
            $package_version->plugin_id = $package_current_id;
        } else {
            $package_version->theme_id = $package_current_id;
        }
        $package_version->version = $version;
        $package_version->hash_sha256 = $hash_sha256;
        $package_version->hash_sha1 = $hash_sha1;
        $package_version->hash_md5 = $hash_md5;

        $package_version->save();
    }
}
