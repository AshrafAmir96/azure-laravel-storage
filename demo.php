<?php

/**
 * Azure Laravel Storage REST API Demo
 * 
 * This example demonstrates how to use the package with Azure's REST API
 */

require_once 'vendor/autoload.php';

use Owlfice\AzureLaravelStorage\AzureLaravelStorage;

// Configuration for Azure Storage using REST API
$config = [
    'account_name' => 'your_storage_account',
    'account_key' => 'your_account_key_here',
    'container' => 'demo-container',
    'endpoint' => 'https://your_storage_account.blob.core.windows.net',
    'timeout' => 300,
    'verify_ssl' => true
];

// Alternative: SAS Token configuration
$sasConfig = [
    'account_name' => 'your_storage_account',
    'sas_token' => 'your_sas_token_here',
    'container' => 'demo-container',
    'endpoint' => 'https://your_storage_account.blob.core.windows.net',
];

try {
    // Initialize the Azure Storage client
    $storage = new AzureLaravelStorage($config);
    
    echo "🚀 Azure Laravel Storage REST API Demo\n";
    echo "=====================================\n\n";
    
    // 1. Create container (if it doesn't exist)
    echo "📁 Creating container...\n";
    $containerCreated = $storage->createContainerIfNotExists('demo-container');
    echo $containerCreated ? "✅ Container ready\n\n" : "❌ Container creation failed\n\n";
    
    // 2. Upload a file
    echo "⬆️  Uploading file...\n";
    $uploadContent = "Hello from Azure REST API! " . date('Y-m-d H:i:s');
    $uploadResult = $storage->upload('demo-file.txt', $uploadContent, null, [
        'metadata' => [
            'created_by' => 'rest_api_demo',
            'timestamp' => time()
        ]
    ]);
    echo $uploadResult ? "✅ File uploaded successfully\n\n" : "❌ Upload failed\n\n";
    
    // 3. Check if file exists
    echo "🔍 Checking if file exists...\n";
    $exists = $storage->exists('demo-file.txt');
    echo $exists ? "✅ File exists\n\n" : "❌ File not found\n\n";
    
    // 4. Get file properties
    if ($exists) {
        echo "📋 Getting file properties...\n";
        $properties = $storage->getProperties('demo-file.txt');
        echo "   Size: " . $properties['size'] . " bytes\n";
        echo "   Content Type: " . $properties['content_type'] . "\n";
        echo "   Last Modified: " . $properties['last_modified']->format('Y-m-d H:i:s') . "\n";
        echo "   ETag: " . $properties['etag'] . "\n";
        if (!empty($properties['metadata'])) {
            echo "   Metadata:\n";
            foreach ($properties['metadata'] as $key => $value) {
                echo "     {$key}: {$value}\n";
            }
        }
        echo "\n";
    }
    
    // 5. Download the file
    echo "⬇️  Downloading file...\n";
    $downloadedContent = $storage->download('demo-file.txt');
    echo "✅ Downloaded content: " . $downloadedContent . "\n\n";
    
    // 6. Get file URL
    echo "🔗 Getting file URL...\n";
    $url = $storage->url('demo-file.txt');
    echo "✅ File URL: " . $url . "\n\n";
    
    // 7. Copy the file
    echo "📄 Copying file...\n";
    $copyResult = $storage->copy('demo-file.txt', 'demo-file-copy.txt');
    echo $copyResult ? "✅ File copied successfully\n\n" : "❌ Copy failed\n\n";
    
    // 8. List all files in container
    echo "📂 Listing files in container...\n";
    $files = $storage->list();
    echo "✅ Found " . count($files) . " files:\n";
    foreach ($files as $file) {
        echo "   - {$file['name']} ({$file['size']} bytes, {$file['content_type']})\n";
    }
    echo "\n";
    
    // 9. Upload different file types
    echo "📁 Testing different file types...\n";
    
    // JSON file
    $jsonData = json_encode(['message' => 'Hello from REST API', 'timestamp' => time()]);
    $storage->upload('data.json', $jsonData);
    echo "✅ JSON file uploaded\n";
    
    // HTML file
    $htmlContent = '<html><body><h1>Hello Azure REST API</h1></body></html>';
    $storage->upload('page.html', $htmlContent);
    echo "✅ HTML file uploaded\n";
    
    // CSS file
    $cssContent = 'body { font-family: Arial, sans-serif; color: #333; }';
    $storage->upload('styles.css', $cssContent);
    echo "✅ CSS file uploaded\n\n";
    
    // 10. List all files again
    echo "📂 Final file listing...\n";
    $allFiles = $storage->list();
    echo "✅ Total files: " . count($allFiles) . "\n";
    foreach ($allFiles as $file) {
        echo "   - {$file['name']} ({$file['content_type']})\n";
    }
    echo "\n";
    
    // 11. Cleanup (optional - uncomment to delete test files)
    /*
    echo "🧹 Cleaning up test files...\n";
    $storage->delete('demo-file.txt');
    $storage->delete('demo-file-copy.txt');
    $storage->delete('data.json');
    $storage->delete('page.html');
    $storage->delete('styles.css');
    echo "✅ Cleanup completed\n\n";
    */
    
    echo "🎉 Demo completed successfully!\n";
    echo "\nThis demo used pure Azure REST API calls without any deprecated SDKs.\n";
    echo "All operations were performed using HTTP requests with proper Azure authentication.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
