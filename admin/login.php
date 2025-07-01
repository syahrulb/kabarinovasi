<?php
// admin/login.php
require_once 'includes/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ' . ADMIN_BASE_URL . '/index.php');
    exit;
}

$error = '';

if ($_POST) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($username === ADMIN_USERNAME && password_verify($password, ADMIN_PASSWORD)) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        $_SESSION['login_time'] = time();
        header('Location: ' . ADMIN_BASE_URL . '/index.php');
        exit;
    } else {
        $error = 'Username atau password salah!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - kabarInovasi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', system-ui, sans-serif; }
        
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .glass-effect {
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
    </style>
</head>
<body class="gradient-bg min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                <svg class="w-8 h-8 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">kabarInovasi</h1>
            <p class="text-white/80">Admin Panel</p>
        </div>

        <!-- Login Form -->
        <div class="bg-white/10 glass-effect rounded-2xl p-8 backdrop-blur-lg border border-white/20">
            <h2 class="text-2xl font-bold text-white mb-6 text-center">Masuk ke Admin Panel</h2>
            
            <?php if ($error): ?>
            <div class="bg-red-500/20 border border-red-500/50 text-red-100 px-4 py-3 rounded-lg mb-6">
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <div>
                    <label for="username" class="block text-sm font-medium text-white/90 mb-2">Username</label>
                    <input type="text" 
                           id="username" 
                           name="username" 
                           required
                           class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-transparent"
                           placeholder="Masukkan username">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-white/90 mb-2">Password</label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           required
                           class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-transparent"
                           placeholder="Masukkan password">
                </div>

                <button type="submit" 
                        class="w-full bg-white text-blue-600 py-3 px-4 rounded-lg font-semibold hover:bg-white/90 transition-colors duration-200 shadow-lg">
                    Masuk
                </button>
            </form>

            <div class="mt-6 pt-6 border-t border-white/20">
                <p class="text-center text-white/60 text-sm">
                    Default: admin / admin123
                </p>
                <p class="text-center mt-2">
                    <a href="<?php echo FRONTEND_BASE_URL; ?>" class="text-white/80 hover:text-white text-sm">
                        ← Kembali ke Website
                    </a>
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-8">
            <p class="text-white/60 text-sm">
                © <?php echo date('Y'); ?> kabarInovasi. Semua hak cipta dilindungi.
            </p>
        </div>
    </div>

    <script>
        // Auto focus pada username input
        document.getElementById('username').focus();
        
        // Handle form submission dengan loading state
        document.querySelector('form').addEventListener('submit', function(e) {
            const button = this.querySelector('button[type="submit"]');
            button.disabled = true;
            button.innerHTML = 'Memproses...';
        });
    </script>
</body>
</html>