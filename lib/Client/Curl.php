<?php

declare(strict_types=1);

namespace Buzz\Client;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class Curl extends AbstractCurl implements BuzzClientInterface
{
    public function sendRequest(RequestInterface $request, array $options = []): ResponseInterface
    {
        $options = $this->validateOptions($options);
        $curl = $this->createHandle();
        $responseBuilder = $this->prepare($curl, $request, $options);

        $curlInfo = null;
        try {
            curl_exec($curl);
            $this->parseError($request, curl_errno($curl), $curl);
            $curlInfo = curl_getinfo($curl);
        } finally {
            $this->releaseHandle($curl);
        }

        $response = $responseBuilder->getResponse();
        if ($options->get('expose_curl_info', false)) {
            $response = $response->withHeader('__curl_info', json_encode($curlInfo));
        }

        return $response;
    }
}
