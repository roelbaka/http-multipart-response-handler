<?php
declare(strict_types=1);

namespace Coolblue\MultipartResponseHandler;

use Psr\Http\Message\ResponseInterface;
use Slim\Http\Response;

class ResponseHandler
{
    const DEFAULT_BOUNDARY = '||||';

    /** @var string */
    private $boundary;

    public function __construct(string $boundary = self::DEFAULT_BOUNDARY)
    {
        $this->boundary = $boundary;
    }

    /**
     * @param int $status
     * @param ResponseInterface[] $responses
     * @return Response
     */
    public function createMultipartResponse(int $status, array $responses) : Response
    {
        $multipartResponse = (new Response($status))
            ->withHeader('Content-Type', ['multipart/mixed', 'boundary=' . $this->boundary]);

        $body = implode('', array_map(function (ResponseInterface $response) : string {
            return '--' . $this->boundary . Response::EOL . $response->getBody() . Response::EOL;
        }, $responses));

        $multipartResponse->getBody()->write($body . '--' . $this->boundary . '--');

        return $multipartResponse;
    }

    public function createResponsesFromMultiPartResponse(ResponseInterface $response) : array
    {
        list($key, $boundary) = explode('=', $response->getHeader('Content-Type')[1]);

        $parts = explode($boundary, (string)$response->getBody());

        return array_map(function (string $part) use ($response) {
            $response=  new Response($response->getStatusCode());
            $response->getBody()->write($part);

            return $response;
        }, $parts);
    }
}