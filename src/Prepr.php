<?php

namespace Graphlr\Prepr;

use GuzzleHttp\Client;

class Prepr
{
    protected $baseUrl;
    protected $path;
    protected $query;
    protected $method;
    protected $params = [];
    protected $response;
    protected $rawResponse;
    protected $request;
    protected $authorization;

    public function __construct()
    {
        $this->baseUrl = config('prepr.url');
        $this->authorization = config('prepr.token');
    }

    protected function client()
    {
        return new Client([
            'http_errors' => false,
            'headers' => array_merge(
                config('prepr.headers'),
                [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => $this->authorization
                ]
            )
        ]);
    }

    protected function request($options = [])
    {
        $this->client = $this->client();

        $url = $this->baseUrl.$this->path;

        $this->request = $this->client->request($this->method, $url.$this->query, [
            'multipart' => $this->nestedArrayToMultipart($this->params)
        ]);

        $this->response = json_decode($this->request->getBody()->getContents(), true);
        $this->rawResponse = $this->request->getBody()->getContents();

        return $this;
    }

    public function authorization($authorization)
    {
        $this->authorization = $authorization;

        return $this;
    }

    public function url($url) {
        $this->baseUrl = $url;

        return $this;
    }

    public function get()
    {
        $this->method = 'get';

        return $this->request();
    }

    public function post()
    {
        $this->method = 'post';

        return $this->request();
    }

    public function put()
    {
        $this->method = 'put';

        return $this->request();
    }

    public function delete()
    {
        $this->method = 'delete';

        return $this->request();
    }

    public function path($path = null, array $array = [])
    {
        foreach($array as $key => $value) {
            $path = str_replace('{' . $key . '}', $value, $path);
        }

        $this->path = $path;

        return $this;
    }

    public function method($method = null)
    {
        $this->method = $method;

        return $this;
    }

    public function query(array $array)
    {
        $this->query = '?'.http_build_query($array);

        return $this;
    }

    public function params(array $array)
    {
        $this->params = $array;

        return $this;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function getRawResponse()
    {
        return $this->rawResponse;
    }

    public function getStatusCode()
    {
        return $this->request->getStatusCode();
    }

    public function nestedArrayToMultipart($array)
    {
        $multipart = [];

        foreach ($array as $key => $value) {

            if(!is_array($value)){
                $multipart[] = [
                    'name' => $key,
                    'contents' => $value
                ];

                continue;
            }

            foreach($value as $multiKey => $multiValue) {
                $multiName = $key . '[' .$multiKey . ']' . (is_array($multiValue) ? '[' . key($multiValue) . ']' : '' ) . '';

                $multipart[] = [
                    'name' => $multiName,
                    'contents' => (is_array($multiValue) ? reset($multiValue) : $multiValue)
                ];
            }
        }

        return $multipart;
    }
}
