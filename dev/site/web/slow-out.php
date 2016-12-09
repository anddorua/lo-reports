<?php

ini_set('zlib.output_compression', false);

// Set a valid header so browsers pick it up correctly.
header('Content-type: application/octet-stream');

// Explicitly disable caching so Varnish and other upstreams won't cache.
header("Cache-Control: no-cache, must-revalidate");

// Setting this header instructs Nginx to disable fastcgi_buffering and disable
// gzip for this request.
header('X-Accel-Buffering: no');

$string_length = 0;
echo 'Begin test with an ' . $string_length . ' character string...<br />' . "\r\n";

// For 3 seconds, repeat the string.
for ($i = 0; $i < 3; $i++) {
    $string = str_repeat('.', $string_length);
    echo $string . '<br />' . "\r\n";
    echo $i . '<br />' . "\r\n";
    @ob_end_flush();
    flush();
    sleep(1);
}

echo 'End test.<br />' . "\r\n";
?>