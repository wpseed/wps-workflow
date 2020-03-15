<?php

namespace App\Helpers;

use Cocur\Slugify\Slugify;
use Symfony\Component\HttpClient\HttpClient;

class HasuraHelper {

    public $hasura_response;
    private $http_client;

    public function __construct()
    {
        $this->http_client = HttpClient::create();
    }

    protected function query($query): \Symfony\Contracts\HttpClient\ResponseInterface
    {
        $hasura_params = [
            'hasura_endpoint'   => config('hasura.api.hasura_endpoint'),
            'hasura_secret'     => config('hasura.api.hasura_secret')
        ];
        return $this->hasura_response = $this->http_client->request(
            'POST',
            $hasura_params['hasura_endpoint'],
            [
                'headers' => [
                    'X-Hasura-Admin-Secret' => $hasura_params['hasura_secret'],
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode([
                    'query' => $query,
                ], JSON_THROW_ON_ERROR, 512),
            ]
        );
    }

    public function checkPlugin($plugin_slug): bool
    {
        $check = <<<GRAPHQL
        query MyQuery {
            wps_plugins(where: {slug: {_eq: "$plugin_slug"}}) {
                id,
                slug,
            }
        }
        GRAPHQL;
        $response = $this->query($check);
        $plugins = json_decode($response->getContent(false), false, 512, JSON_THROW_ON_ERROR);
        return (bool) $plugins->data->wps_plugins;
    }

    public function addPlugin(
        $plugin_name,
        $plugin_slug = null,
        $plugin_description = null,
        $plugin_short_description = null,
        $plugin_type = 'free',
        $plugin_status = 'private'
    )
    {
        if (null === $plugin_slug) {
            $plugin_slug = (new Slugify)->slugify($plugin_name);
        }

        if ($this->checkPlugin($plugin_slug)) {
            return false;
        }

        $mutation = <<<GRAPHQL
        mutation pluginAdd {
            insert_wps_plugins(objects: {
                name: "$plugin_name",
                slug: "$plugin_slug"
                description: "$plugin_description",
                short_description: "$plugin_short_description",
                type: "$plugin_type",
                status: "$plugin_status"
            }) {
                returning {
                    id
                    name
                    slug
                }
            }
        }
        GRAPHQL;
        return $this->query($mutation);
    }
}
