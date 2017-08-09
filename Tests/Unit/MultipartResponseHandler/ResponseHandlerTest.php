<?php
declare(strict_types=1);

namespace Coolblue\MultipartResponseHandler\Tests\Unit;

use Coolblue\MultipartResponseHandler\ResponseHandler;
use PHPUnit\Framework\TestCase;
use Slim\Http\Response;

class ResponseHandlerTest extends TestCase
{
    const DELIMITER = '||';

    /** @var ResponseHandler */
    private $responseHandler;

    public function setUp()
    {
        $this->responseHandler = new ResponseHandler(self::DELIMITER);
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
        $this->assertEquals(
            'content1' . self::DELIMITER . 'content2' . self::DELIMITER . 'content3',
            (string)$multiPartResponse->getBody()
        );
    }

    public function testCreateResponsesFromMultiPartResponse()
    {
        $multipartResponse = new Response();
        $multipartResponse->getBody()->write(
            'content1' . self::DELIMITER . 'content2' . self::DELIMITER . 'content3'
        );

        $responses = $this->responseHandler->createResponsesFromMultiPartResponse($multipartResponse);

        $this->assertEquals(3, count($responses));

        for ($i = 1; $i <= 3; $i++) {
            $this->assertEquals('content' . $i, $responses[$i-1]->getBody());
        }
    }
}
