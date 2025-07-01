<!-- 404 Error Page -->
<main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="text-center">
        <!-- 404 Illustration -->
        <div class="mb-12">
            <div class="relative inline-block">
                <div class="text-8xl md:text-9xl font-bold text-gray-200 select-none">404</div>
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="w-24 h-24 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center animate-pulse">
                        <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 0112 15c-2.34 0-4.29-1.2-5.454-3.077C5.78 10.77 5.78 9.23 6.546 8.077A7.962 7.962 0 0112 5c2.34 0 4.29 1.2 5.454 3.077C18.22 9.23 18.22 10.77 17.454 11.923"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Error Message -->
        <div class="mb-12">
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                Halaman Tidak Ditemukan
            </h1>
            <p class="text-xl text-gray-600 mb-6 max-w-2xl mx-auto">
                Maaf, halaman yang Anda cari tidak dapat ditemukan. Mungkin halaman telah dipindahkan, dihapus, atau Anda salah mengetik alamat URL.
            </p>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row justify-center items-center space-y-4 sm:space-y-0 sm:space-x-4 mb-16">
            <a href="<?php echo $base_url; ?>" 
               class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-full font-medium transition-colors duration-200 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Kembali ke Beranda
            </a>
            
            <button onclick="history.back()" 
                    class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-8 py-3 rounded-full font-medium transition-colors duration-200 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Halaman Sebelumnya
            </button>
        </div>

        <!-- Search Section -->
        <div class="bg-gray-50 rounded-2xl p-8 mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">
                Coba Cari Artikel
            </h2>
            <p class="text-gray-600 mb-6">
                Gunakan pencarian untuk menemukan artikel yang Anda cari
            </p>
            <form method="GET" action="<?php echo $base_url; ?>" class="max-w-md mx-auto">
                <div class="relative">
                    <input type="text" name="search" placeholder="Masukkan kata kunci..." 
                           class="w-full px-4 py-3 pr-12 bg-white rounded-full border-2 border-gray-200 focus:border-blue-500 focus:outline-none transition-colors">
                    <button type="submit" class="absolute right-2 top-2 p-2 bg-blue-600 hover:bg-blue-700 text-white rounded-full transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </button>
                </div>
            </form>
        </div>

        <!-- Popular Categories -->
        <?php if (!empty($categories)): ?>
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-8">
                Jelajahi Kategori Populer
            </h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <?php foreach (array_slice($categories, 0, 8) as $category): ?>
                <a href="<?php echo $base_url; ?>/category/<?php echo htmlspecialchars($category['slug']); ?>" 
                   class="bg-white hover:shadow-lg rounded-xl p-4 transition-all duration-200 group border-2 border-gray-100 hover:border-blue-200">
                    <div class="w-12 h-12 rounded-lg mx-auto mb-3 flex items-center justify-center" style="background-color: <?php echo htmlspecialchars($category['color']); ?>20;">
                        <div class="w-6 h-6 rounded-full" style="background-color: <?php echo htmlspecialchars($category['color']); ?>;"></div>
                    </div>
                    <h3 class="font-semibold text-gray-900 group-hover:text-blue-600 transition-colors text-sm">
                        <?php echo htmlspecialchars($category['name']); ?>
                    </h3>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Helpful Links -->
        <div class="border-t border-gray-200 pt-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                Link Berguna
            </h3>
            <div class="flex flex-wrap justify-center gap-6 text-sm">
                <a href="<?php echo $base_url; ?>" class="text-blue-600 hover:text-blue-700 transition-colors">Beranda</a>
                <a href="#" class="text-blue-600 hover:text-blue-700 transition-colors">Tentang Kami</a>
                <a href="#" class="text-blue-600 hover:text-blue-700 transition-colors">Kontak</a>
                <a href="#" class="text-blue-600 hover:text-blue-700 transition-colors">FAQ</a>
                <a href="#" class="text-blue-600 hover:text-blue-700 transition-colors">Bantuan</a>
            </div>
        </div>
    </div>
</main>