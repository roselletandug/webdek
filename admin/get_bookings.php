<?php
require_once 'db.php';

header('Content-Type: application/json');

try {
    $conn = getDbConnection();

$month = isset($_GET['month']) ? intval($_GET['month']) : 0;
$year = isset($_GET['year']) ? intval($_GET['year']) : 0;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$sql = "SELECT b.customer_id, b.last_name, b.first_name, b.contact_no, b.address, b.theme, b.specific_theme, b.color_theme, b.date_of_event, b.time_of_event, b.name_of_celebrant, b.age_of_celebrant, b.venue_address, b.package, b.sample_event_design, b.image_payment, b.status, b.remarks,
        CASE 
            WHEN b.status LIKE 'Declined%' THEN b.reason 
            ELSE '' 
        END AS reason
        FROM bookings b";

$whereClauses = [];

if ($month > 0 && $year > 0) {
    $whereClauses[] = "MONTH(b.date_of_event) = $month AND YEAR(b.date_of_event) = $year";
}

if ($search !== '') {
    $searchEscaped = $conn->real_escape_string($search);
    $whereClauses[] = "(LOWER(b.last_name) LIKE LOWER('%$searchEscaped%') OR LOWER(b.first_name) LIKE LOWER('%$searchEscaped%'))";
}

if (count($whereClauses) > 0) {
    $sql .= " WHERE " . implode(" AND ", $whereClauses);
}

$sql .= " ORDER BY b.date_of_event DESC";

    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception('Database query failed: ' . $conn->error);
    }

    $bookings = [];

    while ($row = $result->fetch_assoc()) {
        $bookings[] = $row;
    }

    echo json_encode(['success' => true, 'data' => $bookings]);

    $conn->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>