<?php
$files = glob("database/migrations/*.php");
foreach ($files as $file) {
    if (strpos(file_get_contents($file), "order_details") !== false) {
        echo "Found in: " . $file . "\n";
    }
}
