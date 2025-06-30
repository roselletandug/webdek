<?php
require_once 'db.php';

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Availability - Admin - DekoRista</title>
    <link rel="stylesheet" href="css/style.css" />
</head>
<body>
    <h2>Availability</h2>
    <table id="availabilityTable" border="1" cellpadding="10" cellspacing="0" style="border-collapse: collapse; width: 100%;">
        <thead>
            <tr>
                <th>Theme</th>
                <th>Availability Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($themes as $theme): ?>
            <tr>
                <td><?php echo htmlspecialchars($theme['theme_name']); ?></td>
                <td class="status-text <?php echo ($theme['is_available'] === '1' || $theme['is_available'] === 1) ? 'status-available' : 'status-not-available'; ?>">
                    <?php echo ($theme['is_available'] === '1' || $theme['is_available'] === 1) ? 'Yes' : 'No'; ?>
                </td>
                <td>
                    <button class="availability-btn available <?php echo $theme['is_available'] ? 'active gray' : ''; ?>">Available</button>
                    <button class="availability-btn not-available <?php echo !$theme['is_available'] ? 'active gray' : ''; ?>">Not Available</button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <script src="js/script.js"></script>
</body>
</html>
