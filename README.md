# Azure Lar- 🎯 **Pure REST API Implementation** - Uses official Azure Blob Storage REST API (v2023-11-03)
- 🚀 **Zero Dependencies** - No deprecated Azure SDKs, only GuzzleHTTP for HTTP requests
- 🔐 **Multiple Authentication Methods** - Account Key, SAS Token support
- 📁 **Complete Blob Operations** - Upload, download, delete, list, copy, properties
- 🛡️ **Secure by Design** - Proper Azure authentication headers and request signing
- 📊 **Built-in Logging** - Comprehensive operation logging and error handling
- 🎯 **Automatic Content Type Detection** - Smart MIME type detection
- 📋 **Metadata Management** - Full support for blob metadata operations
- ⚡ **Performance Optimized** - Configurable timeouts and SSL verification
- 🎨 **Laravel Integration** - Facades, service providers, and dependency injection
- 🧪 **Future Proof** - Direct REST API calls ensure longevitye

[![Latest Version on Packagist](https://img.shields.io/packagist/v/owlfice/azure-laravel-storage.svg?style=flat-square)](https://packagist.org/packages/owlfice/azure-laravel-storage)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/owlfice/azure-laravel-storage/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/owlfice/azure-laravel-storage/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/owlfice/azure-laravel-storage/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/owlfice/azure-laravel-storage/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/owlfice/azure-laravel-storage.svg?style=flat-square)](https://packagist.org/packages/owlfice/azure-laravel-storage)

A modern, lightweight Laravel package that provides a comprehensive wrapper for Microsoft Azure Blob Storage using the **official Azure REST API**. Unlike deprecated SDK packages, this implementation directly uses Azure's REST endpoints, ensuring long-term compatibility and maximum performance.

## Features

- 🎯 **Pure REST API Implementation** - Uses official Azure Blob Storage REST API (v2023-11-03)
- 🚀 **Zero Dependencies** - No deprecated Azure SDKs, only GuzzleHTTP for HTTP requests
- 🔐 **Multiple Authentication Methods** - Account Key, SAS Token support
- 📁 **Complete Blob Operations** - Upload, download, delete, list, copy, properties
- �️ **Secure by Design** - Proper Azure authentication headers and request signing
- �📊 **Built-in Logging** - Comprehensive operation logging and error handling
- 🎯 **Automatic Content Type Detection** - Smart MIME type detection
- 📋 **Metadata Management** - Full support for blob metadata operations
- ⚡ **Performance Optimized** - Configurable timeouts and SSL verification
- 🎨 **Laravel Integration** - Facades, service providers, and dependency injection
- 🧪 **Future Proof** - Direct REST API calls ensure longevity

## Installation

You can install the package via composer:

```bash
composer require owlfice/azure-laravel-storage
```

## Configuration

Publish the config file with:

```bash
php artisan vendor:publish --tag="azure-laravel-storage-config"
```

Add your Azure Storage credentials to your `.env` file:

```env
AZURE_STORAGE_ACCOUNT_NAME=your_storage_account_name
AZURE_STORAGE_ACCOUNT_KEY=your_storage_account_key
AZURE_STORAGE_CONTAINER=your_default_container
AZURE_STORAGE_URL=https://your_storage_account_name.blob.core.windows.net/
AZURE_STORAGE_TIMEOUT=300
AZURE_STORAGE_VERIFY_SSL=true
```

## Why REST API over Deprecated SDKs?

This package uses the **official Azure Blob Storage REST API** instead of deprecated Microsoft Azure SDK packages because:

- ✅ **Always Up-to-Date**: Direct REST API calls ensure compatibility with latest Azure features
- ✅ **No Deprecated Dependencies**: Avoids abandoned Microsoft Azure SDK packages
- ✅ **Lightweight**: Only depends on GuzzleHTTP for HTTP requests
- ✅ **Performance**: Direct HTTP requests without SDK overhead
- ✅ **Transparency**: Clear understanding of all API calls being made
- ✅ **Future Proof**: REST API endpoints have long-term Microsoft support

## Supported Azure REST API Operations

This package implements the following Azure Blob Storage REST API endpoints:

- **PUT Blob** - Upload files and content
- **GET Blob** - Download blob content  
- **DELETE Blob** - Remove blobs
- **HEAD Blob** - Check existence and get properties
- **List Blobs** - Enumerate blobs in containers
- **Copy Blob** - Server-side blob copying
- **PUT Container** - Create containers
- **Blob Properties** - Get detailed blob metadata

All operations use proper Azure authentication headers and request signing.
```

## Usage

### Basic File Operations

#### Upload a file

```php
use Owlfice\AzureLaravelStorage\Facades\AzureLaravelStorage;

// Upload from file path
AzureLaravelStorage::upload('documents/file.pdf', '/path/to/local/file.pdf');

// Upload from UploadedFile (in a controller)
AzureLaravelStorage::upload('images/photo.jpg', $request->file('photo'));

// Upload to specific container
AzureLaravelStorage::upload('documents/file.pdf', $fileContent, 'my-container');
```

#### Download a file

```php
// Download file content
$content = AzureLaravelStorage::download('documents/file.pdf');

// Save to local file
file_put_contents('/local/path/file.pdf', $content);
```

#### Check if file exists

```php
if (AzureLaravelStorage::exists('documents/file.pdf')) {
    // File exists
}
```

#### Delete a file

```php
AzureLaravelStorage::delete('documents/file.pdf');
```

#### Get file URL

```php
$url = AzureLaravelStorage::url('documents/file.pdf');
```

### Advanced Operations

#### List files in container

```php
// List all files
$files = AzureLaravelStorage::list();

// List files with prefix
$files = AzureLaravelStorage::list('my-container', 'documents/');

foreach ($files as $file) {
    echo $file['name'] . ' - ' . $file['size'] . ' bytes';
}
```

#### Get file properties

```php
$properties = AzureLaravelStorage::getProperties('documents/file.pdf');
echo 'Size: ' . $properties['size'];
echo 'Last Modified: ' . $properties['last_modified']->format('Y-m-d H:i:s');
```

#### Copy files

```php
// Copy within same container
AzureLaravelStorage::copy('source/file.pdf', 'destination/file.pdf');

// Copy between containers
AzureLaravelStorage::copy('file.pdf', 'file.pdf', 'source-container', 'destination-container');
```

### Using Dependency Injection

```php
use Owlfice\AzureLaravelStorage\AzureLaravelStorage;

class DocumentController extends Controller
{
    public function store(Request $request, AzureLaravelStorage $storage)
    {
        $file = $request->file('document');
        $path = 'documents/' . $file->getClientOriginalName();
        
        $storage->upload($path, $file);
        
        return response()->json(['url' => $storage->url($path)]);
    }
}
```

### Custom Configuration

You can also create instances with custom configuration:

```php
$customStorage = new AzureLaravelStorage([
    'account_name' => 'different_account',
    'account_key' => 'different_key',
    'container' => 'different_container'
]);

$customStorage->upload('file.pdf', $content);
```

## Configuration Options

The package provides extensive configuration options:

- **Multiple Authentication Methods**: Account Key, SAS Token
- **Blob Settings**: Chunk size, retry attempts, timeouts
- **Security**: File type validation, size limits, HTTPS enforcement
- **Logging**: Comprehensive operation logging
- **Caching**: Metadata and container information caching
- **CDN Integration**: Azure CDN support

See the [configuration file](config/azure-laravel-storage.php) for all available options.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Owlfice](https://github.com/owlfice)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
