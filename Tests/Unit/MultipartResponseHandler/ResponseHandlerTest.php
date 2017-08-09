<?php
declare(strict_types=1);

namespace Coolblue\MultipartResponseHandler\Tests\Unit;

use Coolblue\MultipartResponseHandler\ResponseHandler;
use PHPUnit\Framework\TestCase;
use Slim\Http\Response;

class ResponseHandlerTest extends TestCase
{
    const BOUNDARY = '||||';

    /** @var ResponseHandler */
    private $responseHandler;

    public function setUp()
    {
        $this->responseHandler = new ResponseHandler(self::BOUNDARY);
    }

    public function testCreateMultipartResponseShouldReturnResponseContainingAllTheResponsesContent()
    {
        $responses = [];

        for ($i = 1; $i <= 3; $i++) {
            $response = new Response();
            $response->getBody()->write('content' . $i);
            $responses[] = $response;
        }

        $multiPartResponse = $this->responseHandler->createMultipartResponse(200, $responses);

        $this->assertInstanceOf(Response::class, $multiPartResponse);
        $this->assertEquals("multipart/mixed", $multiPartResponse->getHeader('Content-Type')[0]);
        $this->assertEquals("boundary=" . self::BOUNDARY, $multiPartResponse->getHeader('Content-Type')[1]);
        $this->assertEquals(
            "--||||" . Response::EOL . "content1" . Response::EOL .
            "--||||" . Response::EOL . "content2" . Response::EOL .
            "--||||" . Response::EOL . "content3" . Response::EOL .
            "--||||--",
            (string)$multiPartResponse->getBody()
        );
    }

    public function testCreateResponsesFromMultiPartResponse()
    {
        $multipartResponse = (new Response())->withHeader(
            'Content-Type',
            ['multipart/mixed', 'boundary=' . self::BOUNDARY]
        );
        $multipartResponse->getBody()->write(
            'content1' . self::BOUNDARY . 'content2' . self::BOUNDARY . 'content3'
        );

        $responses = $this->responseHandler->createResponsesFromMultiPartResponse($multipartResponse);

        $this->assertEquals(3, count($responses));

        for ($i = 1; $i <= 3; $i++) {
            $this->assertEquals('content' . $i, $responses[$i-1]->getBody());
        }
    }
}
