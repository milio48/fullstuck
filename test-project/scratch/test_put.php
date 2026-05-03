<?php
$d = json_encode(['status' => 'done']);
$o = [
    'http' => [
        'header' => "Content-Type: application/json\r\n",
        'method' => 'PUT',
        'content' => $d,
        'ignore_errors' => true
    ]
];
// Update task ID 2
$resp = file_get_contents('http://localhost:8000/api/v1/tasks/2?api_key=5f4d4839ec623fd5', false, stream_context_create($o));
echo $resp;
