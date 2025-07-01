<?php
// admin/newsletter.php
require_once 'includes/config.php';
requireLogin();

// Handle form submissions
if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    try {
        if ($action === 'export') {
            // Export subscribers to CSV
            $subscribers = $adminDb->fetchAll("
                SELECT email, subscribed_at, 
                       CASE WHEN is_active = 1 THEN 'Active' ELSE 'Inactive' END as status
                FROM newsletter_subscribers 
                ORDER BY subscribed_at DESC
            ");
            
            $filename = 'newsletter_subscribers_' . date('Y-m-d_H-i-s') . '.csv';
            
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Email', 'Subscribed At', 'Status']);
            
            foreach ($subscribers as $subscriber) {
                fputcsv($output, [
                    $subscriber['email'],
                    date('Y-m-d H:i:s', strtotime($subscriber['subscribed_at'])),
                    $subscriber['status']
                ]);
            }
            
            fclose($output);
            exit;
            
        } elseif ($action === 'bulk_delete') {
            $subscriberIds = $_POST['subscriber_ids'] ?? [];
            if (!empty($subscriberIds)) {
                $placeholders = str_repeat('?,', count($subscriberIds) - 1) . '?';
                $adminDb->query("DELETE FROM newsletter_subscribers WHERE id IN ($placeholders)", $subscriberIds);
                showAlert(count($subscriberIds) . ' subscriber berhasil dihapus!');
            }
            
        } elseif ($action === 'bulk_activate') {
            $subscriberIds = $_POST['subscriber_ids'] ?? [];
            if (!empty($subscriberIds)) {
                $placeholders = str_repeat('?,', count($subscriberIds) - 1) . '?';
                $adminDb->query("UPDATE newsletter_subscribers SET is_active = 1, unsubscribed_at = NULL WHERE id IN ($placeholders)", $subscriberIds);
                showAlert(count($subscriberIds) . ' subscriber berhasil diaktifkan!');
            }
            
        } elseif ($action === 'bulk_deactivate') {
            $subscriberIds = $_POST['subscriber_ids'] ?? [];
            if (!empty($subscriberIds)) {
                $placeholders = str_repeat('?,', count($subscriberIds) - 1) . '?';
                $adminDb->query("UPDATE newsletter_subscribers SET is_active = 0, unsubscribed_at = NOW() WHERE id IN ($placeholders)", $subscriberIds);
                showAlert(count($subscriberIds) . ' subscriber berhasil dinonaktifkan!');
            }
            
        } elseif ($action === 'add_subscriber') {
            $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
            if (!$email) {
                throw new Exception('Email tidak valid!');
            }
            
            $existing = $adminDb->fetch("SELECT id FROM newsletter_subscribers WHERE email = :email", ['email' => $email]);
            if ($existing) {
                throw new Exception('Email sudah terdaftar dalam newsletter!');
            }
            
            $adminDb->query("INSERT INTO newsletter_subscribers (email, is_active) VALUES (:email, 1)", ['email' => $email]);
            showAlert('Subscriber berhasil ditambahkan!');
        }
    } catch (Exception $e) {
        showAlert('Error: ' . $e->getMessage(), 'error');
    }
}

// Get statistics
$stats = [
    'total_subscribers' => $adminDb->fetch("SELECT COUNT(*) as count FROM newsletter_subscribers")['count'],
    'active_subscribers' => $adminDb->fetch("SELECT COUNT(*) as count FROM newsletter_subscribers WHERE is_active = 1")['count'],
    'inactive_subscribers' => $adminDb->fetch("SELECT COUNT(*) as count FROM newsletter_subscribers WHERE is_active = 0")['count'],
    'today_subscribers' => $adminDb->fetch("SELECT COUNT(*) as count FROM newsletter_subscribers WHERE DATE(subscribed_at) = CURDATE()")['count'],
    'week_subscribers' => $adminDb->fetch("SELECT COUNT(*) as count FROM newsletter_subscribers WHERE subscribed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")['count'],
    'month_subscribers' => $adminDb->fetch("SELECT COUNT(*) as count FROM newsletter_subscribers WHERE subscribed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")['count'],
];

// Get filters
$filter = $_GET['filter'] ?? 'all';
$search = $_GET['search'] ?? '';

// Apply filters
$whereClause = [];
$params = [];

if ($filter === 'active') {
    $whereClause[] = 'is_active = 1';
} elseif ($filter === 'inactive') {
    $whereClause[] = 'is_active = 0';
}

if (!empty($search)) {
    $whereClause[] = 'email LIKE :search';
    $params['search'] = "%{$search}%";
}

$whereSQL = !empty($whereClause) ? 'WHERE ' . implode(' AND ', $whereClause) : '';

// Get subscribers
$subscribers = $adminDb->fetchAll("
    SELECT * FROM newsletter_subscribers 
    {$whereSQL}
    ORDER BY subscribed_at DESC 
    LIMIT 100
", $params);

// Get recent activity (last 30 days)
$recentActivity = $adminDb->fetchAll("
    SELECT DATE(subscribed_at) as date, COUNT(*) as count
    FROM newsletter_subscribers 
    WHERE subscribed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY DATE(subscribed_at)
    ORDER BY date DESC
    LIMIT 10
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Newsletter - Admin kabarInovasi</title>
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
                <a href="<?php echo ADMIN_BASE_URL; ?>/newsletter.php" class="flex items-center px-4 py-2 text-gray-700 bg-blue-50 rounded-lg">
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
                    <h1 class="text-3xl font-bold text-gray-900">Kelola Newsletter</h1>
                    <p class="text-gray-600 mt-2">Manajemen subscriber dan statistik newsletter</p>
                </div>
                <div class="flex space-x-3">
                    <form method="POST" class="inline">
                        <input type="hidden" name="action" value="export">
                        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                            Export CSV
                        </button>
                    </form>
                    <button onclick="showAddForm()" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                        + Tambah Subscriber
                    </button>
                </div>
            </div>
        </div>

        <?php displayAlert(); ?>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Subscriber</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($stats['total_subscribers']); ?></p>
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
                        <p class="text-sm font-medium text-gray-600">Aktif</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($stats['active_subscribers']); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-red-100 rounded-lg">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Nonaktif</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($stats['inactive_subscribers']); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-purple-100 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Bulan Ini</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($stats['month_subscribers']); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Subscriber Form -->
        <div id="addSubscriberForm" class="hidden bg-white rounded-lg shadow-lg p-6 mb-8">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Tambah Subscriber Baru</h2>
            
            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="add_subscriber">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email Subscriber</label>
                    <input type="email" name="email" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="subscriber@example.com">
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="hideAddForm()" class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                        Batal
                    </button>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Tambah Subscriber
                    </button>
                </div>
            </form>
        </div>

        <!-- Recent Activity -->
        <?php if (!empty($recentActivity)): ?>
        <div class="bg-white rounded-lg shadow mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Aktivitas Terbaru (30 Hari)</h3>
            </div>
            <div class="p-6">
                <div class="space-y-3">
                    <?php foreach ($recentActivity as $activity): ?>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600"><?php echo date('d F Y', strtotime($activity['date'])); ?></span>
                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs font-semibold">
                            +<?php echo $activity['count']; ?> subscriber
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
                <div class="flex items-center space-x-4">
                    <a href="?" class="px-4 py-2 rounded-full text-sm font-medium <?php echo $filter === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                        Semua (<?php echo $stats['total_subscribers']; ?>)
                    </a>
                    <a href="?filter=active" class="px-4 py-2 rounded-full text-sm font-medium <?php echo $filter === 'active' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                        Aktif (<?php echo $stats['active_subscribers']; ?>)
                    </a>
                    <a href="?filter=inactive" class="px-4 py-2 rounded-full text-sm font-medium <?php echo $filter === 'inactive' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                        Nonaktif (<?php echo $stats['inactive_subscribers']; ?>)
                    </a>
                </div>
                
                <form method="GET" class="flex items-center space-x-2">
                    <input type="hidden" name="filter" value="<?php echo htmlspecialchars($filter); ?>">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                           placeholder="Cari email..." 
                           class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-sm">
                        Cari
                    </button>
                </form>
            </div>
        </div>

        <!-- Bulk Actions -->
        <div id="bulkActions" class="hidden bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-yellow-800">
                        <span id="selectedCount">0</span> subscriber dipilih
                    </span>
                    <button onclick="selectAll()" class="text-sm text-blue-600 hover:text-blue-800">Pilih Semua</button>
                    <button onclick="deselectAll()" class="text-sm text-blue-600 hover:text-blue-800">Batal Pilih</button>
                </div>
                <div class="flex space-x-2">
                    <button onclick="bulkAction('activate')" class="bg-green-600 text-white px-4 py-2 rounded text-sm hover:bg-green-700">
                        Aktifkan
                    </button>
                    <button onclick="bulkAction('deactivate')" class="bg-yellow-600 text-white px-4 py-2 rounded text-sm hover:bg-yellow-700">
                        Nonaktifkan
                    </button>
                    <button onclick="bulkAction('delete')" class="bg-red-600 text-white px-4 py-2 rounded text-sm hover:bg-red-700">
                        Hapus
                    </button>
                </div>
            </div>
        </div>

        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Daftar Subscriber</h3>
            <button onclick="toggleBulkMode()" id="bulkToggle" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 text-sm">
                Mode Bulk
            </button>
        </div>

        <!-- Subscribers List -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <?php if (!empty($subscribers)): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="bulk-header hidden px-6 py-3 text-left">
                                <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll()" class="rounded">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bergabung</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Terakhir Update</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($subscribers as $subscriber): ?>
                        <tr>
                            <td class="bulk-cell hidden px-6 py-4">
                                <input type="checkbox" class="subscriber-checkbox rounded" value="<?php echo $subscriber['id']; ?>" onchange="updateSelectedCount()">
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <?php echo htmlspecialchars($subscriber['email']); ?>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $subscriber['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                    <?php echo $subscriber['is_active'] ? 'Aktif' : 'Nonaktif'; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <?php echo date('d/m/Y H:i', strtotime($subscriber['subscribed_at'])); ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <?php if ($subscriber['unsubscribed_at']): ?>
                                    <?php echo date('d/m/Y H:i', strtotime($subscriber['unsubscribed_at'])); ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="p-6 text-center">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">
                    <?php if (!empty($search)): ?>
                        Tidak ada subscriber yang ditemukan
                    <?php else: ?>
                        Belum Ada Subscriber
                    <?php endif; ?>
                </h3>
                <p class="text-gray-600 mb-6">
                    <?php if (!empty($search)): ?>
                        Coba gunakan kata kunci yang berbeda
                    <?php else: ?>
                        Mulai kumpulkan subscriber untuk newsletter Anda
                    <?php endif; ?>
                </p>
                <?php if (empty($search)): ?>
                <button onclick="showAddForm()" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700">
                    Tambah Subscriber Pertama
                </button>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bulk Action Forms -->
    <form id="bulkForm" method="POST" style="display: none;">
        <input type="hidden" name="action" id="bulkAction">
        <div id="bulkIds"></div>
    </form>

    <script>
        let bulkMode = false;

        function showAddForm() {
            document.getElementById('addSubscriberForm').classList.remove('hidden');
        }

        function hideAddForm() {
            document.getElementById('addSubscriberForm').classList.add('hidden');
        }

        function toggleBulkMode() {
            bulkMode = !bulkMode;
            const bulkActions = document.getElementById('bulkActions');
            const bulkToggle = document.getElementById('bulkToggle');
            const bulkHeaders = document.querySelectorAll('.bulk-header');
            const bulkCells = document.querySelectorAll('.bulk-cell');
            
            if (bulkMode) {
                bulkActions.classList.remove('hidden');
                bulkToggle.textContent = 'Mode Normal';
                bulkToggle.classList.remove('bg-gray-600', 'hover:bg-gray-700');
                bulkToggle.classList.add('bg-red-600', 'hover:bg-red-700');
                bulkHeaders.forEach(header => header.classList.remove('hidden'));
                bulkCells.forEach(cell => cell.classList.remove('hidden'));
            } else {
                bulkActions.classList.add('hidden');
                bulkToggle.textContent = 'Mode Bulk';
                bulkToggle.classList.remove('bg-red-600', 'hover:bg-red-700');
                bulkToggle.classList.add('bg-gray-600', 'hover:bg-gray-700');
                bulkHeaders.forEach(header => header.classList.add('hidden'));
                bulkCells.forEach(cell => cell.classList.add('hidden'));
                deselectAll();
            }
        }

        function selectAll() {
            const checkboxes = document.querySelectorAll('.subscriber-checkbox');
            checkboxes.forEach(checkbox => checkbox.checked = true);
            document.getElementById('selectAllCheckbox').checked = true;
            updateSelectedCount();
        }

        function deselectAll() {
            const checkboxes = document.querySelectorAll('.subscriber-checkbox, #selectAllCheckbox');
            checkboxes.forEach(checkbox => checkbox.checked = false);
            updateSelectedCount();
        }

        function toggleSelectAll() {
            const selectAll = document.getElementById('selectAllCheckbox').checked;
            const checkboxes = document.querySelectorAll('.subscriber-checkbox');
            checkboxes.forEach(checkbox => checkbox.checked = selectAll);
            updateSelectedCount();
        }

        function updateSelectedCount() {
            const selected = document.querySelectorAll('.subscriber-checkbox:checked').length;
            document.getElementById('selectedCount').textContent = selected;
        }

        function bulkAction(action) {
            const selected = document.querySelectorAll('.subscriber-checkbox:checked');
            if (selected.length === 0) {
                alert('Pilih subscriber yang ingin diproses');
                return;
            }
            
            let actionText = '';
            let actionValue = '';
            
            switch(action) {
                case 'activate':
                    actionText = 'aktifkan';
                    actionValue = 'bulk_activate';
                    break;
                case 'deactivate':
                    actionText = 'nonaktifkan';
                    actionValue = 'bulk_deactivate';
                    break;
                case 'delete':
                    actionText = 'hapus';
                    actionValue = 'bulk_delete';
                    break;
            }
            
            if (confirm(`Apakah Anda yakin ingin ${actionText} ${selected.length} subscriber yang dipilih?`)) {
                const bulkIds = document.getElementById('bulkIds');
                bulkIds.innerHTML = '';
                
                selected.forEach(checkbox => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'subscriber_ids[]';
                    input.value = checkbox.value;
                    bulkIds.appendChild(input);
                });
                
                document.getElementById('bulkAction').value = actionValue;
                document.getElementById('bulkForm').submit();
            }
        }
    </script>
</body>
</html>