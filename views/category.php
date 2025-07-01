<!-- Category Page -->
<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- Breadcrumb -->
    <nav class="mb-8">
        <ol class="flex items-center space-x-2 text-sm text-gray-500">
            <li><a href="<?php echo $base_url; ?>" class="hover:text-blue-600">Beranda</a></li>
            <li><span class="mx-2">/</span></li>
            <li class="text-gray-900 font-medium"><?php echo htmlspecialchars($category['name']); ?></li>
        </ol>
    </nav>

    <!-- Category Header -->
    <header class="text-center mb-12">
        <div class="inline-block p-4 rounded-full mb-6" style="background-color: <?php echo htmlspecialchars($category['color']); ?>20;">
            <div class="w-12 h-12 rounded-full flex items-center justify-center" style="background-color: <?php echo htmlspecialchars($category['color']); ?>;">
                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        
        <h1 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-4">
            <?php echo htmlspecialchars($category['name']); ?>
        </h1>
        
        <p class="text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
            <?php echo htmlspecialchars($category['description']); ?>
        </p>
        
        <div class="mt-6 flex items-center justify-center space-x-6 text-sm text-gray-500">
            <span><?php echo count($articles); ?> artikel tersedia</span>
            <span>•</span>
            <span>Diperbarui secara berkala</span>
        </div>
    </header>

    <div class="grid lg:grid-cols-3 gap-12">
        
        <!-- Articles Grid -->
        <div class="lg:col-span-2">
            <?php if (!empty($articles)): ?>
            <div class="grid gap-8">
                <?php foreach($articles as $index => $article): ?>
                <article class="bg-white rounded-2xl shadow-lg hover-lift flex flex-col md:flex-row overflow-hidden">
                    <div class="md:w-80 relative">
                        <img src="<?php echo htmlspecialchars($article['image']); ?>" 
                             alt="<?php echo htmlspecialchars($article['title']); ?>" 
                             class="w-full h-48 md:h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent"></div>
                    </div>
                    <div class="flex-1 p-6 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <span class="inline-block px-3 py-1 rounded-full text-sm font-medium text-white" style="background-color: <?php echo htmlspecialchars($category['color']); ?>;">
                                    <?php echo htmlspecialchars($article['category']); ?>
                                </span>
                                <span class="text-sm text-gray-500"><?php echo htmlspecialchars($article['date']); ?></span>
                            </div>
                            
                            <h3 class="text-xl md:text-2xl font-bold text-gray-900 mb-3 line-clamp-2 hover:text-blue-600 transition-colors">
                                <a href="<?php echo $base_url; ?>/article/<?php echo htmlspecialchars($article['slug']); ?>">
                                    <?php echo htmlspecialchars($article['title']); ?>
                                </a>
                            </h3>
                            
                            <p class="text-gray-600 mb-4 line-clamp-3">
                                <?php echo htmlspecialchars($article['excerpt']); ?>
                            </p>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <img src="<?php echo htmlspecialchars($article['author_avatar']); ?>" 
                                     alt="<?php echo htmlspecialchars($article['author']); ?>"
                                     class="w-10 h-10 rounded-full object-cover">
                                <div>
                                    <p class="font-medium text-gray-900"><?php echo htmlspecialchars($article['author']); ?></p>
                                    <p class="text-sm text-gray-500">
                                        <?php echo htmlspecialchars($article['read_time']); ?> menit • 
                                        <?php echo number_format($article['views_count']); ?> views
                                    </p>
                                </div>
                            </div>
                            
                            <a href="<?php echo $base_url; ?>/article/<?php echo htmlspecialchars($article['slug']); ?>" 
                               class="inline-flex items-center text-blue-600 hover:text-blue-700 font-medium">
                                Baca Selengkapnya
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>

            <!-- Load More Button -->
            <div class="text-center mt-12">
                <button onclick="loadMoreCategoryArticles('<?php echo htmlspecialchars($category['slug']); ?>')" 
                        class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-8 py-3 rounded-full font-medium transition-colors duration-200">
                    Muat Lebih Banyak
                </button>
            </div>
            
            <?php else: ?>
            <div class="text-center py-16">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 0112 15c-2.34 0-4.29-1.2-5.454-3.077C5.78 10.77 5.78 9.23 6.546 8.077A7.962 7.962 0 0112 5c2.34 0 4.29 1.2 5.454 3.077C18.22 9.23 18.22 10.77 17.454 11.923"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Belum Ada Artikel</h3>
                <p class="text-gray-600 mb-6">Artikel untuk kategori ini akan segera hadir. Pantau terus untuk update terbaru!</p>
                <a href="<?php echo $base_url; ?>" 
                   class="inline-flex items-center text-blue-600 hover:text-blue-700 font-medium">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Kembali ke Beranda
                </a>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <aside class="lg:col-span-1">
            <!-- Category Info -->
            <div class="bg-white rounded-2xl shadow-lg p-6 mb-8">
                <h3 class="text-xl font-bold text-gray-900 mb-4">Tentang Kategori</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">Total Artikel</span>
                        <span class="font-bold text-2xl" style="color: <?php echo htmlspecialchars($category['color']); ?>;">
                            <?php echo count($articles); ?>
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">Kategori</span>
                        <span class="inline-block px-3 py-1 rounded-full text-sm font-medium text-white" style="background-color: <?php echo htmlspecialchars($category['color']); ?>;">
                            <?php echo htmlspecialchars($category['name']); ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Other Categories -->
            <div class="bg-white rounded-2xl shadow-lg p-6 mb-8">
                <h3 class="text-xl font-bold text-gray-900 mb-6">Kategori Lainnya</h3>
                <div class="space-y-3">
                    <?php foreach($categories as $otherCategory): ?>
                        <?php if ($otherCategory['slug'] !== $category['slug']): ?>
                        <a href="<?php echo $base_url; ?>/category/<?php echo htmlspecialchars($otherCategory['slug']); ?>" 
                           class="block p-3 bg-gray-50 hover:bg-blue-50 rounded-lg transition-colors duration-200 group">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-700 group-hover:text-blue-600 font-medium">
                                    <?php echo htmlspecialchars($otherCategory['name']); ?>
                                </span>
                                <div class="w-3 h-3 rounded-full" style="background-color: <?php echo htmlspecialchars($otherCategory['color']); ?>;"></div>
                            </div>
                        </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Trending Topics -->
            <?php if (!empty($trending_topics)): ?>
            <div class="bg-white rounded-2xl shadow-lg p-6 mb-8">
                <h3 class="text-xl font-bold text-gray-900 mb-6">Topik Trending</h3>
                <div class="space-y-3">
                    <?php foreach($trending_topics as $topic): ?>
                    <a href="<?php echo $base_url; ?>/tag/<?php echo htmlspecialchars($topic['slug'] ?? strtolower(str_replace(' ', '-', $topic['name']))); ?>" 
                       class="block p-3 bg-gray-50 hover:bg-blue-50 rounded-lg transition-colors duration-200 group">
                        <span class="text-gray-700 group-hover:text-blue-600 font-medium">
                            #<?php echo htmlspecialchars($topic['name']); ?>
                        </span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Newsletter Signup -->
            <div class="bg-gradient-to-br from-blue-600 to-purple-600 rounded-2xl p-6 text-white">
                <h3 class="text-xl font-bold mb-3">Dapatkan Update Terbaru</h3>
                <p class="text-blue-100 mb-6">Berlangganan newsletter untuk update artikel <?php echo htmlspecialchars($category['name']); ?></p>
                <form method="POST" action="<?php echo $base_url; ?>" class="space-y-3">
                    <input type="hidden" name="action" value="subscribe">
                    <input type="email" name="email" placeholder="Email Anda" required
                           class="w-full px-4 py-3 rounded-lg text-gray-900 border-0 focus:ring-2 focus:ring-white/50">
                    <button type="submit" 
                            class="w-full bg-white text-blue-600 py-3 rounded-lg font-bold hover:bg-gray-100 transition-colors duration-200">
                        Berlangganan
                    </button>
                </form>
            </div>
        </aside>
    </div>
</main>

<script>
function loadMoreCategoryArticles(categorySlug) {
    // This would be implemented with AJAX to load more articles for the category
    console.log('Loading more articles for category:', categorySlug);
    alert('Fitur "Muat Lebih Banyak" akan diimplementasikan dengan AJAX untuk memuat artikel tambahan dari kategori ini.');
}
</script>