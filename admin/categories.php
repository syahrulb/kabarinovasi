<?php
// admin/categories.php
require_once 'includes/config.php';
requireLogin();

// Handle form submissions
if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    try {
        if ($action === 'create' || $action === 'update') {
            $name = sanitizeInput($_POST['name']);
            $slug = !empty($_POST['slug']) ? sanitizeInput($_POST['slug']) : generateSlug($name);
            $description = sanitizeInput($_POST['description']);
            $color = sanitizeInput($_POST['color']);
            
            if ($action === 'create') {
                $sql = "INSERT INTO categories (name, slug, description, color) VALUES (:name, :slug, :description, :color)";
                $adminDb->query($sql, [
                    'name' => $name,
                    'slug' => $slug,
                    'description' => $description,
                    'color' => $color
                ]);
                showAlert('Kategori berhasil dibuat!');
            } else {
                $id = (int)$_POST['id'];
                $sql = "UPDATE categories SET name = :name, slug = :slug, description = :description, color = :color WHERE id = :id";
                $adminDb->query($sql, [
                    'name' => $name,
                    'slug' => $slug,
                    'description' => $description,
                    'color' => $color,
                    'id' => $id
                ]);
                showAlert('Kategori berhasil diupdate!');
            }
        } elseif ($action === 'delete') {
            $id = (int)$_POST['id'];
            // Check if category has articles
            $articleCount = $adminDb->fetch("SELECT COUNT(*) as count FROM articles WHERE category_id = :id", ['id' => $id])['count'];
            if ($articleCount > 0) {
                showAlert("Tidak dapat menghapus kategori yang masih memiliki {$articleCount} artikel!", 'error');
            } else {
                $adminDb->query("DELETE FROM categories WHERE id = :id", ['id' => $id]);
                showAlert('Kategori berhasil dihapus!');
            }
        }
    } catch (Exception $e) {
        showAlert('Error: ' . $e->getMessage(), 'error');
    }
}

// Get edit data
$editCategory = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $editCategory = $adminDb->fetch("SELECT * FROM categories WHERE id = :id", ['id' => $editId]);
}

// Get all categories with article count
$categories = $adminDb->fetchAll("
    SELECT c.*, COUNT(a.id) as article_count 
    FROM categories c 
    LEFT JOIN articles a ON c.id = a.category_id 
    GROUP BY c.id 
    ORDER BY c.name
");

// Color options
$colorOptions = [
    '#3b82f6' => 'Biru',
    '#10b981' => 'Hijau',
    '#f59e0b' => 'Kuning',
    '#ef4444' => 'Merah',
    '#8b5cf6' => 'Ungu',
    '#06b6d4' => 'Cyan',
    '#84cc16' => 'Hijau Lime',
    '#f97316' => 'Orange',
    '#ec4899' => 'Pink',
    '#6b7280' => 'Abu-abu'
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kategori - Admin kabarInovasi</title>
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
                <a href="<?php echo ADMIN_BASE_URL; ?>/categories.php" class="flex items-center px-4 py-2 text-gray-700 bg-blue-50 rounded-lg">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    Kategori
                </a>
            </div>
            
            <div class="px-4 py-2">
                <a href="<?php echo ADMIN_BASE_URL; ?>/tags.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
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
                    <h1 class="text-3xl font-bold text-gray-900">Kelola Kategori</h1>
                    <p class="text-gray-600 mt-2">Buat dan kelola kategori artikel</p>
                </div>
                <button onclick="showCreateForm()" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                    + Buat Kategori Baru
                </button>
            </div>
        </div>

        <?php displayAlert(); ?>

        <!-- Form Modal -->
        <div id="categoryForm" class="<?php echo $editCategory ? 'block' : 'hidden'; ?> bg-white rounded-lg shadow-lg p-6 mb-8">
            <h2 id="formTitle" class="text-xl font-bold text-gray-900 mb-6">
                <?php echo $editCategory ? 'Edit Kategori' : 'Buat Kategori Baru'; ?>
            </h2>
            
            <form method="POST" class="space-y-6">
                <input type="hidden" name="action" id="formAction" value="<?php echo $editCategory ? 'update' : 'create'; ?>">
                <?php if ($editCategory): ?>
                <input type="hidden" name="id" value="<?php echo $editCategory['id']; ?>">
                <?php endif; ?>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Kategori</label>
                        <input type="text" name="name" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               value="<?php echo htmlspecialchars($editCategory['name'] ?? ''); ?>"
                               onchange="generateSlug(this.value)">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Slug URL</label>
                        <input type="text" name="slug" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               value="<?php echo htmlspecialchars($editCategory['slug'] ?? ''); ?>">
                        <p class="text-xs text-gray-500 mt-1">Kosongkan untuk auto-generate dari nama</p>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                    <textarea name="description" rows="3" required
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                              placeholder="Deskripsi kategori..."><?php echo htmlspecialchars($editCategory['description'] ?? ''); ?></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Warna Kategori</label>
                    <div class="grid grid-cols-5 md:grid-cols-10 gap-3">
                        <?php foreach ($colorOptions as $colorValue => $colorName): ?>
                        <label class="flex flex-col items-center cursor-pointer">
                            <input type="radio" name="color" value="<?php echo $colorValue; ?>" 
                                   <?php echo ($editCategory && $editCategory['color'] === $colorValue) || (!$editCategory && $colorValue === '#3b82f6') ? 'checked' : ''; ?>
                                   class="sr-only">
                            <div class="w-8 h-8 rounded-full border-2 border-gray-300 hover:border-gray-400 transition-colors flex items-center justify-center color-option"
                                 style="background-color: <?php echo $colorValue; ?>;">
                                <svg class="w-4 h-4 text-white hidden check-icon" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <span class="text-xs text-gray-600 mt-1"><?php echo $colorName; ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="hideForm()" class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                        Batal
                    </button>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <?php echo $editCategory ? 'Update Kategori' : 'Simpan Kategori'; ?>
                    </button>
                </div>
            </form>
        </div>

        <!-- Categories Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($categories as $category): ?>
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="h-4" style="background-color: <?php echo htmlspecialchars($category['color']); ?>;"></div>
                <div class="p-6">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($category['name']); ?></h3>
                        <div class="w-6 h-6 rounded-full" style="background-color: <?php echo htmlspecialchars($category['color']); ?>;"></div>
                    </div>
                    
                    <p class="text-gray-600 mb-4 text-sm"><?php echo htmlspecialchars($category['description']); ?></p>
                    
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-sm text-gray-500">Slug: <?php echo htmlspecialchars($category['slug']); ?></span>
                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs font-semibold">
                            <?php echo $category['article_count']; ?> artikel
                        </span>
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <div class="text-xs text-gray-500">
                            Dibuat: <?php echo date('d/m/Y', strtotime($category['created_at'])); ?>
                        </div>
                        <div class="flex space-x-2">
                            <a href="<?php echo FRONTEND_BASE_URL; ?>/category/<?php echo $category['slug']; ?>" 
                               target="_blank" 
                               class="text-blue-600 hover:text-blue-800 text-sm">Lihat</a>
                            <a href="?edit=<?php echo $category['id']; ?>" 
                               class="text-indigo-600 hover:text-indigo-900 text-sm">Edit</a>
                            <button onclick="deleteCategory(<?php echo $category['id']; ?>, '<?php echo addslashes($category['name']); ?>', <?php echo $category['article_count']; ?>)" 
                                    class="text-red-600 hover:text-red-900 text-sm">Hapus</button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <?php if (empty($categories)): ?>
        <div class="text-center py-12">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
            </svg>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">Belum Ada Kategori</h3>
            <p class="text-gray-600 mb-6">Buat kategori pertama untuk mengorganisir artikel Anda</p>
            <button onclick="showCreateForm()" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700">
                Buat Kategori Pertama
            </button>
        </div>
        <?php endif; ?>
    </div>

    <!-- Delete Form -->
    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id" id="deleteId">
    </form>

    <script>
        function showCreateForm() {
            document.getElementById('categoryForm').classList.remove('hidden');
            document.getElementById('formTitle').textContent = 'Buat Kategori Baru';
            document.getElementById('formAction').value = 'create';
            document.querySelector('form').reset();
            // Set default color
            document.querySelector('input[name="color"][value="#3b82f6"]').checked = true;
            updateColorSelection();
        }

        function hideForm() {
            document.getElementById('categoryForm').classList.add('hidden');
            window.location.href = '<?php echo ADMIN_BASE_URL; ?>/categories.php';
        }

        function generateSlug(name) {
            const slug = name.toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/[\s-]+/g, '-')
                .trim()
                .replace(/^-+|-+$/g, '');
            document.querySelector('input[name="slug"]').value = slug;
        }

        function deleteCategory(id, name, articleCount) {
            if (articleCount > 0) {
                alert(`Tidak dapat menghapus kategori "${name}" karena masih memiliki ${articleCount} artikel. Hapus atau pindahkan artikel terlebih dahulu.`);
                return;
            }
            
            if (confirm(`Apakah Anda yakin ingin menghapus kategori "${name}"?`)) {
                document.getElementById('deleteId').value = id;
                document.getElementById('deleteForm').submit();
            }
        }

        function updateColorSelection() {
            // Hide all check icons
            document.querySelectorAll('.check-icon').forEach(icon => {
                icon.classList.add('hidden');
            });
            
            // Show check icon for selected color
            const selectedRadio = document.querySelector('input[name="color"]:checked');
            if (selectedRadio) {
                const checkIcon = selectedRadio.parentElement.querySelector('.check-icon');
                if (checkIcon) {
                    checkIcon.classList.remove('hidden');
                }
            }
        }

        // Handle color selection
        document.querySelectorAll('input[name="color"]').forEach(radio => {
            radio.addEventListener('change', updateColorSelection);
        });

        // Initialize color selection
        updateColorSelection();

        // Show form if editing
        <?php if ($editCategory): ?>
        document.getElementById('categoryForm').classList.remove('hidden');
        <?php endif; ?>
    </script>
</body>
</html>