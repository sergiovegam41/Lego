<?php
// Test autoloader
require_once __DIR__ . '/../vendor/autoload.php';

echo "Testing autoloader...\n";
echo "Composer autoload file exists: " . (file_exists(__DIR__ . '/../vendor/autoload.php') ? 'YES' : 'NO') . "\n";
echo "Core\\Providers\\Request class exists: " . (class_exists('Core\\Providers\\Request') ? 'YES' : 'NO') . "\n";

if (class_exists('Core\\Providers\\Request')) {
    echo "Request class found!\n";
} else {
    echo "Request class NOT found\n";
    echo "Checking file existence: " . (file_exists(__DIR__ . '/../Core/Providers/Request.php') ? 'YES' : 'NO') . "\n";
}

// Try to instantiate
try {
    $request = new \Core\Providers\Request();
    echo "Request instantiated successfully!\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
