<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FriendLink Setup Verification</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen p-8">

<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-lg p-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">
            <span class="text-blue-600">Friend</span><span class="text-purple-600">Link</span> Setup Verification
        </h1>

        <?php
        $checks = [];
        
        // Check 1: PHP Version
        $phpVersion = phpversion();
        $checks[] = [
            'name' => 'PHP Version',
            'status' => version_compare($phpVersion, '7.4.0', '>='),
            'message' => "PHP $phpVersion " . (version_compare($phpVersion, '7.4.0', '>=') ? '✓' : '✗ (Requires 7.4+)'),
        ];

        // Check 2: PDO Extension
        $pdoLoaded = extension_loaded('pdo');
        $checks[] = [
            'name' => 'PDO Extension',
            'status' => $pdoLoaded,
            'message' => $pdoLoaded ? 'Loaded ✓' : 'Not loaded ✗',
        ];

        // Check 3: PDO_ODBC Extension
        $pdoOdbcLoaded = extension_loaded('pdo_odbc');
        $checks[] = [
            'name' => 'PDO_ODBC Extension',
            'status' => $pdoOdbcLoaded,
            'message' => $pdoOdbcLoaded ? 'Loaded ✓' : 'Not loaded ✗ (Enable in php.ini)',
        ];

        // Check 4: ODBC Drivers
        $odbcDrivers = [];
        if (function_exists('odbc_drivers')) {
            $drivers = @odbc_drivers();
            if ($drivers) {
                while ($driver = @odbc_fetch_array($drivers)) {
                    $odbcDrivers[] = $driver['Description'] ?? $driver[0];
                }
            }
        }
        
        $hasOdbcDriver = false;
        $driverMessage = 'No ODBC drivers found';
        
        if (!empty($odbcDrivers)) {
            $driverMessage = 'Found: ' . implode(', ', $odbcDrivers);
            $hasOdbcDriver = in_array('ODBC Driver 17 for SQL Server', $odbcDrivers) || 
                             in_array('SQL Server', $odbcDrivers);
        }
        
        $checks[] = [
            'name' => 'ODBC Driver for SQL Server',
            'status' => $hasOdbcDriver,
            'message' => $driverMessage,
        ];

        // Check 5: Database Connection
        $dbConnected = false;
        $dbMessage = '';
        
        try {
            $dsn = "odbc:Driver={ODBC Driver 17 for SQL Server};Server=(localdb)\\MSSQLLocalDB;Database=friendlink;Trusted_Connection=yes;";
            $conn = new PDO($dsn);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $dbConnected = true;
            $dbMessage = 'Connected to friendlink database ✓';
            
            // Check tables
            $stmt = $conn->query("SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE'");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $tableCount = $result['count'];
            
            $checks[] = [
                'name' => 'Database Tables',
                'status' => $tableCount > 0,
                'message' => "$tableCount tables found " . ($tableCount > 0 ? '✓' : '✗'),
            ];
            
        } catch (PDOException $e) {
            $dbMessage = 'Connection failed: ' . $e->getMessage();
        }
        
        $checks[] = [
            'name' => 'Database Connection',
            'status' => $dbConnected,
            'message' => $dbMessage,
        ];

        // Check 6: Config File
        $configExists = file_exists('config.php');
        $checks[] = [
            'name' => 'Config File',
            'status' => $configExists,
            'message' => $configExists ? 'config.php exists ✓' : 'config.php not found ✗',
        ];

        // Check 7: Required Directories
        $ajaxDir = is_dir('ajax');
        $includesDir = is_dir('includes');
        
        $checks[] = [
            'name' => 'Required Directories',
            'status' => $ajaxDir && $includesDir,
            'message' => ($ajaxDir ? 'ajax/ ✓ ' : 'ajax/ ✗ ') . ($includesDir ? 'includes/ ✓' : 'includes/ ✗'),
        ];

        // Display Results
        $allPassed = true;
        foreach ($checks as $check) {
            $allPassed = $allPassed && $check['status'];
            
            $bgColor = $check['status'] ? 'bg-green-50' : 'bg-red-50';
            $textColor = $check['status'] ? 'text-green-700' : 'text-red-700';
            $borderColor = $check['status'] ? 'border-green-200' : 'border-red-200';
            
            echo "<div class='mb-4 p-4 rounded-lg border $bgColor $borderColor'>";
            echo "<div class='flex items-center justify-between'>";
            echo "<span class='font-semibold $textColor'>{$check['name']}</span>";
            echo "<span class='text-sm $textColor'>{$check['message']}</span>";
            echo "</div>";
            echo "</div>";
        }

        // Overall Status
        if ($allPassed) {
            echo "<div class='mt-8 p-6 bg-green-50 border-2 border-green-500 rounded-xl'>";
            echo "<h2 class='text-2xl font-bold text-green-800 mb-2'>✓ All Checks Passed!</h2>";
            echo "<p class='text-green-700 mb-4'>Your FriendLink setup is ready to go.</p>";
            echo "<a href='index.php' class='inline-block px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium'>";
            echo "Go to FriendLink →";
            echo "</a>";
            echo "</div>";
        } else {
            echo "<div class='mt-8 p-6 bg-red-50 border-2 border-red-500 rounded-xl'>";
            echo "<h2 class='text-2xl font-bold text-red-800 mb-2'>✗ Setup Incomplete</h2>";
            echo "<p class='text-red-700 mb-4'>Please fix the issues above before proceeding.</p>";
            echo "<div class='text-sm text-red-600'>";
            echo "<p class='font-semibold mb-2'>Common Solutions:</p>";
            echo "<ul class='list-disc list-inside space-y-1'>";
            echo "<li>Enable PDO_ODBC in php.ini: <code>extension=pdo_odbc</code></li>";
            echo "<li>Install ODBC Driver 17 for SQL Server</li>";
            echo "<li>Create database: <code>CREATE DATABASE friendlink;</code></li>";
            echo "<li>Run schema.sql file in SQL Server Management Studio</li>";
            echo "<li>Ensure LocalDB is running: <code>sqllocaldb start MSSQLLocalDB</code></li>";
            echo "</ul>";
            echo "</div>";
            echo "</div>";
        }
        ?>

        <!-- Additional Info -->
        <div class="mt-8 p-6 bg-blue-50 border border-blue-200 rounded-xl">
            <h3 class="text-lg font-semibold text-blue-900 mb-3">📋 System Information</h3>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="font-medium text-blue-800">PHP Version:</span>
                    <span class="text-blue-700"> <?= phpversion() ?></span>
                </div>
                <div>
                    <span class="font-medium text-blue-800">Server Software:</span>
                    <span class="text-blue-700"> <?= $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ?></span>
                </div>
                <div>
                    <span class="font-medium text-blue-800">PHP Extensions:</span>
                    <span class="text-blue-700"> <?= count(get_loaded_extensions()) ?> loaded</span>
                </div>
                <div>
                    <span class="font-medium text-blue-800">Memory Limit:</span>
                    <span class="text-blue-700"> <?= ini_get('memory_limit') ?></span>
                </div>
            </div>
        </div>

        <!-- Help Links -->
        <div class="mt-6 text-center text-sm text-gray-600">
            <p>Need help? Check the <a href="README-SQLSERVER.md" class="text-blue-600 hover:underline">Setup Documentation</a></p>
        </div>
    </div>
</div>

</body>
</html>
