<?php
// admin/utilities.php
require_once 'includes/config.php';
requireLogin();

// Handle actions
if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    try {
        if ($action === 'update_statistics') {
            // Update all statistics
            $stats = [
                'total_articles' => $adminDb->fetch("SELECT COUNT(*) as count FROM articles")['count'],
                'active_readers' => rand(15000, 20000), // This would normally come from analytics
                'featured_startups' => rand(300, 400), // This would come from a startups table
                'newsletter_subscribers' => $adminDb->fetch("SELECT COUNT(*) as count FROM newsletter_subscribers WHERE is_active = 1")['count']
            ];
            
            foreach ($stats as $metric => $value) {
                $adminDb->query("
                    INSERT INTO site_statistics (metric_name, metric_value) 
                    VALUES (:metric, :value) 
                    ON DUPLICATE KEY UPDATE metric_value = :value
                ", ['metric' => $metric, 'value' => $value]);
            }
            
            showAlert('Statistik berhasil diupdate!');
            
        } elseif ($action === 'cleanup_unused_tags') {
            // Delete tags that are not used in any articles
            $deletedCount = $adminDb->query("DELETE FROM tags WHERE id NOT IN (SELECT DISTINCT tag_id FROM article_tags WHERE tag_id IS NOT NULL)")->rowCount();
            showAlert("Berhasil menghapus {$deletedCount} tag yang tidak digunakan!");
            
        } elseif ($action === 'update_tag_counts') {
            // Update all tag usage counts
            $tags = $adminDb->fetchAll("SELECT id FROM tags");
            foreach ($tags as $tag) {
                $count = $adminDb->fetch("SELECT COUNT(*) as count FROM article_tags WHERE tag_id = :tag_id", ['tag_id' => $tag['id']])['count'];
                $adminDb->query("UPDATE tags SET usage_count = :count WHERE id = :id", ['count' => $count, 'id' => $tag['id']]);
            }
            showAlert('Tag counts berhasil diupdate!');
            
        } elseif ($action === 'reset_views') {
            // Reset all article view counts
            $adminDb->query("UPDATE articles SET views_count = 0");
            showAlert('View counts berhasil direset!');
            
        } elseif ($action === 'generate_slugs') {
            // Generate slugs for articles that don't have them
            $articles = $adminDb->fetchAll("SELECT id, title, slug FROM articles WHERE slug = '' OR slug IS NULL");
            $count = 0;
            foreach ($articles as $article) {
                $slug = generateSlug($article['title']);
                $adminDb->query("UPDATE articles SET slug = :slug WHERE id = :id", ['slug' => $slug, 'id' => $article['id']]);
                $count++;
            }
            showAlert("Berhasil generate {$count} slug artikel!");
            
        } elseif ($action === 'backup_database') {
            // Create database backup (simplified version)
            $backupFile = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
            $backupPath = '/tmp/' . $backupFile;
            
            // This is a simplified backup - in production you'd use mysqldump
            $tables = ['articles', 'categories', 'authors', 'tags', 'article_tags', 'newsletter_subscribers', 'site_statistics'];
            $backup = "-- Database Backup Created: " . date('Y-m-d H:i:s') . "\n\n";
            
            foreach ($tables as $table) {
                $backup .= "-- Table: {$table}\n";
                $backup .= "DROP TABLE IF EXISTS `{$table}`;\n";
                
                // Get table structure (simplified)
                $backup .= "-- Table structure for {$table} would go here\n";
                
                // Get table data
                $rows = $adminDb->fetchAll("SELECT * FROM {$table}");
                if (!empty($rows)) {
                    $backup .= "INSERT INTO `{$table}` VALUES\n";
                    $values = [];
                    foreach ($rows as $row) {
                        $values[] = "('" . implode("','", array_map('addslashes', $row)) . "')";
                    }
                    $backup .= implode(",\n", $values) . ";\n\n";
                }
            }
            
            file_put_contents($backupPath, $backup);
            
            // Offer download
            header('Content-Type: application/sql');
            header('Content-Disposition: attachment; filename="' . $backupFile . '"');
            header('Content-Length: ' . filesize($backupPath));
            readfile($backupPath);
            unlink($backupPath);
            exit;
        }
    } catch (Exception $e) {
        showAlert('Error: ' . $e->getMessage(), 'error');
    }
}

// Get system info
$systemInfo = [
    'php_version' => PHP_VERSION,
    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'mysql_version' => $adminDb->fetch("SELECT VERSION() as version")['version'] ?? 'Unknown',
    'disk_usage' => disk_free_space('/') ? round((disk_total_space('/') - disk_free_space('/')) / disk_total_space('/') * 100, 2) . '%' : 'Unknown',
    'memory_usage' => memory_get_usage(true) / 1024 / 1024 . ' MB',
    'uptime' => file_exists('/proc/uptime') ? explode(' ', file_get_contents('/proc/uptime'))[0] . ' seconds' : 'Unknown'
];

// Get database stats
$dbStats = [
    'total_articles' => $adminDb->fetch("SELECT COUNT(*) as count FROM articles")['count'],
    'total_categories' => $adminDb->fetch("SELECT COUNT(*) as count FROM categories")['count'],
    'total_authors' => $adminDb->fetch("SELECT COUNT(*) as count FROM authors")['count'],
    'total_tags' => $adminDb->fetch("SELECT COUNT(*) as count FROM tags")['count'],
    'unused_tags' => $adminDb->fetch("SELECT COUNT(*) as count FROM tags WHERE id NOT IN (SELECT DISTINCT tag_id FROM article_tags WHERE tag_id IS NOT NULL)")['count'],
    'total_subscribers' => $adminDb->fetch("SELECT COUNT(*) as count FROM newsletter_subscribers")['count'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Utilities - Admin kabarInovasi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', system-ui, sans-serif; }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Sidebar -->
    <div class="fixed inset-y-0 left-0 w-64 bg-white shadow-lg">
        <div class="flex items-center justify-center h-16 bg-blue-600">
            <div class="flex items-center space-x-2">
                <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="text-xl font-bold text-white">Admin Panel</span>
            </div>
        </div>
        
        <nav class="mt-8">
            <div class="px-4 py-2">
                <a href="<?php echo ADMIN_BASE_URL; ?>" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/>
                    </svg>
                    Dashboard
                </a>
            </div>
            
            <div class="px-4 py-2">
                <a href="<?php echo ADMIN_BASE_URL; ?>/articles.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5a2 2 0 00-2-2h-2"/>
                    </svg>
                    Artikel
                </a>
            </div>
            
            <div class="px-4 py-2">
                <a href="<?php echo ADMIN_BASE_URL; ?>/authors.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Penulis
                </a>
            </div>
            
            <div class="px-4 py-2">
                <a href="<?php echo ADMIN_BASE_URL; ?>/categories.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 713 12V7a4 4 0 714-4z"/>
                    </svg>
                    Kategori
                </a>
            </div>
            
            <div class="px-4 py-2">
                <a href="<?php echo ADMIN_BASE_URL; ?>/tags.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 713 12V7a4 4 0 714-4z"/>
                    </svg>
                    Tags
                </a>
            </div>
            
            <div class="px-4 py-2">
                <a href="<?php echo ADMIN_BASE_URL; ?>/newsletter.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    Newsletter
                </a>
            </div>
            
            <div class="px-4 py-2 mt-4 border-t border-gray-200 pt-4">
                <a href="<?php echo ADMIN_BASE_URL; ?>/utilities.php" class="flex items-center px-4 py-2 text-gray-700 bg-blue-50 rounded-lg">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Utilities
                </a>
            </div>
            
            <div class="px-4 py-2 mt-8">
                <a href="<?php echo ADMIN_BASE_URL; ?>/logout.php" class="flex items-center px-4 py-2 text-red-600 hover:bg-red-50 rounded-lg">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Logout
                </a>
            </div>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="ml-64 p-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Utilities & Maintenance</h1>
            <p class="text-gray-600 mt-2">Tools untuk maintenance dan optimasi website</p>
        </div>

        <?php displayAlert(); ?>

        <!-- System Information -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Sistem</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">PHP Version:</span>
                        <span class="font-medium"><?php echo $systemInfo['php_version']; ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Server:</span>
                        <span class="font-medium"><?php echo $systemInfo['server_software']; ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">MySQL Version:</span>
                        <span class="font-medium"><?php echo $systemInfo['mysql_version']; ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Memory Usage:</span>
                        <span class="font-medium"><?php echo $systemInfo['memory_usage']; ?></span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Database Statistics</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total Artikel:</span>
                        <span class="font-medium"><?php echo number_format($dbStats['total_articles']); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total Kategori:</span>
                        <span class="font-medium"><?php echo number_format($dbStats['total_categories']); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total Penulis:</span>
                        <span class="font-medium"><?php echo number_format($dbStats['total_authors']); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total Tags:</span>
                        <span class="font-medium"><?php echo number_format($dbStats['total_tags']); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Tags Tidak Digunakan:</span>
                        <span class="font-medium text-red-600"><?php echo number_format($dbStats['unused_tags']); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total Subscribers:</span>
                        <span class="font-medium"><?php echo number_format($dbStats['total_subscribers']); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Maintenance Actions -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Statistics -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center mb-4">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 ml-3">Update Statistik</h3>
                </div>
                <p class="text-gray-600 mb-4">Perbarui statistik website untuk dashboard</p>
                <form method="POST">
                    <input type="hidden" name="action" value="update_statistics">
                    <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700">
                        Update Statistik
                    </button>
                </form>
            </div>

            <!-- Clean Tags -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center mb-4">
                    <div class="p-2 bg-yellow-100 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 713 12V7a4 4 0 714-4z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 ml-3">Bersihkan Tags</h3>
                </div>
                <p class="text-gray-600 mb-4">Hapus tags yang tidak digunakan dalam artikel</p>
                <form method="POST" onsubmit="return confirm('Yakin ingin menghapus tags yang tidak digunakan?')">
                    <input type="hidden" name="action" value="cleanup_unused_tags">
                    <button type="submit" class="w-full bg-yellow-600 text-white py-2 px-4 rounded-lg hover:bg-yellow-700">
                        Bersihkan Tags
                    </button>
                </form>
            </div>

            <!-- Update Tag Counts -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center mb-4">
                    <div class="p-2 bg-green-100 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 ml-3">Update Tag Counts</h3>
                </div>
                <p class="text-gray-600 mb-4">Perbaiki hitungan penggunaan tags</p>
                <form method="POST">
                    <input type="hidden" name="action" value="update_tag_counts">
                    <button type="submit" class="w-full bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-700">
                        Update Counts
                    </button>
                </form>
            </div>

            <!-- Generate Slugs -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center mb-4">
                    <div class="p-2 bg-purple-100 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 ml-3">Generate Slugs</h3>
                </div>
                <p class="text-gray-600 mb-4">Buat slug untuk artikel yang belum memiliki slug</p>
                <form method="POST">
                    <input type="hidden" name="action" value="generate_slugs">
                    <button type="submit" class="w-full bg-purple-600 text-white py-2 px-4 rounded-lg hover:bg-purple-700">
                        Generate Slugs
                    </button>
                </form>
            </div>

            <!-- Reset Views -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center mb-4">
                    <div class="p-2 bg-red-100 rounded-lg">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 ml-3">Reset Views</h3>
                </div>
                <p class="text-gray-600 mb-4">Reset semua hitungan view artikel ke 0</p>
                <form method="POST" onsubmit="return confirm('Yakin ingin reset semua view count? Aksi ini tidak bisa dibatalkan!')">
                    <input type="hidden" name="action" value="reset_views">
                    <button type="submit" class="w-full bg-red-600 text-white py-2 px-4 rounded-lg hover:bg-red-700">
                        Reset Views
                    </button>
                </form>
            </div>

            <!-- Database Backup -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center mb-4">
                    <div class="p-2 bg-indigo-100 rounded-lg">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 ml-3">Backup Database</h3>
                </div>
                <p class="text-gray-600 mb-4">Download backup database dalam format SQL</p>
                <form method="POST">
                    <input type="hidden" name="action" value="backup_database">
                    <button type="submit" class="w-full bg-indigo-600 text-white py-2 px-4 rounded-lg hover:bg-indigo-700">
                        Download Backup
                    </button>
                </form>
            </div>
        </div>

        <!-- Warning Notice -->
        <div class="mt-8 bg-yellow-50 border border-yellow-200 rounded-lg p-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">Peringatan</h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <ul class="list-disc pl-5 space-y-1">
                            <li>Selalu backup database sebelum melakukan operasi maintenance</li>
                            <li>Beberapa operasi seperti reset views tidak dapat dibatalkan</li>
                            <li>Lakukan maintenance pada waktu traffic rendah</li>
                            <li>Pastikan tidak ada user yang sedang menulis artikel saat maintenance</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto refresh system info every 30 seconds
        setInterval(function() {
            // You can implement AJAX refresh for system info here
        }, 30000);
    </script>
</body>
</html>