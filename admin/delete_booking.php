<?php
require_once 'db.php';

header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get and validate customer_id
$customer_id = $_POST['customer_id'] ?? null;

if (!$customer_id) {
    echo json_encode(['success' => false, 'message' => 'Missing customer_id']);
    exit;
}

// Validate customer_id is a positive integer
if (!filter_var($customer_id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
    echo json_encode(['success' => false, 'message' => 'Invalid customer_id format']);
    exit;
}

try {
    $conn = getDbConnection();
    
    // Start transaction for data consistency
    $conn->autocommit(false);
    
    // Removed check for customer existence in customers table
    // Directly proceed to delete bookings by customer_id
    
    // Get count of bookings to be deleted (for response info)
    $count_stmt = $conn->prepare("SELECT COUNT(*) FROM bookings WHERE customer_id = ?");
    if (!$count_stmt) {
        throw new Exception('Booking count prepare failed: ' . $conn->error);
    }
    
    $count_stmt->bind_param('i', $customer_id);
    if (!$count_stmt->execute()) {
        throw new Exception('Booking count execute failed: ' . $count_stmt->error);
    }
    
    $count_stmt->bind_result($booking_count);
    $count_stmt->fetch();
    $count_stmt->close();
    
    if ($booking_count == 0) {
        $conn->rollback();
        echo json_encode([
            'success' => false, 
            'message' => 'No bookings found for this customer'
        ]);
        exit;
    }
    
    // Optional: Log the deletion attempt (uncomment if you have an audit_log table)
    /*
    $log_stmt = $conn->prepare("INSERT INTO audit_log (action, table_name, record_id, user_ip, timestamp) VALUES (?, ?, ?, ?, NOW())");
    if ($log_stmt) {
        $action = "DELETE_CUSTOMER_BOOKINGS";
        $table_name = "bookings";
        $user_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $log_stmt->bind_param('ssis', $action, $table_name, $customer_id, $user_ip);
        $log_stmt->execute();
        $log_stmt->close();
    }
    */
    
    // Delete bookings
    $delete_stmt = $conn->prepare("DELETE FROM bookings WHERE customer_id = ?");
    if (!$delete_stmt) {
        throw new Exception('Delete prepare failed: ' . $conn->error);
    }
    
    $delete_stmt->bind_param('i', $customer_id);
    
    if (!$delete_stmt->execute()) {
        throw new Exception('Delete execute failed: ' . $delete_stmt->error);
    }
    
    $affected_rows = $delete_stmt->affected_rows;
    $delete_stmt->close();
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Bookings deleted successfully',
        'deleted_count' => $affected_rows
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($conn)) {
        $conn->rollback();
    }
    
    // Log error (optional - uncomment if you have error logging)
    // error_log("Delete bookings error: " . $e->getMessage());
    
    echo json_encode([
        'success' => false, 
        'message' => 'Database error occurred',
        'error' => $e->getMessage() // Remove this in production for security
    ]);
    
} finally {
    // Clean up connections
    if (isset($conn)) {
        $conn->autocommit(true); // Reset autocommit
        $conn->close();
    }
}
?>