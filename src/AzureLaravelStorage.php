<?php

namespace Owlfice\AzureLaravelStorage;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;
use Carbon\Carbon;

class AzureLaravelStorage
{
    protected Client $httpClient;
    protected array $config;
    protected string $defaultContainer;
    protected string $accountName;
    protected string $accountKey;
    protected ?string $sasToken;
    protected string $baseUrl;

    public function __construct(array $config = null)
    {
        $this->config = $config ?? config('azure-laravel-storage.connections.azure');
        $this->defaultContainer = $this->config['container'] ?? 'default';
        $this->accountName = $this->config['account_name'];
        $this->accountKey = $this->config['account_key'] ?? null;
        $this->sasToken = $this->config['sas_token'] ?? null;
        $this->baseUrl = $this->config['endpoint'] ?? "https://{$this->accountName}.blob.core.windows.net";
        
        $this->httpClient = new Client([
            'timeout' => $this->config['timeout'] ?? 300,
            'verify' => $this->config['verify_ssl'] ?? true,
        ]);
    }

    /**
     * Upload a file to Azure Blob Storage using REST API
     */
    public function upload(string $path, $content, string $container = null, array $options = []): bool
    {
        try {
            $container = $container ?? $this->defaultContainer;
            
            // Ensure container exists
            $this->createContainerIfNotExists($container);

            // Handle different content types
            if ($content instanceof UploadedFile) {
                $contentType = $content->getMimeType() ?: $this->detectContentType($path);
                $content = file_get_contents($content->getRealPath());
            } elseif (is_string($content) && file_exists($content)) {
                $contentType = mime_content_type($content) ?: $this->detectContentType($path);
                $content = file_get_contents($content);
            } else {
                $contentType = $options['content-type'] ?? $this->detectContentType($path);
            }

            $url = "{$this->baseUrl}/{$container}/{$path}";
            $headers = $this->buildHeaders('PUT', $container, $path, [
                'Content-Type' => $contentType,
                'Content-Length' => strlen($content),
                'x-ms-blob-type' => 'BlockBlob',
                'x-ms-date' => gmdate('D, d M Y H:i:s T'),
                'x-ms-version' => '2023-11-03'
            ]);

            // Add custom metadata
            $metadata = array_merge(
                config('azure-laravel-storage.default_metadata', []),
                $options['metadata'] ?? []
            );
            
            foreach ($metadata as $key => $value) {
                $headers["x-ms-meta-{$key}"] = $value;
            }

            $response = $this->httpClient->put($url, [
                'headers' => $headers,
                'body' => $content
            ]);

            if (config('azure-laravel-storage.logging.enabled', true)) {
                Log::info("File uploaded successfully via REST API", [
                    'path' => $path, 
                    'container' => $container,
                    'size' => strlen($content)
                ]);
            }

            return $response->getStatusCode() === 201;

        } catch (RequestException $e) {
            if (config('azure-laravel-storage.logging.enabled', true)) {
                Log::error("Failed to upload file via REST API", [
                    'path' => $path,
                    'container' => $container,
                    'error' => $e->getMessage(),
                    'response' => $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : null
                ]);
            }
            throw $e;
        }
    }

    /**
     * Download a file from Azure Blob Storage using REST API
     */
    public function download(string $path, string $container = null): string
    {
        try {
            $container = $container ?? $this->defaultContainer;
            $url = "{$this->baseUrl}/{$container}/{$path}";
            
            $headers = $this->buildHeaders('GET', $container, $path, [
                'x-ms-date' => gmdate('D, d M Y H:i:s T'),
                'x-ms-version' => '2023-11-03'
            ]);

            $response = $this->httpClient->get($url, [
                'headers' => $headers
            ]);
            
            return $response->getBody()->getContents();

        } catch (RequestException $e) {
            if (config('azure-laravel-storage.logging.enabled', true)) {
                Log::error("Failed to download file via REST API", [
                    'path' => $path,
                    'container' => $container,
                    'error' => $e->getMessage()
                ]);
            }
            throw $e;
        }
    }

    /**
     * Delete a file from Azure Blob Storage using REST API
     */
    public function delete(string $path, string $container = null): bool
    {
        try {
            $container = $container ?? $this->defaultContainer;
            $url = "{$this->baseUrl}/{$container}/{$path}";
            
            $headers = $this->buildHeaders('DELETE', $container, $path, [
                'x-ms-date' => gmdate('D, d M Y H:i:s T'),
                'x-ms-version' => '2023-11-03'
            ]);

            $response = $this->httpClient->delete($url, [
                'headers' => $headers
            ]);

            if (config('azure-laravel-storage.logging.enabled', true)) {
                Log::info("File deleted successfully via REST API", [
                    'path' => $path, 
                    'container' => $container
                ]);
            }

            return $response->getStatusCode() === 202;

        } catch (RequestException $e) {
            if (config('azure-laravel-storage.logging.enabled', true)) {
                Log::error("Failed to delete file via REST API", [
                    'path' => $path,
                    'container' => $container,
                    'error' => $e->getMessage()
                ]);
            }
            throw $e;
        }
    }

    /**
     * Check if a file exists in Azure Blob Storage using REST API
     */
    public function exists(string $path, string $container = null): bool
    {
        try {
            $container = $container ?? $this->defaultContainer;
            $url = "{$this->baseUrl}/{$container}/{$path}";
            
            $headers = $this->buildHeaders('HEAD', $container, $path, [
                'x-ms-date' => gmdate('D, d M Y H:i:s T'),
                'x-ms-version' => '2023-11-03'
            ]);

            $response = $this->httpClient->head($url, [
                'headers' => $headers
            ]);

            return $response->getStatusCode() === 200;

        } catch (RequestException $e) {
            return false;
        }
    }

    /**
     * Get the URL of a blob
     */
    public function url(string $path, string $container = null): string
    {
        $container = $container ?? $this->defaultContainer;
        $baseUrl = $this->config['url'] ?? $this->baseUrl;

        return sprintf('%s/%s/%s', rtrim($baseUrl, '/'), $container, ltrim($path, '/'));
    }

    /**
     * List blobs in a container using REST API
     */
    public function list(string $container = null, string $prefix = '', int $maxResults = 5000): array
    {
        try {
            $container = $container ?? $this->defaultContainer;
            $queryParams = [
                'restype' => 'container',
                'comp' => 'list',
                'maxresults' => $maxResults
            ];
            
            if ($prefix) {
                $queryParams['prefix'] = $prefix;
            }

            $url = "{$this->baseUrl}/{$container}?" . http_build_query($queryParams);
            
            $headers = $this->buildHeaders('GET', $container, '', [
                'x-ms-date' => gmdate('D, d M Y H:i:s T'),
                'x-ms-version' => '2023-11-03'
            ]);

            $response = $this->httpClient->get($url, [
                'headers' => $headers
            ]);

            $xml = simplexml_load_string($response->getBody()->getContents());
            $blobs = [];

            if (isset($xml->Blobs->Blob)) {
                foreach ($xml->Blobs->Blob as $blob) {
                    $blobs[] = [
                        'name' => (string) $blob->Name,
                        'url' => $this->url((string) $blob->Name, $container),
                        'last_modified' => Carbon::parse((string) $blob->Properties->{'Last-Modified'}),
                        'size' => (int) $blob->Properties->{'Content-Length'},
                        'content_type' => (string) $blob->Properties->{'Content-Type'},
                        'etag' => trim((string) $blob->Properties->Etag, '"'),
                    ];
                }
            }

            return $blobs;

        } catch (RequestException $e) {
            if (config('azure-laravel-storage.logging.enabled', true)) {
                Log::error("Failed to list blobs via REST API", [
                    'container' => $container,
                    'error' => $e->getMessage()
                ]);
            }
            throw $e;
        }
    }

    /**
     * Create container if it doesn't exist using REST API
     */
    public function createContainerIfNotExists(string $container): bool
    {
        try {
            $url = "{$this->baseUrl}/{$container}?restype=container";
            
            $headers = $this->buildHeaders('PUT', $container, '', [
                'x-ms-date' => gmdate('D, d M Y H:i:s T'),
                'x-ms-version' => '2023-11-03',
                'x-ms-blob-public-access' => $this->config['visibility'] === 'public' ? 'container' : 'private'
            ]);

            $response = $this->httpClient->put($url, [
                'headers' => $headers
            ]);

            return in_array($response->getStatusCode(), [201, 409]); // 201 = created, 409 = already exists

        } catch (RequestException $e) {
            // Container might already exist
            if ($e->hasResponse() && $e->getResponse()->getStatusCode() === 409) {
                return true;
            }
            throw $e;
        }
    }

    /**
     * Get blob properties using REST API
     */
    public function getProperties(string $path, string $container = null): array
    {
        try {
            $container = $container ?? $this->defaultContainer;
            $url = "{$this->baseUrl}/{$container}/{$path}";
            
            $headers = $this->buildHeaders('HEAD', $container, $path, [
                'x-ms-date' => gmdate('D, d M Y H:i:s T'),
                'x-ms-version' => '2023-11-03'
            ]);

            $response = $this->httpClient->head($url, [
                'headers' => $headers
            ]);

            $responseHeaders = $response->getHeaders();
            $metadata = [];

            // Extract metadata from headers
            foreach ($responseHeaders as $key => $value) {
                if (strpos(strtolower($key), 'x-ms-meta-') === 0) {
                    $metaKey = substr($key, 10); // Remove 'x-ms-meta-' prefix
                    $metadata[$metaKey] = $value[0] ?? $value;
                }
            }

            return [
                'last_modified' => Carbon::parse($response->getHeaderLine('Last-Modified')),
                'size' => (int) $response->getHeaderLine('Content-Length'),
                'content_type' => $response->getHeaderLine('Content-Type'),
                'etag' => trim($response->getHeaderLine('ETag'), '"'),
                'metadata' => $metadata,
            ];

        } catch (RequestException $e) {
            if (config('azure-laravel-storage.logging.enabled', true)) {
                Log::error("Failed to get blob properties via REST API", [
                    'path' => $path,
                    'container' => $container,
                    'error' => $e->getMessage()
                ]);
            }
            throw $e;
        }
    }

    /**
     * Copy a blob using REST API
     */
    public function copy(string $sourcePath, string $destinationPath, string $sourceContainer = null, string $destinationContainer = null): bool
    {
        try {
            $sourceContainer = $sourceContainer ?? $this->defaultContainer;
            $destinationContainer = $destinationContainer ?? $this->defaultContainer;
            
            $sourceUrl = "{$this->baseUrl}/{$sourceContainer}/{$sourcePath}";
            $destinationUrl = "{$this->baseUrl}/{$destinationContainer}/{$destinationPath}";
            
            $headers = $this->buildHeaders('PUT', $destinationContainer, $destinationPath, [
                'x-ms-date' => gmdate('D, d M Y H:i:s T'),
                'x-ms-version' => '2023-11-03',
                'x-ms-copy-source' => $sourceUrl
            ]);

            $response = $this->httpClient->put($destinationUrl, [
                'headers' => $headers
            ]);

            if (config('azure-laravel-storage.logging.enabled', true)) {
                Log::info("File copied successfully via REST API", [
                    'source' => $sourcePath,
                    'destination' => $destinationPath,
                    'source_container' => $sourceContainer,
                    'destination_container' => $destinationContainer
                ]);
            }

            return $response->getStatusCode() === 202;

        } catch (RequestException $e) {
            if (config('azure-laravel-storage.logging.enabled', true)) {
                Log::error("Failed to copy file via REST API", [
                    'source' => $sourcePath,
                    'destination' => $destinationPath,
                    'error' => $e->getMessage()
                ]);
            }
            throw $e;
        }
    }

    /**
     * Build headers for Azure REST API requests
     */
    protected function buildHeaders(string $method, string $container, string $path, array $additionalHeaders = []): array
    {
        $headers = array_merge([
            'x-ms-date' => gmdate('D, d M Y H:i:s T'),
            'x-ms-version' => '2023-11-03'
        ], $additionalHeaders);

        // If using SAS token, don't add Authorization header
        if ($this->sasToken) {
            return $headers;
        }

        // Generate authorization header using account key
        if ($this->accountKey) {
            $headers['Authorization'] = $this->generateAuthorizationHeader($method, $container, $path, $headers);
        }

        return $headers;
    }

    /**
     * Generate Azure Storage authorization header
     */
    protected function generateAuthorizationHeader(string $method, string $container, string $path, array $headers): string
    {
        $canonicalizedHeaders = $this->buildCanonicalizedHeaders($headers);
        $canonicalizedResource = $this->buildCanonicalizedResource($container, $path);
        
        $stringToSign = implode("\n", [
            strtoupper($method),
            $headers['Content-Encoding'] ?? '',
            $headers['Content-Language'] ?? '',
            $headers['Content-Length'] ?? '',
            $headers['Content-MD5'] ?? '',
            $headers['Content-Type'] ?? '',
            $headers['Date'] ?? '',
            $headers['If-Modified-Since'] ?? '',
            $headers['If-Match'] ?? '',
            $headers['If-None-Match'] ?? '',
            $headers['If-Unmodified-Since'] ?? '',
            $headers['Range'] ?? '',
            $canonicalizedHeaders,
            $canonicalizedResource
        ]);

        $signature = base64_encode(hash_hmac('sha256', $stringToSign, base64_decode($this->accountKey), true));
        
        return "SharedKey {$this->accountName}:{$signature}";
    }

    /**
     * Build canonicalized headers for Azure authorization
     */
    protected function buildCanonicalizedHeaders(array $headers): string
    {
        $canonicalizedHeaders = [];
        
        foreach ($headers as $key => $value) {
            $key = strtolower($key);
            if (strpos($key, 'x-ms-') === 0) {
                $canonicalizedHeaders[$key] = $value;
            }
        }
        
        ksort($canonicalizedHeaders);
        
        $result = [];
        foreach ($canonicalizedHeaders as $key => $value) {
            $result[] = $key . ':' . $value;
        }
        
        return implode("\n", $result);
    }

    /**
     * Build canonicalized resource for Azure authorization
     */
    protected function buildCanonicalizedResource(string $container, string $path): string
    {
        $resource = "/{$this->accountName}/{$container}";
        if ($path) {
            $resource .= "/{$path}";
        }
        return $resource;
    }

    /**
     * Detect content type based on file extension
     */
    protected function detectContentType(string $path): string
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'txt' => 'text/plain',
            'zip' => 'application/zip',
            'mp4' => 'video/mp4',
            'mp3' => 'audio/mpeg',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'html' => 'text/html',
        ];

        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }
}
