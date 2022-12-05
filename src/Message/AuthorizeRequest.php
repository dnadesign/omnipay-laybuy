<?php

namespace Omnipay\Laybuy\Message;

use Omnipay\Common\Exception\InvalidResponseException;
use Omnipay\Common\Message\AbstractRequest;

class AuthorizeRequest extends AbstractRequest
{
    protected $liveEndpoint = 'https://api.laybuy.com';

    protected $testEndpoint = 'https://sandbox-api.laybuy.com';

    /**
     * @return mixed
     */
    public function getMerchantId()
    {
        return $this->getParameter('merchantId');
    }

    /**
     * @param mixed $value
     * @return $this
     * @throws \Omnipay\Common\Exception\RuntimeException
     */
    public function setMerchantId($value)
    {
        return $this->setParameter('merchantId', $value);
    }

    /**
     * @return mixed
     */
    public function getMerchantSecret()
    {
        return $this->getParameter('merchantSecret');
    }

    /**
     * @param mixed $value
     * @return $this
     * @throws \Omnipay\Common\Exception\RuntimeException
     */
    public function setMerchantSecret($value)
    {
        return $this->setParameter('merchantSecret', $value);
    }

    protected function getHttpMethod()
    {
        return 'POST';
    }

    protected function getEndpoint()
    {
        return $this->getTestMode() ? $this->testEndpoint : $this->liveEndpoint;
    }

    public function getData()
    {
        if (isset($this->data)) {
            return $this->data;
        }
    }

    public function sendData($data)
    {
        try {
            $endpoint = $this->getHttpMethod() == 'GET' ? $this->getEndpoint() . '?' . http_build_query($data) : $this->getEndpoint();
            $response = $this->httpClient->request(
                $this->getHttpMethod(),
                $endpoint,
                array(
                    'Accept' => 'application/json',
                    'Authorization' => $this->buildAuthorizationHeader(),
                    'Content-type' => 'application/json',
                ),
                $this->toJSON($data)
            );

            // NOTE: this is relying on the response being a GuzzleHttp\Psr7\Response
            // Not sure how to rely on something more generic like Psr\Http\Message\ResponseInterface
            $responseJson = json_decode($response->getBody()->getContents(), true);
            // return Response
            return $this->createResponse($responseJson);
        } catch (\Exception $e) {
            throw new InvalidResponseException(
                'Error communicating with payment gateway: ' . $e->getMessage(),
                $e->getCode()
            );
        }

    }

    /**
     * @return json
     */
    public function toJSON($data, $options = 0)
    {
        if (version_compare(phpversion(), '5.4.0', '>=') === true) {
            return json_encode($data, $options | 64);
        }
        return str_replace('\\/', '/', json_encode($data, $options));
    }

    /**
     * @return Response
     */
    protected function createResponse($data)
    {
        return $this->response = new Response($this, $data);
    }

    /**
     * Return string
     */
    protected function buildAuthorizationHeader()
    {
        $merchantId = $this->getMerchantId();
        $merchantSecret = $this->getMerchantSecret();

        return 'Basic ' . base64_encode($merchantId . ':' . $merchantSecret);
    }
}
