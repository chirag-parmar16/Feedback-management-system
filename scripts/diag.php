<?php
$c = new mysqli('localhost', 'root', '', 'userdb');
echo "--- CLASSES ---\n";
$r = $c->query("SELECT id, name, section FROM classes");
while($row = $r->fetch_assoc()) echo "{$row['id']}: {$row['name']} {$row['section']}\n";

echo "\n--- SUBJECTS ---\n";
$r = $c->query("SELECT id, name FROM subjects");
while($row = $r->fetch_assoc()) echo "{$row['id']}: {$row['name']}\n";

echo "\n--- TEACHERS ---\n";
$r = $c->query("SELECT id, username FROM users WHERE role='teacher'");
while($row = $r->fetch_assoc()) echo "{$row['id']}: {$row['username']}\n";
?>
