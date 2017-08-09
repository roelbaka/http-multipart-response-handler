<?php
declare(strict_types=1);

namespace Coolblue\MultipartResponseHandler;

use Psr\Http\Message\ResponseInterface;
use Slim\Http\Response;

class ResponseHandler
{
    const DEFAULT_DELIMITER = '|||||||||||||||||||||||||||||||||||||||||||';

    /** @var string */
    private $delimiter;

    public function __construct(string $delimiter = self::DEFAULT_DELIMITER)
    {
        $this->delimiter = $delimiter;
    }

    /**ƒ
     * @param int $status
     * @param ResponseInterface[] $responses
     * @return Response
     */
    public function createMultipartResponse(int $status, array $responses) : Response
    {
        $multipartResponse = (new Response($status))->withHeader('Content-Type', 'multipart/form-data');

        $body = implode($this->delimiter, array_map(function (ResponseInterface $response) : string {
            return (string)$response->getBody();
        }, $responses));

        $multipartResponse->getBody()->write($body);

        return $multipartResponse;
    }

    public function createResponsesFromMultiPartResponse(ResponseInterface $response) : array
    {
        $parts = explode($this->delimiter, (string)$response->getBody());

        return array_map(function (string $part) use ($response) {
            $response=  new Response($response->getStatusCode());
            $response->getBody()->write($part);

            return $response;
        }, $parts);
    }
}