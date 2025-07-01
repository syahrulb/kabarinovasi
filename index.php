<?php
// Enable error reporting for debugging (remove in production)
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
require __DIR__ . '/vendor/autoload.php';
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();
// config/database.php
class Database {
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
}

// models/ArticleModel.php
class ArticleModel {
    private $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    public function getFeaturedArticle() {
        $sql = "SELECT a.*, c.name as category_name, c.slug as category_slug, c.color as category_color,
                       au.name as author_name, au.email as author_email, au.avatar as author_avatar
                FROM articles a 
                LEFT JOIN categories c ON a.category_id = c.id 
                LEFT JOIN authors au ON a.author_id = au.id 
                WHERE a.is_featured = 1 AND a.is_published = 1 
                ORDER BY a.published_at DESC LIMIT 1";
        return $this->db->fetch($sql);
    }

    public function getLatestArticles($limit = 4, $offset = 0) {
        $sql = "SELECT a.*, c.name as category_name, c.slug as category_slug, c.color as category_color,
                       au.name as author_name, au.email as author_email, au.avatar as author_avatar
                FROM articles a 
                LEFT JOIN categories c ON a.category_id = c.id 
                LEFT JOIN authors au ON a.author_id = au.id 
                WHERE a.is_published = 1 
                ORDER BY a.published_at DESC LIMIT ? OFFSET ?";
        
        return $this->db->fetchAll($sql, [$limit, $offset]);
    }

    public function getArticleBySlug($slug) {
        $sql = "SELECT a.*, c.name as category_name, c.slug as category_slug, c.color as category_color,
                       au.name as author_name, au.email as author_email, au.avatar as author_avatar,
                       GROUP_CONCAT(t.name SEPARATOR ', ') as tags
                FROM articles a 
                LEFT JOIN categories c ON a.category_id = c.id 
                LEFT JOIN authors au ON a.author_id = au.id 
                LEFT JOIN article_tags at ON a.id = at.article_id
                LEFT JOIN tags t ON at.tag_id = t.id
                WHERE a.slug = ? AND a.is_published = 1
                GROUP BY a.id";
        return $this->db->fetch($sql, [$slug]);
    }

    public function getArticlesByCategory($categorySlug, $limit = 10) {
        $sql = "SELECT a.*, c.name as category_name, c.slug as category_slug, c.color as category_color,
                       au.name as author_name, au.email as author_email, au.avatar as author_avatar
                FROM articles a 
                LEFT JOIN categories c ON a.category_id = c.id 
                LEFT JOIN authors au ON a.author_id = au.id 
                WHERE c.slug = ? AND a.is_published = 1 
                ORDER BY a.published_at DESC LIMIT ?";
        return $this->db->fetchAll($sql, [$categorySlug, $limit]);
    }

    public function searchArticles($query, $limit = 10) {
        // Improved search query with better matching and JOIN
        $sql = "SELECT a.*, c.name as category_name, c.slug as category_slug, c.color as category_color,
                       au.name as author_name, au.email as author_email, au.avatar as author_avatar,
                       GROUP_CONCAT(DISTINCT t.name SEPARATOR ', ') as tags,
                       MATCH(a.title, a.excerpt, a.content) AGAINST(? IN NATURAL LANGUAGE MODE) as relevance
                FROM articles a 
                LEFT JOIN categories c ON a.category_id = c.id 
                LEFT JOIN authors au ON a.author_id = au.id 
                LEFT JOIN article_tags at ON a.id = at.article_id
                LEFT JOIN tags t ON at.tag_id = t.id
                WHERE a.is_published = 1 AND (
                    a.title LIKE ? OR 
                    a.excerpt LIKE ? OR 
                    a.content LIKE ? OR
                    c.name LIKE ? OR
                    au.name LIKE ? OR
                    t.name LIKE ?
                )
                GROUP BY a.id
                ORDER BY relevance DESC, a.published_at DESC 
                LIMIT ?";
        
        $searchTerm = "%{$query}%";
        return $this->db->fetchAll($sql, [
            $query,      // for MATCH AGAINST
            $searchTerm, // title
            $searchTerm, // excerpt  
            $searchTerm, // content
            $searchTerm, // category
            $searchTerm, // author
            $searchTerm, // tags
            $limit
        ]);
    }

    public function incrementViews($articleId) {
        $sql = "UPDATE articles SET views_count = views_count + 1 WHERE id = ?";
        $this->db->query($sql, [$articleId]);
    }
}

// models/CategoryModel.php
class CategoryModel {
    private $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    public function getAllCategories() {
        $sql = "SELECT * FROM categories ORDER BY name ASC";
        return $this->db->fetchAll($sql);
    }

    public function getCategoryBySlug($slug) {
        $sql = "SELECT * FROM categories WHERE slug = ?";
        return $this->db->fetch($sql, [$slug]);
    }
}

// models/TagModel.php
class TagModel {
    private $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    public function getTrendingTags($limit = 5) {
        $sql = "SELECT * FROM tags ORDER BY usage_count DESC LIMIT ?";
        return $this->db->fetchAll($sql, [$limit]);
    }
}

// models/StatisticsModel.php
class StatisticsModel {
    private $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    public function getStatistics() {
        $sql = "SELECT metric_name, metric_value FROM site_statistics";
        $stats = $this->db->fetchAll($sql);
        
        $result = [];
        foreach ($stats as $stat) {
            $result[$stat['metric_name']] = $stat['metric_value'];
        }
        return $result;
    }

    public function updateStatistic($metricName, $value) {
        $sql = "UPDATE site_statistics SET metric_value = ? WHERE metric_name = ?";
        $this->db->query($sql, [$value, $metricName]);
    }
}

// models/NewsletterModel.php
class NewsletterModel {
    private $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    public function subscribe($email) {
        try {
            $sql = "INSERT INTO newsletter_subscribers (email) VALUES (?) ON DUPLICATE KEY UPDATE is_active = 1, unsubscribed_at = NULL";
            $this->db->query($sql, [$email]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function getSubscriberCount() {
        $sql = "SELECT COUNT(*) as count FROM newsletter_subscribers WHERE is_active = 1";
        $result = $this->db->fetch($sql);
        return $result['count'];
    }
}

// Simple Router Class with nginx support
class Router {
    private $routes = [];
    private $debug = false;
    
    public function get($pattern, $callback) {
        $this->routes['GET'][$pattern] = $callback;
    }
    
    public function post($pattern, $callback) {
        $this->routes['POST'][$pattern] = $callback;
    }
    
    public function enableDebug() {
        $this->debug = true;
    }
    
    public function dispatch($method, $uri) {
        // Parse and clean URI
        $originalUri = $uri;
        
        // Check if we have a route parameter from nginx rewrite
        if (isset($_GET['_route'])) {
            $uri = '/' . ltrim($_GET['_route'], '/');
            if ($this->debug) {
                error_log("Router Debug - Using nginx route parameter: " . $_GET['_route']);
                error_log("Router Debug - Converted to URI: $uri");
            }
        } else {
            // Fallback to parsing REQUEST_URI
            $uri = parse_url($uri, PHP_URL_PATH);
            
            // Remove base path - CHANGED FROM kabar_inovasi TO kabarinovasi
            $basePath = '/kabarinovasi';
            if (strpos($uri, $basePath) === 0) {
                $uri = substr($uri, strlen($basePath));
            }
            
            // Ensure we have a leading slash
            if (empty($uri) || $uri[0] !== '/') {
                $uri = '/' . ltrim($uri, '/');
            }
        }
        
        // Debug logging
        if ($this->debug) {
            error_log("Router Debug - Original URI: $originalUri");
            error_log("Router Debug - Final URI: $uri");
            error_log("Router Debug - Method: $method");
            error_log("Router Debug - Available routes: " . print_r(array_keys($this->routes[$method] ?? []), true));
            error_log("Router Debug - GET params: " . print_r($_GET, true));
        }
        
        // Try to match routes
        foreach ($this->routes[$method] ?? [] as $pattern => $callback) {
            // Escape pattern for regex
            $regexPattern = str_replace('/', '\/', $pattern);
            $regexPattern = preg_replace('/\{([^}]+)\}/', '([^\/]+)', $regexPattern);
            $regexPattern = '/^' . $regexPattern . '$/';
            
            if ($this->debug) {
                error_log("Router Debug - Trying pattern: $pattern -> $regexPattern against URI: $uri");
            }
            
            if (preg_match($regexPattern, $uri, $matches)) {
                if ($this->debug) {
                    error_log("Router Debug - Pattern matched! Matches: " . print_r($matches, true));
                }
                
                array_shift($matches); // Remove full match
                return call_user_func_array($callback, $matches);
            }
        }
        
        if ($this->debug) {
            error_log("Router Debug - No route matched for URI: $uri");
        }
        
        return false;
    }
}

// Configuration - CHANGED FROM /kabar_inovasi TO /kabarinovasi
$base_url = 'https://kabarinovasi.my.id/';

// Initialize database and models
try {
    $database = new Database();
    $articleModel = new ArticleModel($database);
    $categoryModel = new CategoryModel($database);
    $tagModel = new TagModel($database);
    $statisticsModel = new StatisticsModel($database);
    $newsletterModel = new NewsletterModel($database);

    // Handle newsletter subscription
    if ($_POST['action'] ?? '' === 'subscribe' && !empty($_POST['email'])) {
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
        if ($email && $newsletterModel->subscribe($email)) {
            $subscriptionSuccess = true;
        } else {
            $subscriptionError = true;
        }
    }

    // Get common data
    $categories = $categoryModel->getAllCategories();
    $trending_topics = $tagModel->getTrendingTags(5);
    $statistics = $statisticsModel->getStatistics();

    // Initialize router
    $router = new Router();
    // Enable debug untuk troubleshooting (uncomment for debugging)
    // $router->enableDebug();

    // Route definitions
    $router->get('/', function() use ($articleModel, $categories, $trending_topics, $statistics) {
        // Handle search - IMPROVED SEARCH HANDLING
        $searchQuery = '';
        $searchResults = [];
        
        // Check for search in both GET and POST
        if (!empty($_GET['search'])) {
            $searchQuery = trim($_GET['search']);
        }
        
        if (!empty($searchQuery)) {
            $searchResults = $articleModel->searchArticles($searchQuery, 20); // Increase limit for search
            
            // Debug search results (remove in production)
            error_log("Search Query: " . $searchQuery);
            error_log("Search Results Count: " . count($searchResults));
        }

        $featured_article = null;
        $articles = [];
        
        // Only show normal content if not searching
        if (empty($searchQuery)) {
            $featured_article = $articleModel->getFeaturedArticle();
            $articles = $articleModel->getLatestArticles(4);
        }

        // Format data for template compatibility
        if ($featured_article) {
            $featured_article['category'] = $featured_article['category_name'];
            $featured_article['author'] = $featured_article['author_name'];
            $featured_article['date'] = date('d F Y', strtotime($featured_article['published_at'] ?? $featured_article['created_at']));
            $featured_article['image'] = $featured_article['featured_image'];
        }

        foreach ($articles as &$article) {
            $article['category'] = $article['category_name'];
            $article['author'] = $article['author_name'];
            $article['date'] = date('d F Y', strtotime($article['published_at'] ?? $article['created_at']));
            $article['image'] = $article['featured_image'];
        }

        return [
            'view' => 'home',
            'data' => compact('featured_article', 'articles', 'categories', 'trending_topics', 'statistics', 'searchQuery', 'searchResults')
        ];
    });

    $router->get('/article/{slug}', function($slug) use ($articleModel, $categories, $trending_topics, $statistics) {
        // Debug log
        error_log("Article route called with slug: $slug");
        
        $article = $articleModel->getArticleBySlug($slug);
        
        if (!$article) {
            error_log("Article not found for slug: $slug");
            http_response_code(404);
            return ['view' => '404', 'data' => compact('categories', 'trending_topics', 'statistics')];
        }

        // Increment view count
        $articleModel->incrementViews($article['id']);

        // Format article data for backward compatibility
        $article['category'] = $article['category_name'];
        $article['author'] = $article['author_name'];
        $article['date'] = date('d F Y', strtotime($article['published_at'] ?? $article['created_at']));
        $article['image'] = $article['featured_image'];

        error_log("Article found and formatted: " . $article['title']);

        return [
            'view' => 'article',
            'data' => compact('article', 'categories', 'trending_topics', 'statistics')
        ];
    });

    $router->get('/category/{slug}', function($slug) use ($articleModel, $categoryModel, $categories, $trending_topics, $statistics) {
        $category = $categoryModel->getCategoryBySlug($slug);
        
        if (!$category) {
            http_response_code(404);
            return ['view' => '404', 'data' => compact('categories', 'trending_topics', 'statistics')];
        }

        $articles = $articleModel->getArticlesByCategory($slug, 12);

        // Format articles data
        foreach ($articles as &$article) {
            $article['category'] = $article['category_name'];
            $article['author'] = $article['author_name'];
            $article['date'] = date('d F Y', strtotime($article['published_at'] ?? $article['created_at']));
            $article['image'] = $article['featured_image'];
        }

        return [
            'view' => 'category',
            'data' => compact('category', 'articles', 'categories', 'trending_topics', 'statistics')
        ];
    });

    // Dispatch route
    $method = $_SERVER['REQUEST_METHOD'];
    $uri = $_SERVER['REQUEST_URI'];
    
    error_log("Dispatching route - Method: $method, URI: $uri");
    
    $result = $router->dispatch($method, $uri);

    if ($result === false) {
        error_log("No route matched, defaulting to home");
        // Default to home page if no route matches
        $result = $router->dispatch('GET', '/');
    }

    if ($result === false) {
        // If still no match, show 404
        http_response_code(404);
        $result = [
            'view' => '404',
            'data' => compact('categories', 'trending_topics', 'statistics')
        ];
    }

    $view = $result['view'];
    $data = $result['data'];
    extract($data);

} catch (Exception $e) {
    // Handle database errors gracefully
    error_log("Database error: " . $e->getMessage());
    $view = 'home';
    $featured_article = null;
    $articles = [];
    $categories = [];
    $trending_topics = [];
    $statistics = [
        'total_articles' => 0,
        'active_readers' => 0,
        'featured_startups' => 0
    ];
    $searchQuery = '';
    $searchResults = [];
}
?>

<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php 
        if ($view === 'article' && isset($article)) {
            echo htmlspecialchars($article['title']) . ' - kabarInovasi';
        } elseif ($view === 'category' && isset($category)) {
            echo 'Kategori: ' . htmlspecialchars($category['name']) . ' - kabarInovasi';
        } elseif (!empty($searchQuery)) {
            echo "Hasil pencarian: $searchQuery - kabarInovasi";
        } else {
            echo 'kabarInovasi - Portal Berita Inovasi Terdepan';
        }
        ?>
    </title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Inter', system-ui, sans-serif;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes slideUp {
            from { transform: translateY(30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        @keyframes pulseCustom {
            0%, 100% { opacity: 0.3; transform: scale(1); }
            50% { opacity: 0.6; transform: scale(1.05); }
        }
        
        .gradient-text {
            background: linear-gradient(135deg, #3b82f6, #8b5cf6, #06b6d4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .glass-effect {
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
        
        .animate-fade-in {
            animation: fadeIn 0.6s ease-out forwards;
        }
        
        .animate-slide-up {
            animation: slideUp 0.6s ease-out forwards;
        }
        
        .animate-pulse-slow {
            animation: pulseCustom 3s infinite;
        }
        
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .line-clamp-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .hover-lift {
            transition: all 0.3s ease;
        }
        
        .hover-lift:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            transition: all 0.2s ease;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #1d4ed8, #1e40af);
            transform: translateY(-1px);
        }
        
        .stagger-1 { animation-delay: 0.1s; }
        .stagger-2 { animation-delay: 0.2s; }
        .stagger-3 { animation-delay: 0.3s; }
        .stagger-4 { animation-delay: 0.4s; }

        .success-message {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            animation: slideUp 0.3s ease-out;
        }

        .error-message {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            animation: slideUp 0.3s ease-out;
        }

        /* Search highlight */
        .search-highlight {
            background-color: yellow;
            padding: 2px 4px;
            border-radius: 3px;
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-900 font-sans antialiased">
    
    <!-- Success/Error Messages -->
    <?php if (isset($subscriptionSuccess)): ?>
    <div class="success-message fixed top-4 right-4 z-50 max-w-sm">
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            Berhasil berlangganan newsletter!
        </div>
    </div>
    <?php endif; ?>

    <?php if (isset($subscriptionError)): ?>
    <div class="error-message fixed top-4 right-4 z-50 max-w-sm">
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            Gagal berlangganan. Email sudah terdaftar atau tidak valid.
        </div>
    </div>
    <?php endif; ?>

    <!-- Navigation Header -->
    <nav class="bg-white/90 glass-effect border-b border-gray-200 sticky top-0 z-40 animate-fade-in">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex items-center space-x-2">
                    <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <a href="<?php echo $base_url; ?>" class="text-2xl font-bold gradient-text">kabarInovasi</a>
                </div>

                <!-- Navigation Menu -->
                <div class="hidden md:flex space-x-8">
                    <a href="<?php echo $base_url; ?>" class="text-gray-700 hover:text-blue-600 font-medium transition-colors duration-200">Beranda</a>
                    <?php foreach (array_slice($categories, 0, 4) as $category): ?>
                    <a href="<?php echo $base_url; ?>/category/<?php echo htmlspecialchars($category['slug']); ?>" 
                       class="text-gray-700 hover:text-blue-600 font-medium transition-colors duration-200">
                        <?php echo htmlspecialchars($category['name']); ?>
                    </a>
                    <?php endforeach; ?>
                </div>

                <!-- Search & CTA -->
                <div class="flex items-center space-x-4">
                    <form method="GET" action="<?php echo $base_url; ?>" class="relative">
                        <input type="text" name="search" value="<?php echo htmlspecialchars($searchQuery ?? ''); ?>" 
                               placeholder="Cari berita..." 
                               class="w-64 px-4 py-2 bg-gray-100 rounded-full border-0 focus:bg-white focus:ring-2 focus:ring-blue-500 transition-all duration-200">
                        <button type="submit" class="absolute right-3 top-2.5">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </button>
                    </form>
                    <button onclick="document.getElementById('newsletter-form').scrollIntoView({behavior: 'smooth'})" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-full font-medium transition-colors duration-200">
                        Berlangganan
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <?php if ($view === 'article'): ?>
        <?php if (file_exists('views/article.php')): ?>
            <?php include 'views/article.php'; ?>
        <?php else: ?>
            <div class="max-w-4xl mx-auto px-4 py-16 text-center">
                <h1 class="text-4xl font-bold text-gray-900 mb-4">File views/article.php tidak ditemukan</h1>
                <p class="text-gray-600">Silakan buat file views/article.php sesuai dengan kode yang telah diberikan.</p>
            </div>
        <?php endif; ?>
    <?php elseif ($view === 'category'): ?>
        <?php if (file_exists('views/category.php')): ?>
            <?php include 'views/category.php'; ?>
        <?php else: ?>
            <div class="max-w-4xl mx-auto px-4 py-16 text-center">
                <h1 class="text-4xl font-bold text-gray-900 mb-4">File views/category.php tidak ditemukan</h1>
                <p class="text-gray-600">Silakan buat file views/category.php.</p>
            </div>
        <?php endif; ?>
    <?php elseif ($view === '404'): ?>
        <?php if (file_exists('views/404.php')): ?>
            <?php include 'views/404.php'; ?>
        <?php else: ?>
            <div class="max-w-4xl mx-auto px-4 py-16 text-center">
                <h1 class="text-4xl font-bold text-gray-900 mb-4">404 - Halaman Tidak Ditemukan</h1>
                <p class="text-gray-600">Halaman yang Anda cari tidak dapat ditemukan.</p>
                <a href="<?php echo $base_url; ?>" class="mt-4 inline-block bg-blue-600 text-white px-6 py-3 rounded-full">Kembali ke Beranda</a>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <?php if (file_exists('views/home.php')): ?>
            <?php include 'views/home.php'; ?>
        <?php else: ?>
            <div class="max-w-4xl mx-auto px-4 py-16 text-center">
                <h1 class="text-4xl font-bold text-gray-900 mb-4">File views/home.php tidak ditemukan</h1>
                <p class="text-gray-600">Silakan buat file views/home.php.</p>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="grid lg:grid-cols-4 gap-8 mb-8">
                <div class="lg:col-span-2">
                    <div class="flex items-center space-x-2 mb-4">
                        <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold">kabarInovasi</h3>
                    </div>
                    <p class="text-gray-400 mb-6 max-w-md">
                        Portal berita inovasi terdepan yang menghadirkan informasi terkini tentang teknologi, startup, dan perkembangan dunia digital di Indonesia.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="w-10 h-10 bg-gray-800 hover:bg-blue-600 rounded-full flex items-center justify-center transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-800 hover:bg-blue-600 rounded-full flex items-center justify-center transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M22.46 6c-.77.35-1.6.58-2.46.69.88-.53 1.56-1.37 1.88-2.38-.83.5-1.75.85-2.72 1.05C18.37 4.5 17.26 4 16 4c-2.35 0-4.27 1.92-4.27 4.29 0 .34.04.67.11.98C8.28 9.09 5.11 7.38 3 4.79c-.37.63-.58 1.37-.58 2.15 0 1.49.75 2.81 1.91 3.56-.71 0-1.37-.2-1.95-.5v.03c0 2.08 1.48 3.82 3.44 4.21a4.22 4.22 0 0 1-1.93.07 4.28 4.28 0 0 0 4 2.98 8.521 8.521 0 0 1-5.33 1.84c-.34 0-.68-.02-1.02-.06C3.44 20.29 5.7 21 8.12 21 16 21 20.33 14.46 20.33 8.79c0-.19 0-.37-.01-.56.84-.6 1.56-1.36 2.14-2.23z"/></svg>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-800 hover:bg-blue-600 rounded-full flex items-center justify-center transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                        </a>
                    </div>
                </div>
                
                <div>
                    <h4 class="text-lg font-semibold mb-4">Kategori</h4>
                    <ul class="space-y-2">
                        <?php foreach (array_slice($categories, 0, 5) as $category): ?>
                        <li>
                            <a href="<?php echo $base_url; ?>/category/<?php echo htmlspecialchars($category['slug']); ?>" 
                               class="text-gray-400 hover:text-white transition-colors">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <div>
                    <h4 class="text-lg font-semibold mb-4">Perusahaan</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Tentang Kami</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Tim Redaksi</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Karir</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Kontak</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Privasi</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-800 pt-8 flex flex-col md:flex-row justify-between items-center">
                <p class="text-gray-400 text-sm">
                    © 2025 kabarInovasi. Seluruh hak cipta dilindungi.
                </p>
                <p class="text-gray-400 text-sm mt-4 md:mt-0">
                    Dibuat dengan ❤️ untuk masa depan Indonesia yang inovatif
                </p>
            </div>
        </div>
    </footer>

    <script>
        // JavaScript untuk interaktivitas dan animasi
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-hide success/error messages
            setTimeout(function() {
                const messages = document.querySelectorAll('.success-message, .error-message');
                messages.forEach(function(message) {
                    message.style.opacity = '0';
                    message.style.transform = 'translateY(-20px)';
                    setTimeout(function() {
                        if (message.parentNode) {
                            message.parentNode.removeChild(message);
                        }
                    }, 300);
                });
            }, 5000);

            // Smooth scrolling untuk links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth'
                        });
                    }
                });
            });

            // Animation on scroll with Intersection Observer
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                        entry.target.classList.add('animate-fade-in');
                    }
                });
            }, observerOptions);

            // Observe artikel cards dan sidebar elements
            document.querySelectorAll('article, aside > div').forEach((el, index) => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(30px)';
                el.style.transition = 'all 0.6s ease-out';
                el.style.transitionDelay = `${index * 0.1}s`;
                observer.observe(el);
            });

            // Hover effects untuk cards
            document.querySelectorAll('.hover-lift').forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-8px) scale(1.02)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
            });

            // Search functionality enhancement
            const searchInput = document.querySelector('input[name="search"]');
            if (searchInput) {
                searchInput.addEventListener('focus', function() {
                    this.parentElement.classList.add('ring-2', 'ring-blue-500');
                });
                
                searchInput.addEventListener('blur', function() {
                    this.parentElement.classList.remove('ring-2', 'ring-blue-500');
                });

                // Auto-submit search on Enter
                searchInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        this.form.submit();
                    }
                });
            }
        });

        // Search highlighting function
        function highlightSearchTerm(text, searchTerm) {
            if (!searchTerm) return text;
            const regex = new RegExp(`(${searchTerm})`, 'gi');
            return text.replace(regex, '<span class="search-highlight">$1</span>');
        }
    </script>
</body>
</html>