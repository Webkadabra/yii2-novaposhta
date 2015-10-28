<?php
namespace jones\novaposhta\components\http;

use Yii;
use yii\base\Exception;
use yii\helpers\Json;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;
use Psr\Http\Message\ResponseInterface;
use jones\novaposhta\components\HttpClientInterface;
use jones\novaposhta\components\RequestInterface;

/**
 * Class Client
 * @package jones\novaposhta\components\http
 */
class Client implements HttpClientInterface
{
    const CONTENT_TYPE = 'text/xml';

    const BASE_URL = 'http://orders.novaposhta.ua/xml.php';

    /**
     * @var \GuzzleHttp\Client
     */
    private $client;

    public function __construct(GuzzleClient $httpClient)
    {
        $this->client = $httpClient;
    }

    /**
     * Execute http request
     * @param RequestInterface $request
     * @return array
     * @throws HttpClientException
     */
    public function execute(RequestInterface $request)
    {
        $options = [
            'headers' => [
                'content-type' => self::CONTENT_TYPE
            ],
            'body' => $request->getBody()
        ];
        try {
            $response = $this->client->post(self::BASE_URL, $options);
        } catch (ClientException $e) {
            Yii::error($e->getRequest());
            if ($e->hasResponse()) {
                Yii::error($e->getResponse());
            }
            throw new HttpClientException($e->getMessage());
        }
        Yii::trace($response);
        return $this->prepareResponse($response);
    }

    /**
     * Convert string xml response to array
     * @param ResponseInterface $response
     * @return array
     */
    private function prepareResponse(ResponseInterface $response)
    {
        $document = new \SimpleXMLElement($response->getBody());
        return Json::decode(Json::encode((array)$document), true);
    }
}

/**
 * Class HttpClientException
 * @package jones\novaposhta\components\http
 */
class HttpClientException extends Exception{}