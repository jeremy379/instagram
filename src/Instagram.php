<?php

/*
 * This file is part of Instagram.
 *
 * (c) Vincent Klaiber <hello@vinkla.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//declare(strict_types=1);

namespace Vinkla\Instagram;

use Http\Client\HttpClient;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Message\RequestFactory;

/**
 * This is the instagram class.
 *
 * @author Vincent Klaiber <hello@vinkla.com>
 */
class Instagram
{
    /** 
    * Access token
    **/
    protected $accessToken;
    
    /**
     * The http client.
     *
     * @var \Http\Client\HttpClient
     */
    protected $httpClient;

    /**
     * The http request factory.
     *
     * @var \Http\Message\RequestFactory
     */
    protected $requestFactory;

    /**
     * Create a new instagram instance.
     *
     * @param string $accessToken
     * @param \Http\Client\HttpClient|null $httpClient
     * @param \Http\Message\RequestFactory|null $requestFactory
     *
     * @return void
     */
    public function __construct($accessToken, HttpClient $httpClient = null, RequestFactory $requestFactory = null)
    {
        $this->accessToken = $accessToken;
        $this->httpClient = $httpClient ?: HttpClientDiscovery::find();
        $this->requestFactory = $requestFactory ?: MessageFactoryDiscovery::find();
    }

    /**
     * Fetch the media items.
     *
     * @throws \Vinkla\Instagram\InstagramException
     *
     * @return array
     */
    public function get()
    {
        $uri = sprintf('https://api.instagram.com/v1/users/self/media/recent?access_token=%s', $this->accessToken);

        $this->sendGetRequest($uri);
    }

    public function getAnonymousPageData($pageName) {

        $uri = sprintf('https://www.instagram.com/%s/?__a=1', $pageName);

        return $this->sendGetRequest($uri);
    }

    public function sendGetRequest($uri) {
        $request = $this->requestFactory->createRequest('GET', $uri);
        $response = $this->httpClient->sendRequest($request);

        if ($response->getStatusCode() === 404) {
            $body = json_decode((string) $response->getBody());
            throw new InstagramException($body->meta->error_message);
        }

        return json_decode((string) $response->getBody())->data;
    }
}
