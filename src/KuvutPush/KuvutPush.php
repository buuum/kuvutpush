<?php

namespace Buuum\KuvutPush;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;

class KuvutPush extends AbstractProvider
{

    const BASE_API_URL = 'https://push.kuvut.com/api/1.0';

    protected $accessToken;

    public function getAccessToken($grant = 'client_credentials', array $params = [])
    {
        if (isset($params['refresh_token'])) {
            throw new \Exception('Kuvut Push does not support token refreshing.');
        }
        if (empty($this->accessToken)) {
            $this->accessToken = parent::getAccessToken($grant, $params);
        }
        return $this->accessToken;
    }

    public function getBaseAuthorizationUrl()
    {
        return static::BASE_API_URL . '/authorize/';
    }

    public function getBaseAccessTokenUrl(array $params)
    {
        return static::BASE_API_URL . '/token/';
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return '';
    }

    protected function getDefaultScopes()
    {
        return ['basic'];
    }

    protected function checkResponse(ResponseInterface $response, $data)
    {
        if (!empty($data['error'])) {
            $message = $data['error'];
            throw new \Exception($message, -1);
        }
    }

    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return null;
    }

    protected function getBaseOptions()
    {
        return ['headers' => ['Authorization' => 'Bearer ' . $this->getAccessToken()->getToken()]];
    }

    protected function parseUrl(string $endpoint, array $parameters = [])
    {
        $url = static::BASE_API_URL . $endpoint;
        foreach ($parameters as $parameter => $value) {
            if (!is_array($value)) {
                $url .= '&' . $parameter . '=' . urlencode($value);
            }
        }
        return $url;
    }

    protected function getResponseBaseOptions($method, $url, $data = [])
    {
        $options = $this->getBaseOptions();
        $json = [];
        foreach ($data as $k => $d) {
            if (is_array($d)) {
                $json[$k] = $d;
            }
        }
        $options['body'] = json_encode($json);
        $request = $this->getRequest($method, $url, $options);
        $response = $this->getParsedResponse($request);
        return $response;
    }

    public function addToken(array $options = [])
    {
        $method = 'PUT';
        if (empty($options['token'])) {
            throw new \Exception('Token missing');
        }

        $url = $this->parseUrl('/add/token/', $options);
        return $this->getResponseBaseOptions($method, $url);
    }

    public function addUsuario(array $options = [])
    {
        $method = 'PUT';
        if (empty($options['name'])) {
            throw new \Exception('Name missing');
        }
        if (empty($options['user_id'])) {
            throw new \Exception('User Id missing');
        }
        $url = $this->parseUrl('/action/add/', $options);
        return $this->getResponseBaseOptions($method, $url);
    }

}