<?php
$d = json_encode(['title' => 'Beli Susu']);
$o = [
    'http' => [
        'header' => "Content-Type: application/json\r\n",
        'method' => 'POST',
        'content' => $d,
        'ignore_errors' => true // PENTING: Jangan lempar error kalau status != 200
    ]
];
$resp = file_get_contents('http://localhost:8000/api/v1/tasks?api_key=5f4d4839ec623fd5', false, stream_context_create($o));
echo $resp;
