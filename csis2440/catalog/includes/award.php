<?php
// award.php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['access']) || $_SESSION['access'] !== 'granted') {
  http_response_code(401);
  echo json_encode(['ok' => false, 'error' => 'unauthorized']);
  exit;
}

$body = json_decode(file_get_contents('php://input'), true);
if (!is_array($body)) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'bad_json']);
  exit;
}

$bounces  = isset($body['bounces'])  ? (int)$body['bounces']  : 0;
$duration = isset($body['duration']) ? (int)$body['duration'] : 0;

// Light sanity caps
$bounces  = max(0, min(10000, $bounces));
$duration = max(0, min(3600,  $duration));

// SAME formula as client HUD: +5 per bounce, +50 every 10-bounce streak
$earned = ($bounces * 500) + (intdiv($bounces, 5) * 50);

if (!isset($_SESSION['wealth']) || !is_numeric($_SESSION['wealth'])) {
  $_SESSION['wealth'] = 0;
}
$_SESSION['wealth'] = (int)$_SESSION['wealth'] + (int)$earned;

include_once 'includes/database.php';
$stmt = $conn->prepare("UPDATE user_account_details SET totalmoneyearned = totalmoneyearned + ?, totalmoneyspent = totalmoneyspent + ? WHERE userkey = ?");
$stmt->bind_param('dds', $earned, $earned, $_SESSION['user-key']);
$stmt->execute(); 

echo json_encode([
  'ok'      => true,
  'earned'  => (int)$earned,
  'wealth'  => (int)$_SESSION['wealth'],
]);
