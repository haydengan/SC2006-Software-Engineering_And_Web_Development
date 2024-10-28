<?php
// get_route.php

// Get parameters from GET request
$start = isset($_GET['start']) ? $_GET['start'] : '';
$end = isset($_GET['end']) ? $_GET['end'] : '';
$routeType = isset($_GET['routeType']) ? $_GET['routeType'] : '';
$date = isset($_GET['date']) ? $_GET['date'] : '';
$time = isset($_GET['time']) ? $_GET['time'] : '';
$mode = isset($_GET['mode']) ? $_GET['mode'] : '';

if (empty($start) || empty($end) || empty($routeType)) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required parameters']);
    exit;
}

// Build the URL for the OneMap API request
$url = 'https://www.onemap.gov.sg/api/public/routingsvc/route?' .
    'start=' . urlencode($start) .
    '&end=' . urlencode($end) .
    '&routeType=' . urlencode($routeType);

// For public transport, add additional parameters
if ($routeType === 'pt') {
    if (empty($date) || empty($time) || empty($mode)) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required parameters for public transport']);
        exit;
    }
    $url .= '&date=' . urlencode($date) .
        '&time=' . urlencode($time) .
        '&mode=' . urlencode($mode);
}

// OneMap API token
$token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOiJkMjI0NTEwOTFlY2JjNTA0NjIyYmU5YjJhMjVjNzg3ZiIsImlzcyI6Imh0dHA6Ly9pbnRlcm5hbC1hbGItb20tcHJkZXppdC1pdC1uZXctMTYzMzc5OTU0Mi5hcC1zb3V0aGVhc3QtMS5lbGIuYW1hem9uYXdzLmNvbS9hcGkvdjIvdXNlci9wYXNzd29yZCIsImlhdCI6MTcyOTU3ODg0OSwiZXhwIjoxNzI5ODM4MDQ5LCJuYmYiOjE3Mjk1Nzg4NDksImp0aSI6IkpkU0xEa1ZKbDNYcEtCWFMiLCJ1c2VyX2lkIjo0OTY1LCJmb3JldmVyIjpmYWxzZX0.OOuPpiDgsOA2_aapeIzy7QGCgw549qCj56RUqDFEvpc'; // Replace with your token

// Initialize cURL
$ch = curl_init($url);

// Set cURL options
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: ' . $token
]);

// Execute the request
$response = curl_exec($ch);

// Check for cURL errors
if ($response === false) {
    $error = curl_error($ch);
    curl_close($ch);
    http_response_code(500);
    echo json_encode(['error' => 'cURL error: ' . $error]);
    exit;
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Set the response code
http_response_code($httpCode);

// Return the response
header('Content-Type: application/json');
echo $response;
?>
