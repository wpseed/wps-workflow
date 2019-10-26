<?php

namespace App\Helpers;

use Bitbucket\API\Repositories\Repository;
use Bitbucket\API\Http\Listener\OAuth2Listener;
use Bitbucket\API\Teams;
use Bitbucket\API\User;

class BitbucketHelper
{
    protected $bitbucket_account;
    protected $bitbucket_repository;

    public function __construct()
    {
        $oauth_params = [
            'client_id'         => config('bitbucket.api.client_id'),
            'client_secret'     => config('bitbucket.api.client_secret')
        ];
        $this->bitbucket_account = new User();
        $this->bitbucket_account->getClient()->addListener(new OAuth2Listener($oauth_params));
        $this->bitbucket_repository = new Repository();
        $this->bitbucket_repository->getClient()->addListener(new OAuth2Listener($oauth_params));
    }

    public function check($account_name, $repo_slug)
    {
        return $this->bitbucket_repository->get($account_name, $repo_slug);
    }

    public function create($account_name, $repo_slug, $repo_description = '')
    {
        $result = $this->bitbucket_repository->create($account_name, $repo_slug, [
            'scm'               => 'git',
            'description'       => $repo_description,
            'language'          => 'php',
            'is_private'        => true,
            'fork_policy'       => 'no_public_forks',
            ]);
        return $result;
    }

    public function delete($account_name, $repo_slug)
    {
        $this->bitbucket_repository->delete($account_name, $repo_slug);
    }

    public function deleteAll($account_name)
    {
        //$this->bitbucket_repository->delete($account_name);
    }

    public function getAll()
    {
        print_r($this->bitbucket_account->repositories()->dashboard());
    }
}
