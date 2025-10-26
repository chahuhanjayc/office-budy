<?php
require_once 'config/config.php';

$equipment = new Equipment($database->getConnection());
$stmt = $equipment->read();

echo "<h3>Equipment in Database:</h3>";
$count = 0;
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $count++;
    echo "ID: {$row['id']} - {$row['name']} - {$row['status']}<br>";
}
echo "<h4>Total Equipment: $count</h4>";
?>