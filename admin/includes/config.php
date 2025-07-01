<?php
// admin/includes/config.php
session_start();

// Database configuration (sama seperti frontend)
class AdminDatabase {
    private $host = '127.0.0.1';
    private $database = 'kabar_inovasi';
    private $username = 'kabar_inovasi';
    private $password = 'HfLNyMPapaB2ZMH6';
    private $charset = 'utf8mb4';
    private $pdo;

    public function __construct() {
        $this->connect();
    }

    private function connect() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->database};charset={$this->charset}";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $this->pdo = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }

    public function getPdo() {
        return $this->pdo;
    }

    public function query($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new Exception("Query failed: " . $e->getMessage());
        }
    }

    public function fetch($sql, $params = []) {
        return $this->query($sql, $params)->fetch();
    }

    public function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }

    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }
}

// Admin configuration
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', password_hash('admin123', PASSWORD_DEFAULT)); // Ganti password ini!
define('ADMIN_BASE_URL', '/admin');
define('FRONTEND_BASE_URL', '/');

// Initialize database
$adminDb = new AdminDatabase();

// Helper functions
function isLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . ADMIN_BASE_URL . '/login.php');
        exit;
    }
}

function redirectToLogin() {
    header('Location: ' . ADMIN_BASE_URL . '/login.php');
    exit;
}

function generateSlug($text) {
    // Convert to lowercase
    $text = strtolower($text);
    
    // Replace Indonesian characters
    $indonesian = ['á', 'à', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'é', 'è', 'ê', 'ë', 'í', 'ì', 'î', 'ï', 'ñ', 'ó', 'ò', 'ô', 'õ', 'ö', 'ø', 'ú', 'ù', 'û', 'ü', 'ý', 'ÿ'];
    $english = ['a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y'];
    $text = str_replace($indonesian, $english, $text);
    
    // Remove special characters and replace spaces with hyphens
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    $text = trim($text, '-');
    
    return $text;
}

function formatDate($date) {
    return date('d F Y H:i', strtotime($date));
}

function formatDateInput($date) {
    return date('Y-m-d\TH:i', strtotime($date));
}

function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function showAlert($message, $type = 'success') {
    $_SESSION['alert'] = ['message' => $message, 'type' => $type];
}

function displayAlert() {
    if (isset($_SESSION['alert'])) {
        $alert = $_SESSION['alert'];
        $bgClass = $alert['type'] === 'success' ? 'bg-green-100 border-green-500 text-green-700' : 'bg-red-100 border-red-500 text-red-700';
        echo "<div class='alert {$bgClass} px-4 py-3 rounded border mb-4'>{$alert['message']}</div>";
        unset($_SESSION['alert']);
    }
}
?>