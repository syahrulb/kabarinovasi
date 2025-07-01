<?php
// admin/index.php
require_once 'includes/config.php';
requireLogin();

// Get statistics
try {
    $stats = [
        'total_articles' => $adminDb->fetch("SELECT COUNT(*) as count FROM articles")['count'],
        'published_articles' => $adminDb->fetch("SELECT COUNT(*) as count FROM articles WHERE is_published = 1")['count'],
        'draft_articles' => $adminDb->fetch("SELECT COUNT(*) as count FROM articles WHERE is_published = 0")['count'],
        'total_categories' => $adminDb->fetch("SELECT COUNT(*) as count FROM categories")['count'],
        'total_tags' => $adminDb->fetch("SELECT COUNT(*) as count FROM tags")['count'],
        'newsletter_subscribers' => $adminDb->fetch("SELECT COUNT(*) as count FROM newsletter_subscribers WHERE is_active = 1")['count'],
        'total_views' => $adminDb->fetch("SELECT SUM(views_count) as total FROM articles")['total'] ?? 0,
    ];
    
    // Recent articles
    $recentArticles = $adminDb->fetchAll("
        SELECT a.*, c.name as category_name, au.name as author_name 
        FROM articles a 
        LEFT JOIN categories c ON a.category_id = c.id 
        LEFT JOIN authors au ON a.author_id = au.id 
        ORDER BY a.created_at DESC 
        LIMIT 5
    ");
    
} catch (Exception $e) {
    $stats = array_fill_keys(['total_articles', 'published_articles', 'draft_articles', 'total_categories', 'total_tags', 'newsletter_subscribers', 'total_views'], 0);
    $recentArticles = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - kabarInovasi</title>
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
                <a href="<?php echo ADMIN_BASE_URL; ?>" class="flex items-center px-4 py-2 text-gray-700 bg-blue-50 rounded-lg">
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    Kategori
                </a>
            </div>
            
            <div class="px-4 py-2">
                <a href="<?php echo ADMIN_BASE_URL; ?>/tags.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 713 12V7a4 4 0 014-4z"/>
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
            
            <div class="px-4 py-2 mt-8">
                <a href="<?php echo FRONTEND_BASE_URL; ?>" target="_blank" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    Lihat Website
                </a>
            </div>
            
            <div class="px-4 py-2">
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
            <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
            <p class="text-gray-600 mt-2">Selamat datang, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>!</p>
        </div>

        <?php displayAlert(); ?>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5a2 2 0 00-2-2h-2"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Artikel</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($stats['total_articles']); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Artikel Terbit</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($stats['published_articles']); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-yellow-100 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Draft</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($stats['draft_articles']); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-purple-100 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Views</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($stats['total_views']); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Kategori</h3>
                <p class="text-3xl font-bold text-blue-600"><?php echo number_format($stats['total_categories']); ?></p>
                <p class="text-sm text-gray-600">Total kategori</p>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Tags</h3>
                <p class="text-3xl font-bold text-green-600"><?php echo number_format($stats['total_tags']); ?></p>
                <p class="text-sm text-gray-600">Total tags</p>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Subscribers</h3>
                <p class="text-3xl font-bold text-purple-600"><?php echo number_format($stats['newsletter_subscribers']); ?></p>
                <p class="text-sm text-gray-600">Newsletter subscribers</p>
            </div>
        </div>

        <!-- Recent Articles -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Artikel Terbaru</h3>
            </div>
            <div class="p-6">
                <?php if (!empty($recentArticles)): ?>
                <div class="space-y-4">
                    <?php foreach ($recentArticles as $article): ?>
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div class="flex-1">
                            <h4 class="font-semibold text-gray-900"><?php echo htmlspecialchars($article['title']); ?></h4>
                            <div class="flex items-center mt-2 text-sm text-gray-600">
                                <span class="<?php echo $article['is_published'] ? 'text-green-600' : 'text-yellow-600'; ?>">
                                    <?php echo $article['is_published'] ? 'Terbit' : 'Draft'; ?>
                                </span>
                                <span class="mx-2">•</span>
                                <span><?php echo htmlspecialchars($article['category_name'] ?? 'No Category'); ?></span>
                                <span class="mx-2">•</span>
                                <span><?php echo formatDate($article['created_at']); ?></span>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="text-sm text-gray-500"><?php echo number_format($article['views_count']); ?> views</span>
                            <a href="<?php echo ADMIN_BASE_URL; ?>/articles.php?edit=<?php echo $article['id']; ?>" 
                               class="text-blue-600 hover:text-blue-800">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p class="text-gray-500 text-center py-8">Belum ada artikel.</p>
                <?php endif; ?>
                
                <div class="mt-6 text-center">
                    <a href="<?php echo ADMIN_BASE_URL; ?>/articles.php" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Kelola Semua Artikel
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-refresh stats every 30 seconds
        setInterval(function() {
            // You can implement AJAX refresh here if needed
        }, 30000);
    </script>
</body>
</html>