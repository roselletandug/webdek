-- Create theme_availability table
CREATE TABLE IF NOT EXISTS theme_availability (
    id INT AUTO_INCREMENT PRIMARY KEY,
    theme_name VARCHAR(255) NOT NULL UNIQUE,
    is_available TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
