<?php
// admin/articles.php
require_once 'includes/config.php';
requireLogin();

// Handle form submissions
if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    try {
        if ($action === 'create' || $action === 'update') {
            $title = sanitizeInput($_POST['title']);
            $slug = !empty($_POST['slug']) ? sanitizeInput($_POST['slug']) : generateSlug($title);
            $excerpt = sanitizeInput($_POST['excerpt']);
            $content = $_POST['content']; // Allow HTML in content
            $featured_image = sanitizeInput($_POST['featured_image']);
            $category_id = (int)$_POST['category_id'];
            $author_id = (int)$_POST['author_id'];
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;
            $is_published = isset($_POST['is_published']) ? 1 : 0;
            $read_time = (int)$_POST['read_time'];
            $published_at = !empty($_POST['published_at']) ? $_POST['published_at'] : null;
            $tags = $_POST['tags'] ?? [];
            
            if ($action === 'create') {
                $sql = "INSERT INTO articles (title, slug, excerpt, content, featured_image, category_id, author_id, is_featured, is_published, read_time, published_at) 
                        VALUES (:title, :slug, :excerpt, :content, :featured_image, :category_id, :author_id, :is_featured, :is_published, :read_time, :published_at)";
                $adminDb->query($sql, [
                    'title' => $title,
                    'slug' => $slug,
                    'excerpt' => $excerpt,
                    'content' => $content,
                    'featured_image' => $featured_image,
                    'category_id' => $category_id,
                    'author_id' => $author_id,
                    'is_featured' => $is_featured,
                    'is_published' => $is_published,
                    'read_time' => $read_time,
                    'published_at' => $published_at
                ]);
                
                $articleId = $adminDb->lastInsertId();
                
                // Handle tags
                if (!empty($tags)) {
                    foreach ($tags as $tagId) {
                        $adminDb->query("INSERT INTO article_tags (article_id, tag_id) VALUES (:article_id, :tag_id)", [
                            'article_id' => $articleId,
                            'tag_id' => $tagId
                        ]);
                    }
                    
                    // Update tag usage count
                    foreach ($tags as $tagId) {
                        $adminDb->query("UPDATE tags SET usage_count = (SELECT COUNT(*) FROM article_tags WHERE tag_id = :tag_id) WHERE id = :tag_id", [
                            'tag_id' => $tagId
                        ]);
                    }
                }
                
                showAlert('Artikel berhasil dibuat!');
            } else {
                $id = (int)$_POST['id'];
                $sql = "UPDATE articles SET title = :title, slug = :slug, excerpt = :excerpt, content = :content, 
                        featured_image = :featured_image, category_id = :category_id, author_id = :author_id, 
                        is_featured = :is_featured, is_published = :is_published, read_time = :read_time, 
                        published_at = :published_at WHERE id = :id";
                $adminDb->query($sql, [
                    'title' => $title,
                    'slug' => $slug,
                    'excerpt' => $excerpt,
                    'content' => $content,
                    'featured_image' => $featured_image,
                    'category_id' => $category_id,
                    'author_id' => $author_id,
                    'is_featured' => $is_featured,
                    'is_published' => $is_published,
                    'read_time' => $read_time,
                    'published_at' => $published_at,
                    'id' => $id
                ]);
                
                // Update tags - first delete existing tags
                $adminDb->query("DELETE FROM article_tags WHERE article_id = :article_id", ['article_id' => $id]);
                
                // Add new tags
                if (!empty($tags)) {
                    foreach ($tags as $tagId) {
                        $adminDb->query("INSERT INTO article_tags (article_id, tag_id) VALUES (:article_id, :tag_id)", [
                            'article_id' => $id,
                            'tag_id' => $tagId
                        ]);
                    }
                }
                
                // Update tag usage counts for all tags
                $allTags = $adminDb->fetchAll("SELECT id FROM tags");
                foreach ($allTags as $tag) {
                    $adminDb->query("UPDATE tags SET usage_count = (SELECT COUNT(*) FROM article_tags WHERE tag_id = :tag_id) WHERE id = :tag_id", [
                        'tag_id' => $tag['id']
                    ]);
                }
                
                showAlert('Artikel berhasil diupdate!');
            }
        } elseif ($action === 'delete') {
            $id = (int)$_POST['id'];
            $adminDb->query("DELETE FROM articles WHERE id = :id", ['id' => $id]);
            showAlert('Artikel berhasil dihapus!');
        }
    } catch (Exception $e) {
        showAlert('Error: ' . $e->getMessage(), 'error');
    }
}

// Get edit data
$editArticle = null;
$editArticleTags = [];
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $editArticle = $adminDb->fetch("SELECT * FROM articles WHERE id = :id", ['id' => $editId]);
    
    // Get article tags
    if ($editArticle) {
        $editArticleTags = $adminDb->fetchAll("
            SELECT tag_id FROM article_tags WHERE article_id = :article_id
        ", ['article_id' => $editId]);
        $editArticleTags = array_column($editArticleTags, 'tag_id');
    }
}

// Get all articles
$articles = $adminDb->fetchAll("
    SELECT a.*, c.name as category_name, au.name as author_name 
    FROM articles a 
    LEFT JOIN categories c ON a.category_id = c.id 
    LEFT JOIN authors au ON a.author_id = au.id 
    ORDER BY a.created_at DESC
");

// Get categories and authors for form
$categories = $adminDb->fetchAll("SELECT * FROM categories ORDER BY name");
$authors = $adminDb->fetchAll("SELECT * FROM authors WHERE is_active = 1 ORDER BY name");
$tags = $adminDb->fetchAll("SELECT * FROM tags ORDER BY name");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Artikel - Admin kabarInovasi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Quill.js CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/quill/1.3.7/quill.snow.min.css" rel="stylesheet">
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', system-ui, sans-serif; }
        
        /* Custom Quill styling */
        .ql-editor {
            min-height: 300px;
            font-family: 'Inter', system-ui, sans-serif;
            font-size: 14px;
            line-height: 1.6;
        }
        
        .ql-toolbar {
            border-top: 1px solid #d1d5db;
            border-left: 1px solid #d1d5db;
            border-right: 1px solid #d1d5db;
            border-radius: 0.5rem 0.5rem 0 0;
        }
        
        .ql-container {
            border-bottom: 1px solid #d1d5db;
            border-left: 1px solid #d1d5db;
            border-right: 1px solid #d1d5db;
            border-radius: 0 0 0.5rem 0.5rem;
        }
        
        .ql-editor:focus {
            outline: none;
        }
        
        .ql-snow .ql-picker {
            color: #374151;
        }
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
                <a href="<?php echo ADMIN_BASE_URL; ?>/articles.php" class="flex items-center px-4 py-2 text-gray-700 bg-blue-50 rounded-lg">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5a2 2 0 00-2-2h-2"/>
                    </svg>
                    Artikel
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
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Kelola Artikel</h1>
                    <p class="text-gray-600 mt-2">Buat dan kelola artikel website</p>
                </div>
                <button onclick="showCreateForm()" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                    + Buat Artikel Baru
                </button>
            </div>
        </div>

        <?php displayAlert(); ?>

        <!-- Form Modal -->
        <div id="articleForm" class="<?php echo $editArticle ? 'block' : 'hidden'; ?> bg-white rounded-lg shadow-lg p-6 mb-8">
            <h2 id="formTitle" class="text-xl font-bold text-gray-900 mb-6">
                <?php echo $editArticle ? 'Edit Artikel' : 'Buat Artikel Baru'; ?>
            </h2>
            
            <form method="POST" class="space-y-6" onsubmit="return submitForm()">
                <input type="hidden" name="action" id="formAction" value="<?php echo $editArticle ? 'update' : 'create'; ?>">
                <?php if ($editArticle): ?>
                <input type="hidden" name="id" value="<?php echo $editArticle['id']; ?>">
                <?php endif; ?>
                
                <!-- Hidden field for content -->
                <input type="hidden" name="content" id="contentInput">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Judul Artikel *</label>
                        <input type="text" name="title" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               value="<?php echo htmlspecialchars($editArticle['title'] ?? ''); ?>"
                               onchange="generateSlug(this.value)"
                               placeholder="Masukkan judul artikel yang menarik...">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Slug URL</label>
                        <input type="text" name="slug" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               value="<?php echo htmlspecialchars($editArticle['slug'] ?? ''); ?>"
                               placeholder="slug-artikel-otomatis">
                        <p class="text-xs text-gray-500 mt-1">Kosongkan untuk auto-generate dari judul</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kategori *</label>
                        <select name="category_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Pilih Kategori</option>
                            <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>" 
                                    <?php echo ($editArticle && $editArticle['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Penulis *</label>
                        <select name="author_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Pilih Penulis</option>
                            <?php foreach ($authors as $author): ?>
                            <option value="<?php echo $author['id']; ?>"
                                    <?php echo ($editArticle && $editArticle['author_id'] == $author['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($author['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Waktu Baca (menit) *</label>
                        <input type="number" name="read_time" min="1" max="60" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               value="<?php echo $editArticle['read_time'] ?? 5; ?>"
                               placeholder="5">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Gambar Utama (URL) *</label>
                    <input type="url" name="featured_image" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                           value="<?php echo htmlspecialchars($editArticle['featured_image'] ?? ''); ?>"
                           placeholder="https://images.unsplash.com/photo-example.jpg"
                           onchange="previewImage(this.value)">
                    
                    <!-- Image Preview -->
                    <div id="imagePreview" class="mt-3 hidden">
                        <p class="text-sm text-gray-600 mb-2">Preview Gambar:</p>
                        <img id="previewImg" src="" alt="Preview" class="w-full max-w-md h-48 object-cover rounded-lg border">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Excerpt/Ringkasan *</label>
                    <textarea name="excerpt" rows="3" required
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                              placeholder="Tulis ringkasan singkat yang menarik untuk artikel ini..."><?php echo htmlspecialchars($editArticle['excerpt'] ?? ''); ?></textarea>
                    <p class="text-xs text-gray-500 mt-1">Ringkasan akan ditampilkan di halaman utama dan hasil pencarian</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Konten Artikel *</label>
                    <div id="quillEditor" style="height: 400px;"></div>
                    <p class="text-xs text-gray-500 mt-1">Gunakan toolbar di atas untuk formatting teks</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tags</label>
                    <div class="border border-gray-300 rounded-lg p-3 max-h-40 overflow-y-auto bg-gray-50">
                        <?php if (!empty($tags)): ?>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                            <?php foreach ($tags as $tag): ?>
                            <label class="flex items-center space-x-2 p-2 hover:bg-white rounded cursor-pointer">
                                <input type="checkbox" name="tags[]" value="<?php echo $tag['id']; ?>"
                                       <?php echo in_array($tag['id'], $editArticleTags) ? 'checked' : ''; ?>
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="text-sm text-gray-700"><?php echo htmlspecialchars($tag['name']); ?></span>
                                <span class="text-xs text-gray-500 ml-auto"><?php echo $tag['usage_count']; ?>x</span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <p class="text-sm text-gray-500 text-center py-4">
                            Belum ada tags. <a href="<?php echo ADMIN_BASE_URL; ?>/tags.php" class="text-blue-600 hover:text-blue-800">Buat tags baru</a>
                        </p>
                        <?php endif; ?>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Pilih tags yang relevan dengan artikel ini untuk membantu kategorisasi</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Publikasi</label>
                        <input type="datetime-local" name="published_at"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               value="<?php echo $editArticle && $editArticle['published_at'] ? formatDateInput($editArticle['published_at']) : ''; ?>">
                        <p class="text-xs text-gray-500 mt-1">Kosongkan untuk menggunakan waktu sekarang saat dipublikasi</p>
                    </div>
                    
                    <div class="flex flex-col justify-center space-y-4">
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="is_featured" value="1"
                                   <?php echo ($editArticle && $editArticle['is_featured']) ? 'checked' : ''; ?>
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="text-sm text-gray-700">Artikel Unggulan</span>
                            <span class="text-xs text-gray-500">(akan ditampilkan di banner utama)</span>
                        </label>
                        
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="is_published" value="1"
                                   <?php echo ($editArticle && $editArticle['is_published']) ? 'checked' : ''; ?>
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="text-sm text-gray-700">Terbitkan Sekarang</span>
                            <span class="text-xs text-gray-500">(artikel akan visible di website)</span>
                        </label>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                    <button type="button" onclick="hideForm()" class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <?php echo $editArticle ? 'Update Artikel' : 'Simpan Artikel'; ?>
                    </button>
                </div>
            </form>
        </div>

        <!-- Articles List -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Daftar Artikel (<?php echo count($articles); ?>)</h3>
            </div>
            
            <?php if (!empty($articles)): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Judul</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kategori</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Penulis</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Views</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($articles as $article): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <?php if ($article['is_featured']): ?>
                                    <svg class="w-4 h-4 text-yellow-500 mr-2" fill="currentColor" viewBox="0 0 20 20" title="Artikel Unggulan">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                    <?php endif; ?>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900 max-w-xs truncate">
                                            <?php echo htmlspecialchars($article['title']); ?>
                                        </div>
                                        <div class="text-sm text-gray-500 max-w-xs truncate">
                                            <?php echo htmlspecialchars(substr($article['excerpt'], 0, 60)) . '...'; ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <?php echo htmlspecialchars($article['category_name'] ?? 'No Category'); ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <?php echo htmlspecialchars($article['author_name'] ?? 'No Author'); ?>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $article['is_published'] ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                    <?php echo $article['is_published'] ? 'Terbit' : 'Draft'; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <span class="text-gray-600"><?php echo number_format($article['views_count']); ?></span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <?php echo date('d/m/Y', strtotime($article['created_at'])); ?>
                            </td>
                            <td class="px-6 py-4 text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="<?php echo FRONTEND_BASE_URL; ?>/article/<?php echo $article['slug']; ?>" 
                                       target="_blank" class="text-blue-600 hover:text-blue-900" title="Lihat Artikel">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                    <a href="?edit=<?php echo $article['id']; ?>" class="text-indigo-600 hover:text-indigo-900" title="Edit Artikel">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                        </svg>
                                    </a>
                                    <button onclick="deleteArticle(<?php echo $article['id']; ?>, '<?php echo addslashes($article['title']); ?>')" 
                                            class="text-red-600 hover:text-red-900" title="Hapus Artikel">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="p-6 text-center">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5a2 2 0 00-2-2h-2"/>
                </svg>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Belum Ada Artikel</h3>
                <p class="text-gray-600 mb-6">Mulai buat artikel pertama untuk mengisi website Anda</p>
                <button onclick="showCreateForm()" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700">
                    Buat Artikel Pertama
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Delete Form -->
    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id" id="deleteId">
    </form>

    <!-- Quill.js JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/quill/1.3.7/quill.min.js"></script>
    
    <script>
        // Initialize Quill editor
        let quill;
        
        function initializeQuill() {
            quill = new Quill('#quillEditor', {
                theme: 'snow',
                placeholder: 'Tulis konten artikel di sini...',
                modules: {
                    toolbar: [
                        [{ 'header': [1, 2, 3, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ 'color': [] }, { 'background': [] }],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        [{ 'indent': '-1'}, { 'indent': '+1' }],
                        [{ 'align': [] }],
                        ['link', 'blockquote', 'code-block'],
                        ['clean']
                    ]
                }
            });
            
            // Load existing content if editing
            <?php if ($editArticle && !empty($editArticle['content'])): ?>
            quill.root.innerHTML = <?php echo json_encode($editArticle['content']); ?>;
            <?php endif; ?>
        }

        function showCreateForm() {
            document.getElementById('articleForm').classList.remove('hidden');
            document.getElementById('formTitle').textContent = 'Buat Artikel Baru';
            document.getElementById('formAction').value = 'create';
            document.querySelector('form').reset();
            
            // Initialize Quill if not already initialized
            if (!quill) {
                setTimeout(initializeQuill, 100);
            } else {
                quill.setText('');
            }
            
            hideImagePreview();
        }

        function hideForm() {
            document.getElementById('articleForm').classList.add('hidden');
            window.location.href = '<?php echo ADMIN_BASE_URL; ?>/articles.php';
        }

        function generateSlug(title) {
            const slug = title.toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/[\s-]+/g, '-')
                .trim()
                .replace(/^-+|-+$/g, '');
            document.querySelector('input[name="slug"]').value = slug;
        }
        
        function previewImage(url) {
            const preview = document.getElementById('imagePreview');
            const img = document.getElementById('previewImg');
            
            if (url && url.trim() !== '') {
                img.src = url;
                img.onload = function() {
                    preview.classList.remove('hidden');
                };
                img.onerror = function() {
                    hideImagePreview();
                };
            } else {
                hideImagePreview();
            }
        }
        
        function hideImagePreview() {
            document.getElementById('imagePreview').classList.add('hidden');
        }

        function submitForm() {
            // Get content from Quill editor
            if (quill) {
                const content = quill.root.innerHTML;
                document.getElementById('contentInput').value = content;
                
                // Basic validation
                if (content.trim() === '<p><br></p>' || content.trim() === '') {
                    alert('Konten artikel harus diisi!');
                    return false;
                }
            }
            return true;
        }

        function deleteArticle(id, title) {
            if (confirm(`Apakah Anda yakin ingin menghapus artikel "${title}"?\n\nTindakan ini tidak dapat dibatalkan.`)) {
                document.getElementById('deleteId').value = id;
                document.getElementById('deleteForm').submit();
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Show form if editing
            <?php if ($editArticle): ?>
            document.getElementById('articleForm').classList.remove('hidden');
            setTimeout(initializeQuill, 100);
            
            // Show image preview if exists
            <?php if (!empty($editArticle['featured_image'])): ?>
            previewImage('<?php echo addslashes($editArticle['featured_image']); ?>');
            <?php endif; ?>
            <?php endif; ?>
        });
    </script>
</body>
</html>