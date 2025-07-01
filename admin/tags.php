<?php
// admin/tags.php
require_once 'includes/config.php';
requireLogin();

// Handle form submissions
if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    try {
        if ($action === 'create' || $action === 'update') {
            $name = sanitizeInput($_POST['name']);
            $slug = !empty($_POST['slug']) ? sanitizeInput($_POST['slug']) : generateSlug($name);
            
            if ($action === 'create') {
                $sql = "INSERT INTO tags (name, slug) VALUES (:name, :slug)";
                $adminDb->query($sql, [
                    'name' => $name,
                    'slug' => $slug
                ]);
                showAlert('Tag berhasil dibuat!');
            } else {
                $id = (int)$_POST['id'];
                $sql = "UPDATE tags SET name = :name, slug = :slug WHERE id = :id";
                $adminDb->query($sql, [
                    'name' => $name,
                    'slug' => $slug,
                    'id' => $id
                ]);
                showAlert('Tag berhasil diupdate!');
            }
        } elseif ($action === 'delete') {
            $id = (int)$_POST['id'];
            // Check if tag has articles
            $articleCount = $adminDb->fetch("SELECT COUNT(*) as count FROM article_tags WHERE tag_id = :id", ['id' => $id])['count'];
            if ($articleCount > 0) {
                showAlert("Tidak dapat menghapus tag yang masih digunakan dalam {$articleCount} artikel!", 'error');
            } else {
                $adminDb->query("DELETE FROM tags WHERE id = :id", ['id' => $id]);
                showAlert('Tag berhasil dihapus!');
            }
        } elseif ($action === 'bulk_delete') {
            $tagIds = $_POST['tag_ids'] ?? [];
            if (!empty($tagIds)) {
                $placeholders = str_repeat('?,', count($tagIds) - 1) . '?';
                $articleCount = $adminDb->fetch("SELECT COUNT(*) as count FROM article_tags WHERE tag_id IN ($placeholders)", $tagIds)['count'];
                
                if ($articleCount > 0) {
                    showAlert("Tidak dapat menghapus tag yang masih digunakan dalam artikel!", 'error');
                } else {
                    $adminDb->query("DELETE FROM tags WHERE id IN ($placeholders)", $tagIds);
                    showAlert(count($tagIds) . ' tag berhasil dihapus!');
                }
            }
        }
    } catch (Exception $e) {
        showAlert('Error: ' . $e->getMessage(), 'error');
    }
}

// Get edit data
$editTag = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $editTag = $adminDb->fetch("SELECT * FROM tags WHERE id = :id", ['id' => $editId]);
}

// Get all tags with usage count
$tags = $adminDb->fetchAll("
    SELECT t.*, COUNT(at.article_id) as article_count 
    FROM tags t 
    LEFT JOIN article_tags at ON t.id = at.tag_id 
    GROUP BY t.id 
    ORDER BY t.usage_count DESC, t.name ASC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Tags - Admin kabarInovasi</title>
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
                <a href="<?php echo ADMIN_BASE_URL; ?>/categories.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    Kategori
                </a>
            </div>
            
            <div class="px-4 py-2">
                <a href="<?php echo ADMIN_BASE_URL; ?>/tags.php" class="flex items-center px-4 py-2 text-gray-700 bg-blue-50 rounded-lg">
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
                    <h1 class="text-3xl font-bold text-gray-900">Kelola Tags</h1>
                    <p class="text-gray-600 mt-2">Buat dan kelola tags untuk artikel</p>
                </div>
                <div class="flex space-x-3">
                    <button onclick="toggleBulkActions()" id="bulkToggle" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">
                        Mode Bulk
                    </button>
                    <button onclick="showCreateForm()" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                        + Buat Tag Baru
                    </button>
                </div>
            </div>
        </div>

        <?php displayAlert(); ?>

        <!-- Form Modal -->
        <div id="tagForm" class="<?php echo $editTag ? 'block' : 'hidden'; ?> bg-white rounded-lg shadow-lg p-6 mb-8">
            <h2 id="formTitle" class="text-xl font-bold text-gray-900 mb-6">
                <?php echo $editTag ? 'Edit Tag' : 'Buat Tag Baru'; ?>
            </h2>
            
            <form method="POST" class="space-y-6">
                <input type="hidden" name="action" id="formAction" value="<?php echo $editTag ? 'update' : 'create'; ?>">
                <?php if ($editTag): ?>
                <input type="hidden" name="id" value="<?php echo $editTag['id']; ?>">
                <?php endif; ?>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Tag</label>
                        <input type="text" name="name" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               value="<?php echo htmlspecialchars($editTag['name'] ?? ''); ?>"
                               onchange="generateSlug(this.value)"
                               placeholder="contoh: Artificial Intelligence">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Slug URL</label>
                        <input type="text" name="slug" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               value="<?php echo htmlspecialchars($editTag['slug'] ?? ''); ?>"
                               placeholder="artificial-intelligence">
                        <p class="text-xs text-gray-500 mt-1">Kosongkan untuk auto-generate dari nama</p>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="hideForm()" class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                        Batal
                    </button>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <?php echo $editTag ? 'Update Tag' : 'Simpan Tag'; ?>
                    </button>
                </div>
            </form>
        </div>

        <!-- Bulk Actions Bar -->
        <div id="bulkActions" class="hidden bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-yellow-800">
                        <span id="selectedCount">0</span> tag dipilih
                    </span>
                    <button onclick="selectAll()" class="text-sm text-blue-600 hover:text-blue-800">Pilih Semua</button>
                    <button onclick="deselectAll()" class="text-sm text-blue-600 hover:text-blue-800">Batal Pilih</button>
                </div>
                <div class="flex space-x-2">
                    <button onclick="bulkDelete()" class="bg-red-600 text-white px-4 py-2 rounded text-sm hover:bg-red-700">
                        Hapus Terpilih
                    </button>
                </div>
            </div>
        </div>

        <!-- Tags Grid -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900">Daftar Tags (<?php echo count($tags); ?>)</h3>
                    <div class="flex items-center space-x-4">
                        <input type="text" id="searchTags" placeholder="Cari tags..." 
                               class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                               onkeyup="searchTags()">
                    </div>
                </div>
            </div>
            
            <?php if (!empty($tags)): ?>
            <div class="p-6">
                <div id="tagsContainer" class="flex flex-wrap gap-3">
                    <?php foreach ($tags as $tag): ?>
                    <div class="tag-item flex items-center bg-gray-100 hover:bg-gray-200 rounded-full px-4 py-2 transition-colors group" data-name="<?php echo strtolower($tag['name']); ?>">
                        <input type="checkbox" class="tag-checkbox hidden mr-2 bulk-checkbox" value="<?php echo $tag['id']; ?>" onchange="updateSelectedCount()">
                        
                        <div class="flex items-center space-x-2">
                            <span class="text-sm font-medium text-gray-700"><?php echo htmlspecialchars($tag['name']); ?></span>
                            
                            <?php if ($tag['article_count'] > 0): ?>
                            <span class="bg-blue-500 text-white text-xs px-2 py-1 rounded-full">
                                <?php echo $tag['article_count']; ?>
                            </span>
                            <?php endif; ?>
                            
                            <?php if ($tag['usage_count'] > 0): ?>
                            <span class="bg-green-500 text-white text-xs px-2 py-1 rounded-full" title="Total penggunaan">
                                <?php echo $tag['usage_count']; ?>x
                            </span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="ml-3 flex items-center space-x-1 opacity-0 group-hover:opacity-100 transition-opacity">
                            <a href="?edit=<?php echo $tag['id']; ?>" 
                               class="text-blue-600 hover:text-blue-800 p-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                </svg>
                            </a>
                            <button onclick="deleteTag(<?php echo $tag['id']; ?>, '<?php echo addslashes($tag['name']); ?>', <?php echo $tag['article_count']; ?>)" 
                                    class="text-red-600 hover:text-red-800 p-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div id="noTagsFound" class="hidden text-center py-8">
                    <p class="text-gray-500">Tidak ada tag yang ditemukan</p>
                </div>
            </div>
            <?php else: ?>
            <div class="p-6 text-center">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Belum Ada Tags</h3>
                <p class="text-gray-600 mb-6">Buat tag pertama untuk mengorganisir artikel Anda</p>
                <button onclick="showCreateForm()" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700">
                    Buat Tag Pertama
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

    <!-- Bulk Delete Form -->
    <form id="bulkDeleteForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="bulk_delete">
        <div id="bulkDeleteIds"></div>
    </form>

    <script>
        let bulkMode = false;

        function showCreateForm() {
            document.getElementById('tagForm').classList.remove('hidden');
            document.getElementById('formTitle').textContent = 'Buat Tag Baru';
            document.getElementById('formAction').value = 'create';
            document.querySelector('form').reset();
        }

        function hideForm() {
            document.getElementById('tagForm').classList.add('hidden');
            window.location.href = '<?php echo ADMIN_BASE_URL; ?>/tags.php';
        }

        function generateSlug(name) {
            const slug = name.toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/[\s-]+/g, '-')
                .trim()
                .replace(/^-+|-+$/g, '');
            document.querySelector('input[name="slug"]').value = slug;
        }

        function deleteTag(id, name, articleCount) {
            if (articleCount > 0) {
                alert(`Tidak dapat menghapus tag "${name}" karena masih digunakan dalam ${articleCount} artikel.`);
                return;
            }
            
            if (confirm(`Apakah Anda yakin ingin menghapus tag "${name}"?`)) {
                document.getElementById('deleteId').value = id;
                document.getElementById('deleteForm').submit();
            }
        }

        function toggleBulkActions() {
            bulkMode = !bulkMode;
            const bulkActions = document.getElementById('bulkActions');
            const bulkToggle = document.getElementById('bulkToggle');
            const checkboxes = document.querySelectorAll('.bulk-checkbox');
            
            if (bulkMode) {
                bulkActions.classList.remove('hidden');
                bulkToggle.textContent = 'Mode Normal';
                bulkToggle.classList.remove('bg-gray-600', 'hover:bg-gray-700');
                bulkToggle.classList.add('bg-red-600', 'hover:bg-red-700');
                checkboxes.forEach(checkbox => checkbox.classList.remove('hidden'));
            } else {
                bulkActions.classList.add('hidden');
                bulkToggle.textContent = 'Mode Bulk';
                bulkToggle.classList.remove('bg-red-600', 'hover:bg-red-700');
                bulkToggle.classList.add('bg-gray-600', 'hover:bg-gray-700');
                checkboxes.forEach(checkbox => {
                    checkbox.classList.add('hidden');
                    checkbox.checked = false;
                });
                updateSelectedCount();
            }
        }

        function selectAll() {
            const visibleCheckboxes = document.querySelectorAll('.tag-item:not([style*="display: none"]) .bulk-checkbox');
            visibleCheckboxes.forEach(checkbox => checkbox.checked = true);
            updateSelectedCount();
        }

        function deselectAll() {
            const checkboxes = document.querySelectorAll('.bulk-checkbox');
            checkboxes.forEach(checkbox => checkbox.checked = false);
            updateSelectedCount();
        }

        function updateSelectedCount() {
            const selected = document.querySelectorAll('.bulk-checkbox:checked').length;
            document.getElementById('selectedCount').textContent = selected;
        }

        function bulkDelete() {
            const selected = document.querySelectorAll('.bulk-checkbox:checked');
            if (selected.length === 0) {
                alert('Pilih tag yang ingin dihapus');
                return;
            }
            
            if (confirm(`Apakah Anda yakin ingin menghapus ${selected.length} tag yang dipilih?`)) {
                const bulkDeleteIds = document.getElementById('bulkDeleteIds');
                bulkDeleteIds.innerHTML = '';
                
                selected.forEach(checkbox => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'tag_ids[]';
                    input.value = checkbox.value;
                    bulkDeleteIds.appendChild(input);
                });
                
                document.getElementById('bulkDeleteForm').submit();
            }
        }

        function searchTags() {
            const searchTerm = document.getElementById('searchTags').value.toLowerCase();
            const tagItems = document.querySelectorAll('.tag-item');
            let visibleCount = 0;
            
            tagItems.forEach(item => {
                const tagName = item.getAttribute('data-name');
                if (tagName.includes(searchTerm)) {
                    item.style.display = 'flex';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            });
            
            const noTagsFound = document.getElementById('noTagsFound');
            if (visibleCount === 0 && searchTerm !== '') {
                noTagsFound.classList.remove('hidden');
            } else {
                noTagsFound.classList.add('hidden');
            }
        }

        // Show form if editing
        <?php if ($editTag): ?>
        document.getElementById('tagForm').classList.remove('hidden');
        <?php endif; ?>
    </script>
</body>
</html>