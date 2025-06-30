<?php
require_once 'db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$customer_id = $_POST['customer_id'] ?? null;
$status = $_POST['status'] ?? null;
$remarks = $_POST['remarks'] ?? null;

if (!$customer_id || !$status) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

try {
    $conn = getDbConnection();

    // Handle different status updates
    if ($status === 'Declined' && !empty($remarks)) {
        // For declined status with remarks, save status and reason separately without concatenation
        $stmt = $conn->prepare("UPDATE bookings SET status = ?, reason = ? WHERE customer_id = ?");
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        $stmt->bind_param('ssi', $status, $remarks, $customer_id);
    } elseif ($status === 'Declined' && empty($remarks)) {
        // Declined without specific reason
        $stmt = $conn->prepare("UPDATE bookings SET status = ?, reason = 'No specific reason provided' WHERE customer_id = ?");
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        $stmt->bind_param('si', $status, $customer_id);
    } else {
        // For other statuses (Approved, Pending, etc.), just update status, keep existing remarks
        $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE customer_id = ?");
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        $stmt->bind_param('si', $status, $customer_id);
    }

    if ($stmt->execute()) {
        // Prepare notification message based on status
        $message = null;
        if (strpos($status, 'Approved') === 0) {
            $message = "We are pleased to inform you that your reservation has been accepted and confirmed.";
        } elseif (strpos($status, 'Declined') === 0) {
            $message = "Unfortunately, we are unable to accommodate your reservation at this time. We apologize for the inconvenience.";
            // Add the reason to the notification if provided
            if (!empty($remarks)) {
                $message .= " Reason: " . $remarks;
            }
        }

        // Insert notification if message is set
        if ($message !== null) {
            $notifStmt = $conn->prepare("INSERT INTO notifications (customer_id, message) VALUES (?, ?)");
            if (!$notifStmt) {
                throw new Exception('Prepare failed: ' . $conn->error);
            }
            $notifStmt->bind_param('is', $customer_id, $message);
            if (!$notifStmt->execute()) {
                throw new Exception('Execute failed: ' . $notifStmt->error);
            }
            $notifStmt->close();
        }

        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Execute failed: ' . $stmt->error);
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>