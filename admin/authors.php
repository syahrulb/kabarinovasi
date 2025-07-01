<?php
// admin/authors.php
require_once 'includes/config.php';
requireLogin();

// Handle form submissions
if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    try {
        if ($action === 'create' || $action === 'update') {
            $name = sanitizeInput($_POST['name']);
            $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
            $bio = sanitizeInput($_POST['bio']);
            $avatar = sanitizeInput($_POST['avatar']);
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            
            if (!$email) {
                throw new Exception('Email tidak valid!');
            }
            
            if ($action === 'create') {
                // Check if email already exists
                $existingAuthor = $adminDb->fetch("SELECT id FROM authors WHERE email = :email", ['email' => $email]);
                if ($existingAuthor) {
                    throw new Exception('Email sudah digunakan oleh penulis lain!');
                }
                
                $sql = "INSERT INTO authors (name, email, bio, avatar, is_active) VALUES (:name, :email, :bio, :avatar, :is_active)";
                $adminDb->query($sql, [
                    'name' => $name,
                    'email' => $email,
                    'bio' => $bio,
                    'avatar' => $avatar,
                    'is_active' => $is_active
                ]);
                showAlert('Penulis berhasil dibuat!');
            } else {
                $id = (int)$_POST['id'];
                
                // Check if email already exists (excluding current author)
                $existingAuthor = $adminDb->fetch("SELECT id FROM authors WHERE email = :email AND id != :id", ['email' => $email, 'id' => $id]);
                if ($existingAuthor) {
                    throw new Exception('Email sudah digunakan oleh penulis lain!');
                }
                
                $sql = "UPDATE authors SET name = :name, email = :email, bio = :bio, avatar = :avatar, is_active = :is_active WHERE id = :id";
                $adminDb->query($sql, [
                    'name' => $name,
                    'email' => $email,
                    'bio' => $bio,
                    'avatar' => $avatar,
                    'is_active' => $is_active,
                    'id' => $id
                ]);
                showAlert('Penulis berhasil diupdate!');
            }
        } elseif ($action === 'delete') {
            $id = (int)$_POST['id'];
            
            // Check if author has articles
            $articleCount = $adminDb->fetch("SELECT COUNT(*) as count FROM articles WHERE author_id = :id", ['id' => $id])['count'];
            if ($articleCount > 0) {
                showAlert("Tidak dapat menghapus penulis yang masih memiliki {$articleCount} artikel!", 'error');
            } else {
                $adminDb->query("DELETE FROM authors WHERE id = :id", ['id' => $id]);
                showAlert('Penulis berhasil dihapus!');
            }
        }
    } catch (Exception $e) {
        showAlert('Error: ' . $e->getMessage(), 'error');
    }
}

// Get edit data
$editAuthor = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $editAuthor = $adminDb->fetch("SELECT * FROM authors WHERE id = :id", ['id' => $editId]);
}

// Get all authors with article count
$authors = $adminDb->fetchAll("
    SELECT a.*, COUNT(ar.id) as article_count 
    FROM authors a 
    LEFT JOIN articles ar ON a.id = ar.author_id 
    GROUP BY a.id 
    ORDER BY a.name ASC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Penulis - Admin kabarInovasi</title>
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
                <a href="<?php echo ADMIN_BASE_URL; ?>/authors.php" class="flex items-center px-4 py-2 text-gray-700 bg-blue-50 rounded-lg">
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
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
                    <h1 class="text-3xl font-bold text-gray-900">Kelola Penulis</h1>
                    <p class="text-gray-600 mt-2">Buat dan kelola penulis artikel</p>
                </div>
                <button onclick="showCreateForm()" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                    + Tambah Penulis Baru
                </button>
            </div>
        </div>

        <?php displayAlert(); ?>

        <!-- Form Modal -->
        <div id="authorForm" class="<?php echo $editAuthor ? 'block' : 'hidden'; ?> bg-white rounded-lg shadow-lg p-6 mb-8">
            <h2 id="formTitle" class="text-xl font-bold text-gray-900 mb-6">
                <?php echo $editAuthor ? 'Edit Penulis' : 'Tambah Penulis Baru'; ?>
            </h2>
            
            <form method="POST" class="space-y-6">
                <input type="hidden" name="action" id="formAction" value="<?php echo $editAuthor ? 'update' : 'create'; ?>">
                <?php if ($editAuthor): ?>
                <input type="hidden" name="id" value="<?php echo $editAuthor['id']; ?>">
                <?php endif; ?>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap *</label>
                        <input type="text" name="name" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               value="<?php echo htmlspecialchars($editAuthor['name'] ?? ''); ?>"
                               placeholder="Dr. Sarah Wijaya">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                        <input type="email" name="email" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               value="<?php echo htmlspecialchars($editAuthor['email'] ?? ''); ?>"
                               placeholder="sarah.wijaya@kabarInovasi.com">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Avatar URL</label>
                    <input type="url" name="avatar"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                           value="<?php echo htmlspecialchars($editAuthor['avatar'] ?? ''); ?>"
                           placeholder="https://example.com/avatar.jpg"
                           onchange="previewAvatar(this.value)">
                    <p class="text-xs text-gray-500 mt-1">Kosongkan untuk menggunakan avatar default</p>
                    
                    <div id="avatarPreview" class="mt-3 hidden">
                        <p class="text-sm text-gray-600 mb-2">Preview Avatar:</p>
                        <img id="avatarImg" src="" alt="Avatar Preview" class="w-16 h-16 rounded-full object-cover">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Bio/Deskripsi</label>
                    <textarea name="bio" rows="4"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                              placeholder="Pakar teknologi kesehatan dengan pengalaman 15 tahun di industri medtech..."><?php echo htmlspecialchars($editAuthor['bio'] ?? ''); ?></textarea>
                </div>
                
                <div class="flex items-center">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" value="1"
                               <?php echo (!$editAuthor || $editAuthor['is_active']) ? 'checked' : ''; ?>
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-700">Penulis Aktif</span>
                    </label>
                    <p class="ml-4 text-xs text-gray-500">Nonaktifkan jika penulis tidak lagi menulis artikel</p>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="hideForm()" class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                        Batal
                    </button>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <?php echo $editAuthor ? 'Update Penulis' : 'Simpan Penulis'; ?>
                    </button>
                </div>
            </form>
        </div>

        <!-- Authors List -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Daftar Penulis (<?php echo count($authors); ?>)</h3>
            </div>
            
            <?php if (!empty($authors)): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Penulis</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bio</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Artikel</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bergabung</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($authors as $author): ?>
                        <tr>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <img src="<?php echo htmlspecialchars($author['avatar'] ?: 'https://ui-avatars.com/api/?name=' . urlencode($author['name']) . '&background=3b82f6&color=fff'); ?>" 
                                         alt="<?php echo htmlspecialchars($author['name']); ?>"
                                         class="w-10 h-10 rounded-full object-cover mr-3">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($author['name']); ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <?php echo htmlspecialchars($author['email']); ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 max-w-xs">
                                <div class="truncate">
                                    <?php echo htmlspecialchars(substr($author['bio'] ?: 'Belum ada bio', 0, 60)) . (strlen($author['bio'] ?: '') > 60 ? '...' : ''); ?>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $author['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                    <?php echo $author['is_active'] ? 'Aktif' : 'Nonaktif'; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs font-semibold">
                                    <?php echo $author['article_count']; ?> artikel
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <?php echo date('d/m/Y', strtotime($author['created_at'])); ?>
                            </td>
                            <td class="px-6 py-4 text-sm font-medium space-x-2">
                                <a href="?edit=<?php echo $author['id']; ?>" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                <button onclick="deleteAuthor(<?php echo $author['id']; ?>, '<?php echo addslashes($author['name']); ?>', <?php echo $author['article_count']; ?>)" 
                                        class="text-red-600 hover:text-red-900">Hapus</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="p-6 text-center">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Belum Ada Penulis</h3>
                <p class="text-gray-600 mb-6">Tambahkan penulis pertama untuk mulai membuat artikel</p>
                <button onclick="showCreateForm()" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700">
                    Tambah Penulis Pertama
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

    <script>
        function showCreateForm() {
            document.getElementById('authorForm').classList.remove('hidden');
            document.getElementById('formTitle').textContent = 'Tambah Penulis Baru';
            document.getElementById('formAction').value = 'create';
            document.querySelector('form').reset();
            document.querySelector('input[name="is_active"]').checked = true;
            hideAvatarPreview();
        }

        function hideForm() {
            document.getElementById('authorForm').classList.add('hidden');
            window.location.href = '<?php echo ADMIN_BASE_URL; ?>/authors.php';
        }

        function deleteAuthor(id, name, articleCount) {
            if (articleCount > 0) {
                alert(`Tidak dapat menghapus penulis "${name}" karena masih memiliki ${articleCount} artikel. Hapus atau ubah penulis artikel terlebih dahulu.`);
                return;
            }
            
            if (confirm(`Apakah Anda yakin ingin menghapus penulis "${name}"?`)) {
                document.getElementById('deleteId').value = id;
                document.getElementById('deleteForm').submit();
            }
        }

        function previewAvatar(url) {
            const preview = document.getElementById('avatarPreview');
            const img = document.getElementById('avatarImg');
            
            if (url && url.trim() !== '') {
                img.src = url;
                img.onload = function() {
                    preview.classList.remove('hidden');
                };
                img.onerror = function() {
                    hideAvatarPreview();
                };
            } else {
                hideAvatarPreview();
            }
        }

        function hideAvatarPreview() {
            document.getElementById('avatarPreview').classList.add('hidden');
        }

        // Show form if editing
        <?php if ($editAuthor): ?>
        document.getElementById('authorForm').classList.remove('hidden');
        // Show avatar preview if exists
        <?php if (!empty($editAuthor['avatar'])): ?>
        previewAvatar('<?php echo addslashes($editAuthor['avatar']); ?>');
        <?php endif; ?>
        <?php endif; ?>
    </script>
</body>
</html>