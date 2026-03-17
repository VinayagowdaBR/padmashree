<?php
$db = new mysqli('localhost', 'root', '', 'siddhranscrm_db');
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}
$res = $db->query("SELECT name, value FROM tbloptions WHERE name IN ('invoice_number_format', 'next_invoice_number', 'invoice_prefix')");
while ($row = $res->fetch_assoc()) {
    echo $row['name'] . ": " . $row['value'] . "\n";
}
