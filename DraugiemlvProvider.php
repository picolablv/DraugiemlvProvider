<?php

namespace App\Helpers;

use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\ProviderInterface;
use Laravel\Socialite\Two\User;

class DraugiemlvProvider extends AbstractProvider implements ProviderInterface
{

    /**
     * @var string
     */
    protected $apiAuthorizeUrl = 'http://api.draugiem.lv/authorize/';

    /**
     * @var string
     */
    protected $apiUrl = 'http://api.draugiem.lv/json/';


    /**
     * @return array|string
     */
    protected function getCode()
    {
        return $this->request->input('dr_auth_code');
    }

    /**
     * @param string $state
     * @return string
     */
    protected function getAuthUrl($state)
    {
        return $this->apiAuthorizeUrl . '?' . http_build_query(['app' => $this->clientId, 'hash' => md5($this->clientSecret . $this->redirectUrl), 'redirect' => $this->redirectUrl], '', '&', $this->encodingType);
    }


    /**
     * @return string
     */
    protected function getTokenUrl()
    {
        return $this->apiUrl;
    }


    /**
     * @param string $code
     * @return string
     */
    public function getAccessToken($code)
    {
        $response = $this->getHttpClient()->get($this->getTokenUrl(), [
            'headers' => ['Accept' => 'application/json'],
            'query' => $this->getTokenFields($code),
        ]);


        return $this->parseAccessToken($response->getBody());
    }


    /**
     * @return mixed
     */
    public function user()
    {

        $user = $this->mapUserToObject($this->getUserByToken(
            $token = $this->getAccessToken($this->getCode())
        ));


        return $user->setToken($token);
    }


    /**
     * Get the POST fields for the token request.
     *
     * @param  string  $code
     * @return array
     */
    protected function getTokenFields($code)
    {
        return [
            'action' => 'authorize',
            'app' => $this->clientSecret,
            'code' => $code
        ];
    }


    /**
     * Get the access token from the token response body.
     *
     * @param  string  $body
     * @return string
     */
    protected function parseAccessToken($body)
    {
        return json_decode($body, true)['apikey'];
    }


    /**
     * @param string $token
     * @return mixed
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get($this->getTokenUrl(), [
            'headers' => ['Accept' => 'application/json'],
            'query' => ['action' => 'userdata', 'apikey' => $token, 'app' => $this->clientSecret]
        ]);

        $returnData = json_decode($response->getBody(), true);

        return reset($returnData['users']);
    }


    /**
     * @param array $user
     * @return $this
     */
    protected function mapUserToObject(array $user)
    {
        return (new User)->setRaw($user)->map([
            'id' => $user['uid'],
            'nickname' => $user['nick'],
            'emailHash' => $user['emailHash'],
            'name' => $user['name'] . ' ' . $user['surname'],
            'avatar' => !empty($user['imgl']) ? $user['imgl'] : null,
        ]);
    }

}