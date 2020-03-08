<?php

namespace App\Helpers;

use Cocur\Slugify\Slugify;
use Symfony\Component\HttpClient\HttpClient;

class HasuraHelper {

    public $hasura_response;

    public function __construct()
    {
    }

    protected function query($query): \Symfony\Contracts\HttpClient\ResponseInterface
    {
        $hasura_params = [
            'hasura_endpoint'   => config('hasura.api.hasura_endpoint'),
            'hasura_secret'     => config('hasura.api.hasura_secret')
        ];
        $client = HttpClient::create();
        return $this->hasura_response = $client->request(
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

    public function checkPlugin($plugin_slug)
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
        $plugins = json_decode($response->getContent(false), true, 512, JSON_THROW_ON_ERROR);
        return (bool) $plugins['data']['wps_plugins'];
    }

    public function addPlugin(
        $plugin_name,
        $plugin_description = null,
        $plugin_short_description = null,
        $plugin_type = 'free',
        $plugin_status = 'private'
    ): \Symfony\Contracts\HttpClient\ResponseInterface
    {
        $plugin_slug = (new Slugify)->slugify($plugin_name);

        var_dump($this->query($check)->getStatusCode());

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
        return $this->query($check);
    }
}
