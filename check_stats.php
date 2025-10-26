<?php
require_once 'config/config.php';

$equipment = new Equipment($database->getConnection());
$stats = $equipment->getStats();

echo "<h3>Equipment Statistics:</h3>";
echo "<pre>";
print_r($stats);
echo "</pre>";
?>