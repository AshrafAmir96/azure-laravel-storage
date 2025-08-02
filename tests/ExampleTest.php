<?php

namespace Owlfice\AzureLaravelStorage\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Owlfice\AzureLaravelStorage\AzureLaravelStorage;

class ExampleTest extends TestCase
{
    protected AzureLaravelStorage $storage;

    protected MockHandler $mockHandler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockHandler = new MockHandler;
        $handlerStack = HandlerStack::create($this->mockHandler);
        $httpClient = new Client(['handler' => $handlerStack]);

        $config = [
            'account_name' => 'testaccount',
            'account_key' => base64_encode('test-key'),
            'container' => 'test-container',
            'endpoint' => 'https://testaccount.blob.core.windows.net',
            'visibility' => 'public',
            'timeout' => 300,
            'verify_ssl' => true,
        ];

        $this->storage = new AzureLaravelStorage($config);

        // Use reflection to replace the HTTP client with our mock
        $reflection = new \ReflectionClass($this->storage);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($this->storage, $httpClient);
    }

    /** @test */
    public function it_can_upload_a_file_via_rest_api()
    {
        // Mock successful container creation and file upload
        $this->mockHandler->append(
            new Response(201), // Container creation response
            new Response(201)  // File upload response
        );

        $result = $this->storage->upload('test-file.txt', 'Hello, Azure REST API!');

        $this->assertTrue($result);
    }

    /** @test */
    public function it_can_download_a_file_via_rest_api()
    {
        $this->mockHandler->append(
            new Response(200, [], 'Hello, Azure REST API!')
        );

        $content = $this->storage->download('test-file.txt');

        $this->assertEquals('Hello, Azure REST API!', $content);
    }

    /** @test */
    public function it_can_check_if_file_exists_via_rest_api()
    {
        $this->mockHandler->append(
            new Response(200, ['Content-Length' => '20'])
        );

        $exists = $this->storage->exists('test-file.txt');

        $this->assertTrue($exists);
    }

    /** @test */
    public function it_can_delete_a_file_via_rest_api()
    {
        $this->mockHandler->append(
            new Response(202)
        );

        $result = $this->storage->delete('test-file.txt');

        $this->assertTrue($result);
    }

    /** @test */
    public function it_can_list_blobs_via_rest_api()
    {
        $xmlResponse = '<?xml version="1.0" encoding="utf-8"?>
        <EnumerationResults>
            <Blobs>
                <Blob>
                    <Name>test-file.txt</Name>
                    <Properties>
                        <Last-Modified>Mon, 01 Jan 2024 00:00:00 GMT</Last-Modified>
                        <Content-Length>20</Content-Length>
                        <Content-Type>text/plain</Content-Type>
                        <Etag>"0x8D123456789ABCD"</Etag>
                    </Properties>
                </Blob>
            </Blobs>
        </EnumerationResults>';

        $this->mockHandler->append(
            new Response(200, [], $xmlResponse)
        );

        $blobs = $this->storage->list();

        $this->assertCount(1, $blobs);
        $this->assertEquals('test-file.txt', $blobs[0]['name']);
        $this->assertEquals(20, $blobs[0]['size']);
        $this->assertEquals('text/plain', $blobs[0]['content_type']);
    }

    /** @test */
    public function it_can_get_blob_properties_via_rest_api()
    {
        $this->mockHandler->append(
            new Response(200, [
                'Content-Length' => '20',
                'Content-Type' => 'text/plain',
                'Last-Modified' => 'Mon, 01 Jan 2024 00:00:00 GMT',
                'ETag' => '"0x8D123456789ABCD"',
                'x-ms-meta-uploaded-by' => 'Laravel',
                'x-ms-meta-environment' => 'testing',
            ])
        );

        $properties = $this->storage->getProperties('test-file.txt');

        $this->assertEquals(20, $properties['size']);
        $this->assertEquals('text/plain', $properties['content_type']);
        $this->assertEquals('0x8D123456789ABCD', $properties['etag']);
        $this->assertArrayHasKey('uploaded-by', $properties['metadata']);
        $this->assertEquals('Laravel', $properties['metadata']['uploaded-by']);
    }

    /** @test */
    public function it_can_copy_blobs_via_rest_api()
    {
        $this->mockHandler->append(
            new Response(202)
        );

        $result = $this->storage->copy('source-file.txt', 'destination-file.txt');

        $this->assertTrue($result);
    }

    /** @test */
    public function it_generates_correct_blob_urls()
    {
        $url = $this->storage->url('test-file.txt');

        $this->assertEquals(
            'https://testaccount.blob.core.windows.net/test-container/test-file.txt',
            $url
        );
    }

    /** @test */
    public function it_detects_content_types_correctly()
    {
        $reflection = new \ReflectionClass($this->storage);
        $method = $reflection->getMethod('detectContentType');
        $method->setAccessible(true);

        $this->assertEquals('image/jpeg', $method->invoke($this->storage, 'test.jpg'));
        $this->assertEquals('application/pdf', $method->invoke($this->storage, 'document.pdf'));
        $this->assertEquals('text/plain', $method->invoke($this->storage, 'readme.txt'));
        $this->assertEquals('application/json', $method->invoke($this->storage, 'data.json'));
        $this->assertEquals('application/octet-stream', $method->invoke($this->storage, 'unknown.xyz'));
    }

    /** @test */
    public function it_can_create_containers_via_rest_api()
    {
        $this->mockHandler->append(
            new Response(201) // Successful container creation
        );

        $result = $this->storage->createContainerIfNotExists('new-container');

        $this->assertTrue($result);
    }

    /** @test */
    public function it_handles_existing_containers_gracefully()
    {
        $this->mockHandler->append(
            new Response(409) // Container already exists
        );

        $result = $this->storage->createContainerIfNotExists('existing-container');

        $this->assertTrue($result);
    }
}
