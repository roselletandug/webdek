<?php
require_once 'db.php';

header('Content-Type: application/json');

$conn = getDbConnection();

$sql = "SELECT theme_name, is_available FROM theme_availability ORDER BY theme_name ASC";
$result = $conn->query($sql);

$themes = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $themes[] = $row;
    }
} else {
    // If no data, fallback to default themes with available status 1
    $defaultThemes = [
        'Rustic', 'Glamorous', 'Modern Minimalist', 'Garden / Botanical', 'Fairy Tale',
        'Vintage', 'Beach / Tropical', 'Rustic Chic', 'Industrial', 'Enchanted Forest',
        'Celestial', 'Masquerade', 'Classic Elegance', 'Carnival / Circus', 'Hollywood / Red Carpet',
        'Winter Wonderland', 'Spring Floral', 'Cultural Themes', 'Disney', 'Disco', 'Safari'
    ];
    foreach ($defaultThemes as $theme) {
        $themes[] = ['theme_name' => $theme, 'is_available' => 1];
    }
}

$conn->close();

echo json_encode($themes);
?>
