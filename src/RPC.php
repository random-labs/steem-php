<?php

namespace Greymass\SteemPHP;

use JsonRPC\Client as RpcClient;
use JsonRPC\HttpClient;

class RPC {

    protected $client;
    protected $host = 'https://node.steem.ws';
    protected $mapping = [
        'get_accounts' => 'Greymass\SteemPHP\Data\Account',
        'get_block' => 'Greymass\SteemPHP\Data\Block',
        'get_content' => 'Greymass\SteemPHP\Data\Comment',
        'get_state' => 'Greymass\SteemPHP\Data\State',
        'get_discussions_by_author_before_date' => 'Greymass\SteemPHP\Data\Comment'
    ];

    public function __construct($host = null) {
        if($host) $this->host = $host;
        if(version_compare(PHP_VERSION, '5.6.0', '<')) {
            $httpClient = new HttpClient($this->host);
            $httpClient->withoutSslVerification();
            $this->client = new RpcClient($this->host, false, $httpClient);
        } else {
            $this->client = new RpcClient($this->host);
        }
    }

    public function get_client() {
        return $this->client;
    }

    public function get_connection() {
        return $this->host;
    }

    public function get_account($accountName) {
        return $this->get_accounts([$accountName])[0];
    }

    public function get_posts($account, $limit = 100, $start = "") {
        return $this->get_discussions_by_author_before_date($account, $start, date("Y-m-d\TH:i:s"), $limit);
    }

    public function __call($name, $arguments) {
        $response = $this->client->$name($arguments);
        if($this->is_assoc($response)) {
            return $this->model($name, $response);
        } else {
            $models = array();
            foreach($response as $idx => $data) {
                $models[$idx] = $this->model($name, $data);
            }
            return $models;
        }
    }

    public function model($name, $data) {
        if(array_key_exists($name, $this->mapping)) {
            return new $this->mapping[$name]($data);
        }
        return $data;
    }

    public function is_assoc(Array $array) {
       $keys = array_keys($array);
       return $keys !== array_keys($keys);
    }

}
