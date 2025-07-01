<?php


// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
class QuickDatabase {
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

function displayResult($title, $data, $status = 'info') {
    $colors = [
        'success' => 'background: #d4edda; border: 1px solid #c3e6cb; color: #155724;',
        'error' => 'background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24;',
        'warning' => 'background: #fff3cd; border: 1px solid #ffeaa7; color: #856404;',
        'info' => 'background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460;'
    ];
    
    echo '<div style="margin: 10px 0; padding: 15px; border-radius: 5px; ' . $colors[$status] . '">';
    echo '<h3 style="margin: 0 0 10px 0;">' . htmlspecialchars($title) . '</h3>';
    
    if (is_array($data) || is_object($data)) {
        echo '<pre style="background: rgba(0,0,0,0.1); padding: 10px; border-radius: 3px; overflow-x: auto;">';
        echo htmlspecialchars(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        echo '</pre>';
    } else {
        echo '<p>' . htmlspecialchars($data) . '</p>';
    }
    echo '</div>';
}

// HTML Header
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>kabarInovasi - Search Fix & Debug Tool</title>
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
            max-width: 1200px; 
            margin: 0 auto; 
            padding: 20px; 
            background: #f8f9fa;
        }
        .header { 
            background: linear-gradient(135deg, #3b82f6, #8b5cf6); 
            color: white; 
            padding: 20px; 
            border-radius: 10px; 
            margin-bottom: 20px; 
        }
        .test-section { 
            background: white; 
            padding: 20px; 
            margin: 20px 0; 
            border-radius: 10px; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); 
        }
        .search-form { 
            background: #e3f2fd; 
            padding: 20px; 
            border-radius: 8px; 
            margin: 20px 0; 
        }
        .search-form input { 
            width: 300px; 
            padding: 10px; 
            border: 1px solid #ddd; 
            border-radius: 5px; 
            margin-right: 10px; 
        }
        .search-form button { 
            padding: 10px 20px; 
            background: #2196f3; 
            color: white; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer; 
        }
        .stats { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
            gap: 15px; 
            margin: 20px 0; 
        }
        .stat-card { 
            background: white; 
            padding: 15px; 
            border-radius: 8px; 
            border-left: 4px solid #2196f3; 
            box-shadow: 0 2px 5px rgba(0,0,0,0.1); 
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🔍 kabarInovasi - Search Fix & Debug Tool</h1>
        <p>Tools untuk mendiagnosis dan memperbaiki masalah pencarian website</p>
    </div>

<?php

// Test 1: Database Connection
try {
    $db = new QuickDatabase();
    displayResult('✅ Database Connection', 'Successfully connected to database', 'success');
} catch (Exception $e) {
    displayResult('❌ Database Connection Failed', $e->getMessage(), 'error');
    exit;
}

// Test 2: Check Tables and Data
echo '<div class="test-section"><h2>📊 Database Structure & Data Check</h2>';

try {
    // Check articles
    $articleCount = $db->fetch("SELECT COUNT(*) as count FROM articles")['count'];
    $publishedCount = $db->fetch("SELECT COUNT(*) as count FROM articles WHERE is_published = 1")['count'];
    
    // Check categories
    $categoryCount = $db->fetch("SELECT COUNT(*) as count FROM categories")['count'];
    
    // Check authors
    $authorCount = $db->fetch("SELECT COUNT(*) as count FROM authors")['count'];
    
    // Check tags
    $tagCount = $db->fetch("SELECT COUNT(*) as count FROM tags")['count'];
    
    echo '<div class="stats">';
    echo '<div class="stat-card"><h4>Total Articles</h4><p>' . $articleCount . '</p></div>';
    echo '<div class="stat-card"><h4>Published Articles</h4><p>' . $publishedCount . '</p></div>';
    echo '<div class="stat-card"><h4>Categories</h4><p>' . $categoryCount . '</p></div>';
    echo '<div class="stat-card"><h4>Authors</h4><p>' . $authorCount . '</p></div>';
    echo '<div class="stat-card"><h4>Tags</h4><p>' . $tagCount . '</p></div>';
    echo '</div>';
    
    if ($publishedCount == 0) {
        displayResult('⚠️ Warning', 'No published articles found! This is why search returns no results.', 'warning');
    }
    
} catch (Exception $e) {
    displayResult('❌ Data Check Failed', $e->getMessage(), 'error');
}

echo '</div>';

// Test 3: Search Functionality Test
echo '<div class="test-section"><h2>🔍 Search Functionality Test</h2>';

// Handle search form submission
$searchQuery = $_GET['test_search'] ?? '';

echo '<div class="search-form">';
echo '<form method="GET">';
echo '<input type="text" name="test_search" value="' . htmlspecialchars($searchQuery) . '" placeholder="Enter search term (e.g., AI, startup, teknologi)">';
echo '<button type="submit">Test Search</button>';
echo '</form>';
echo '</div>';

if (!empty($searchQuery)) {
    try {
        // Test search query
        $searchSql = "SELECT a.*, c.name as category_name, au.name as author_name
                      FROM articles a 
                      LEFT JOIN categories c ON a.category_id = c.id 
                      LEFT JOIN authors au ON a.author_id = au.id 
                      WHERE a.is_published = 1 AND (
                          a.title LIKE ? OR 
                          a.excerpt LIKE ? OR 
                          a.content LIKE ? OR
                          c.name LIKE ? OR
                          au.name LIKE ?
                      )
                      ORDER BY a.published_at DESC 
                      LIMIT 10";
        
        $searchTerm = "%{$searchQuery}%";
        $searchResults = $db->fetchAll($searchSql, [
            $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm
        ]);
        
        displayResult(
            '🎯 Search Results for: "' . $searchQuery . '"', 
            'Found ' . count($searchResults) . ' results', 
            count($searchResults) > 0 ? 'success' : 'warning'
        );
        
        if (count($searchResults) > 0) {
            echo '<h4>Search Results:</h4>';
            foreach ($searchResults as $result) {
                echo '<div style="border: 1px solid #ddd; padding: 10px; margin: 10px 0; border-radius: 5px;">';
                echo '<h5>' . htmlspecialchars($result['title']) . '</h5>';
                echo '<p><strong>Category:</strong> ' . htmlspecialchars($result['category_name'] ?? 'No Category') . '</p>';
                echo '<p><strong>Author:</strong> ' . htmlspecialchars($result['author_name'] ?? 'No Author') . '</p>';
                echo '<p>' . htmlspecialchars(substr($result['excerpt'], 0, 200)) . '...</p>';
                echo '</div>';
            }
        } else {
            displayResult('❌ No Results Found', 'Search query returned no results. Check if articles contain the search term.', 'warning');
        }
        
    } catch (Exception $e) {
        displayResult('❌ Search Test Failed', $e->getMessage(), 'error');
    }
}

echo '</div>';

// Test 4: Sample Data Check
echo '<div class="test-section"><h2>📝 Sample Articles</h2>';

try {
    $sampleArticles = $db->fetchAll("
        SELECT a.title, a.slug, c.name as category_name, au.name as author_name, a.is_published
        FROM articles a 
        LEFT JOIN categories c ON a.category_id = c.id 
        LEFT JOIN authors au ON a.author_id = au.id 
        ORDER BY a.created_at DESC 
        LIMIT 5
    ");
    
    if (count($sampleArticles) > 0) {
        displayResult('📰 Latest Articles in Database', $sampleArticles, 'info');
    } else {
        displayResult('⚠️ No Articles Found', 'Database has no articles. You need to add some articles first.', 'warning');
    }
    
} catch (Exception $e) {
    displayResult('❌ Sample Data Check Failed', $e->getMessage(), 'error');
}

echo '</div>';

// Test 5: Quick Fix Options
echo '<div class="test-section"><h2>🔧 Quick Fix Options</h2>';

if (isset($_GET['action'])) {
    $action = $_GET['action'];
    
    try {
        switch ($action) {
            case 'add_sample_data':
                // Add sample articles if none exist
                $existingCount = $db->fetch("SELECT COUNT(*) as count FROM articles WHERE is_published = 1")['count'];
                
                if ($existingCount == 0) {
                    // Insert sample article
                    $db->query("
                        INSERT INTO articles (title, slug, excerpt, content, featured_image, category_id, author_id, is_featured, is_published, read_time, published_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                    ", [
                        'Contoh Artikel untuk Testing Pencarian',
                        'contoh-artikel-testing-pencarian',
                        'Ini adalah artikel contoh yang dibuat untuk testing fitur pencarian website kabarInovasi.',
                        'Artikel ini berisi berbagai kata kunci seperti teknologi, AI, startup, inovasi, dan blockchain untuk memastikan fitur pencarian berfungsi dengan baik. Anda dapat mencari kata kunci ini untuk menguji apakah sistem pencarian bekerja dengan benar.',
                        'https://images.unsplash.com/photo-1518770660439-4636190af475?w=800&h=400&fit=crop',
                        1, // category_id
                        1, // author_id
                        0, // is_featured
                        1, // is_published
                        5  // read_time
                    ]);
                    
                    displayResult('✅ Sample Data Added', 'Successfully added sample article for testing', 'success');
                } else {
                    displayResult('ℹ️ Sample Data', 'Sample data already exists (' . $existingCount . ' published articles)', 'info');
                }
                break;
                
            case 'fix_published_status':
                // Publish all articles that are not published
                $updated = $db->query("UPDATE articles SET is_published = 1 WHERE is_published = 0")->rowCount();
                displayResult('✅ Published Status Fixed', "Updated $updated articles to published status", 'success');
                break;
                
            case 'rebuild_indexes':
                // Rebuild search indexes
                $db->query("ALTER TABLE articles DROP INDEX IF EXISTS idx_search");
                $db->query("ALTER TABLE articles ADD FULLTEXT KEY idx_search (title, excerpt, content)");
                displayResult('✅ Indexes Rebuilt', 'Successfully rebuilt FULLTEXT search indexes', 'success');
                break;
        }
    } catch (Exception $e) {
        displayResult('❌ Fix Action Failed', $e->getMessage(), 'error');
    }
}

echo '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">';

echo '<div style="border: 1px solid #ddd; padding: 15px; border-radius: 8px;">';
echo '<h4>Add Sample Data</h4>';
echo '<p>Add sample articles for testing search functionality</p>';
echo '<a href="?action=add_sample_data" style="background: #4CAF50; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px;">Add Sample Data</a>';
echo '</div>';

echo '<div style="border: 1px solid #ddd; padding: 15px; border-radius: 8px;">';
echo '<h4>Fix Published Status</h4>';
echo '<p>Make sure all articles are published so they appear in search</p>';
echo '<a href="?action=fix_published_status" style="background: #2196F3; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px;">Fix Published</a>';
echo '</div>';

echo '<div style="border: 1px solid #ddd; padding: 15px; border-radius: 8px;">';
echo '<h4>Rebuild Indexes</h4>';
echo '<p>Rebuild database indexes for better search performance</p>';
echo '<a href="?action=rebuild_indexes" style="background: #FF9800; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px;">Rebuild Indexes</a>';
echo '</div>';

echo '</div>';
echo '</div>';

// Test 6: Final Recommendations
echo '<div class="test-section"><h2>💡 Recommendations</h2>';

$recommendations = [];

// Check if search is working
if (!empty($searchQuery)) {
    $testResults = $db->fetchAll("
        SELECT COUNT(*) as count 
        FROM articles a 
        WHERE a.is_published = 1 AND a.title LIKE ?
    ", ["%{$searchQuery}%"]);
    
    if ($testResults[0]['count'] > 0) {
        $recommendations[] = "✅ Search functionality is working correctly!";
    } else {
        $recommendations[] = "❌ Search not returning results. Check article content and published status.";
    }
}

// Check data completeness
$publishedCount = $db->fetch("SELECT COUNT(*) as count FROM articles WHERE is_published = 1")['count'];
if ($publishedCount == 0) {
    $recommendations[] = "⚠️ Add published articles to test search functionality";
}

if ($publishedCount < 5) {
    $recommendations[] = "📝 Add more articles for better search testing";
}

// Check for categories and authors
$categoriesWithArticles = $db->fetch("
    SELECT COUNT(DISTINCT c.id) as count 
    FROM categories c 
    JOIN articles a ON c.id = a.category_id 
    WHERE a.is_published = 1
")['count'];

if ($categoriesWithArticles == 0) {
    $recommendations[] = "🏷️ Assign categories to articles for better search results";
}

// Check for basic search terms
$commonTerms = ['AI', 'teknologi', 'startup', 'inovasi'];
$foundTerms = 0;
foreach ($commonTerms as $term) {
    $count = $db->fetch("
        SELECT COUNT(*) as count 
        FROM articles 
        WHERE is_published = 1 AND (title LIKE ? OR excerpt LIKE ? OR content LIKE ?)
    ", ["%{$term}%", "%{$term}%", "%{$term}%"])['count'];
    
    if ($count > 0) $foundTerms++;
}

if ($foundTerms < 2) {
    $recommendations[] = "🔍 Add articles with common search terms (AI, teknologi, startup, inovasi)";
}

$recommendations[] = "🔧 After fixes, test search on main website: /kabarinovasi/?search=YOUR_TERM";
$recommendations[] = "📱 Test search functionality on both desktop and mobile";

foreach ($recommendations as $rec) {
    echo '<p style="padding: 8px; margin: 5px 0; background: #f0f0f0; border-radius: 4px;">' . $rec . '</p>';
}

echo '</div>';

?>

<div class="test-section">
    <h2>🚀 Next Steps</h2>
    <ol>
        <li><strong>Run Quick Fixes:</strong> Use the fix buttons above to resolve common issues</li>
        <li><strong>Test Search:</strong> Use the search form above to test different keywords</li>
        <li><strong>Verify on Website:</strong> Go to your main website and test search functionality</li>
        <li><strong>Add Content:</strong> Add more articles with relevant keywords for better search results</li>
        <li><strong>Monitor:</strong> Keep track of popular search terms and add relevant content</li>
    </ol>
    
    <div style="background: #e8f5e8; padding: 15px; border-radius: 8px; margin-top: 20px;">
        <h4>🎯 Pro Tips:</h4>
        <ul>
            <li>Include popular keywords in your article titles and excerpts</li>
            <li>Use descriptive category names that users might search for</li>
            <li>Tag articles with relevant keywords</li>
            <li>Regularly update and publish new content</li>
            <li>Monitor which search terms return no results and create content for them</li>
        </ul>
    </div>
</div>

<div style="text-align: center; margin: 30px 0; padding: 20px; background: #f8f9fa; border-radius: 8px;">
    <p><strong>kabarInovasi Search Fix Tool</strong> | Last Updated: June 24, 2025</p>
    <p>Remember to delete this file (search_fix.php) after troubleshooting for security!</p>
</div>

</body>
</html>