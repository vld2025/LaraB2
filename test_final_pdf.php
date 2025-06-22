<?php
// Test finale standalone
$base64 = file_get_contents('storage/app/resized_test.txt');

$html = '<!DOCTYPE html>
<html><head><meta charset="UTF-8"></head>
<body>
<h1>Test Finale</h1>
<p>Se vedi l\'immagine sotto, il sistema funziona:</p>
<img src="data:image/jpeg;base64,' . $base64 . '" style="border: 2px solid red;">
<p>Fine test</p>
</body></html>';

echo $html;
