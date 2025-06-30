<?php
require_once 'db.php';

header('Content-Type: application/json');

try {
    $conn = getDbConnection();

    $sql = "SELECT lastName, firstName, rating, comment FROM feedback";

    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception('Database query failed: ' . $conn->error);
    }

    $feedback = [];

    while ($row = $result->fetch_assoc()) {
        $feedback[] = $row;
    }

    echo json_encode(['success' => true, 'data' => $feedback]);

    $conn->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>