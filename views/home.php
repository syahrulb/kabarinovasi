<?php if (!empty($searchQuery)): ?>
<!-- Search Results Section -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-blue-50 rounded-lg p-6 mb-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-2">
            Hasil pencarian untuk: "<span class="text-blue-600"><?php echo htmlspecialchars($searchQuery); ?></span>"
        </h2>
        <p class="text-gray-600">
            Ditemukan <span class="font-semibold text-blue-600"><?php echo count($searchResults); ?></span> artikel
        </p>
    </div>

    <?php if (!empty($searchResults)): ?>
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php foreach ($searchResults as $result): ?>
        <article class="bg-white rounded-2xl shadow-lg hover-lift">
            <div class="relative">
                <img src="<?php echo htmlspecialchars($result['featured_image']); ?>" 
                     alt="<?php echo htmlspecialchars($result['title']); ?>" 
                     class="w-full h-48 object-cover rounded-t-2xl"
                     onerror="this.src='https://images.unsplash.com/photo-1518770660439-4636190af475?w=400&h=250&fit=crop'">
                <span class="absolute top-4 left-4 bg-white/90 text-gray-800 px-3 py-1 rounded-full text-sm font-medium">
                    <?php echo htmlspecialchars($result['category_name'] ?? 'No Category'); ?>
                </span>
                
                <!-- Relevance indicator if using FULLTEXT search -->
                <?php if (isset($result['relevance']) && $result['relevance'] > 0): ?>
                <span class="absolute top-4 right-4 bg-green-500/90 text-white px-2 py-1 rounded-full text-xs font-medium">
                    Relevan
                </span>
                <?php endif; ?>
            </div>
            <div class="p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-3 line-clamp-2 hover:text-blue-600 transition-colors">
                    <a href="<?php echo $base_url; ?>/article/<?php echo htmlspecialchars($result['slug']); ?>">
                        <?php echo htmlspecialchars($result['title']); ?>
                    </a>
                </h3>
                <p class="text-gray-600 mb-4 line-clamp-3">
                    <?php echo htmlspecialchars($result['excerpt']); ?>
                </p>
                
                <!-- Tags if available -->
                <?php if (!empty($result['tags'])): ?>
                <div class="mb-4">
                    <div class="flex flex-wrap gap-1">
                        <?php foreach (array_slice(explode(', ', $result['tags']), 0, 3) as $tag): ?>
                        <span class="bg-gray-100 text-gray-600 px-2 py-1 rounded-full text-xs">
                            #<?php echo htmlspecialchars(trim($tag)); ?>
                        </span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 bg-gradient-to-r from-blue-400 to-purple-500 rounded-full flex items-center justify-center">
                            <span class="text-xs font-bold text-white">
                                <?php echo strtoupper(substr($result['author_name'] ?? 'A', 0, 1)); ?>
                            </span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($result['author_name'] ?? 'Unknown'); ?></p>
                            <p class="text-xs text-gray-500">
                                <?php echo date('d M Y', strtotime($result['published_at'] ?? $result['created_at'])); ?>
                                <?php if (isset($result['views_count'])): ?>
                                • <?php echo number_format($result['views_count']); ?> views
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                    <a href="<?php echo $base_url; ?>/article/<?php echo htmlspecialchars($result['slug']); ?>" 
                       class="text-blue-600 hover:text-blue-700 font-medium text-sm flex items-center">
                        Baca
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>
        </article>
        <?php endforeach; ?>
    </div>

    <!-- Search suggestions if many results -->
    <?php if (count($searchResults) > 6): ?>
    <div class="mt-8 text-center">
        <p class="text-gray-600 mb-4">Terlalu banyak hasil? Coba pencarian yang lebih spesifik:</p>
        <div class="flex flex-wrap justify-center gap-2">
            <?php 
            // Extract common words from search results for suggestions
            $suggestions = [];
            foreach (array_slice($searchResults, 0, 5) as $result) {
                if (!empty($result['category_name'])) {
                    $suggestions[] = $result['category_name'];
                }
                if (!empty($result['tags'])) {
                    $tags = explode(', ', $result['tags']);
                    $suggestions = array_merge($suggestions, array_slice($tags, 0, 2));
                }
            }
            $suggestions = array_unique($suggestions);
            ?>
            <?php foreach (array_slice($suggestions, 0, 5) as $suggestion): ?>
            <a href="<?php echo $base_url; ?>?search=<?php echo urlencode(trim($suggestion)); ?>" 
               class="bg-blue-100 hover:bg-blue-200 text-blue-700 px-3 py-1 rounded-full text-sm transition-colors">
                <?php echo htmlspecialchars($suggestion); ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <!-- No results found -->
    <div class="text-center py-12">
        <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>
        <h3 class="text-xl font-semibold text-gray-900 mb-2">Tidak ada artikel ditemukan</h3>
        <p class="text-gray-600 mb-6">Coba gunakan kata kunci yang berbeda atau lebih umum</p>
        
        <!-- Search suggestions -->
        <div class="max-w-md mx-auto mb-8">
            <h4 class="text-sm font-semibold text-gray-700 mb-3">Saran pencarian:</h4>
            <div class="flex flex-wrap justify-center gap-2">
                <a href="<?php echo $base_url; ?>?search=AI" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1 rounded-full text-sm transition-colors">AI</a>
                <a href="<?php echo $base_url; ?>?search=startup" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1 rounded-full text-sm transition-colors">Startup</a>
                <a href="<?php echo $base_url; ?>?search=teknologi" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1 rounded-full text-sm transition-colors">Teknologi</a>
                <a href="<?php echo $base_url; ?>?search=inovasi" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1 rounded-full text-sm transition-colors">Inovasi</a>
                <a href="<?php echo $base_url; ?>?search=blockchain" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1 rounded-full text-sm transition-colors">Blockchain</a>
            </div>
        </div>
        
        <!-- Browse categories -->
        <div class="border-t border-gray-200 pt-8">
            <h4 class="text-lg font-semibold text-gray-900 mb-4">Atau jelajahi berdasarkan kategori:</h4>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 max-w-2xl mx-auto">
                <?php foreach (array_slice($categories, 0, 4) as $category): ?>
                <a href="<?php echo $base_url; ?>/category/<?php echo htmlspecialchars($category['slug']); ?>" 
                   class="bg-white hover:shadow-lg rounded-xl p-4 transition-all duration-200 group border-2 border-gray-100 hover:border-blue-200">
                    <div class="w-12 h-12 rounded-lg mx-auto mb-3 flex items-center justify-center" style="background-color: <?php echo htmlspecialchars($category['color']); ?>20;">
                        <div class="w-6 h-6 rounded-full" style="background-color: <?php echo htmlspecialchars($category['color']); ?>;"></div>
                    </div>
                    <h3 class="font-semibold text-gray-900 group-hover:text-blue-600 transition-colors text-sm text-center">
                        <?php echo htmlspecialchars($category['name']); ?>
                    </h3>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Back to home -->
        <div class="mt-8">
            <a href="<?php echo $base_url; ?>" 
               class="inline-flex items-center text-blue-600 hover:text-blue-700 font-medium">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Kembali ke Beranda
            </a>
        </div>
    </div>
    <?php endif; ?>
</section>

<?php elseif ($featured_article): ?>
<!-- Hero Section (only show if not searching) -->
<section class="relative bg-gradient-to-br from-blue-900 via-purple-900 to-blue-800 text-white overflow-hidden">
    <div class="absolute inset-0 bg-black/20"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div class="animate-slide-up">
                <span class="inline-block bg-blue-500/20 text-blue-200 px-4 py-2 rounded-full text-sm font-medium mb-4">
                    <?php echo htmlspecialchars($featured_article['category_name'] ?? $featured_article['category']); ?>
                </span>
                <h1 class="text-4xl lg:text-5xl font-bold leading-tight mb-6">
                    <?php echo htmlspecialchars($featured_article['title']); ?>
                </h1>
                <p class="text-xl text-gray-200 mb-8 leading-relaxed">
                    <?php echo htmlspecialchars($featured_article['excerpt']); ?>
                </p>
                <div class="flex items-center space-x-6 mb-8">
                    <div class="flex items-center space-x-2">
                        <div class="w-10 h-10 bg-gradient-to-r from-blue-400 to-purple-500 rounded-full flex items-center justify-center">
                            <span class="text-sm font-bold">
                                <?php echo strtoupper(substr($featured_article['author_name'] ?? $featured_article['author'], 0, 2)); ?>
                            </span>
                        </div>
                        <div>
                            <p class="font-medium"><?php echo htmlspecialchars($featured_article['author_name'] ?? $featured_article['author']); ?></p>
                            <p class="text-gray-300 text-sm">
                                <?php echo htmlspecialchars($featured_article['date']); ?> • 
                                <?php echo htmlspecialchars($featured_article['read_time']); ?> menit
                            </p>
                        </div>
                    </div>
                </div>
                <a href="<?php echo $base_url; ?>/article/<?php echo htmlspecialchars($featured_article['slug']); ?>" 
                   class="btn-primary text-white px-8 py-4 rounded-full font-bold shadow-lg inline-block">
                    Baca Selengkapnya
                </a>
            </div>
            <div class="relative animate-fade-in">
                <div class="relative rounded-2xl overflow-hidden shadow-2xl">
                    <img src="<?php echo htmlspecialchars($featured_article['featured_image'] ?? $featured_article['image']); ?>" 
                         alt="Featured Article" 
                         class="w-full h-96 object-cover"
                         onerror="this.src='https://images.unsplash.com/photo-1518770660439-4636190af475?w=600&h=400&fit=crop'">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
                </div>
                <div class="absolute -top-4 -right-4 w-20 h-20 bg-blue-500/20 rounded-full animate-pulse-slow"></div>
                <div class="absolute -bottom-6 -left-6 w-16 h-16 bg-purple-500/20 rounded-full animate-pulse-slow" style="animation-delay: 1s;"></div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Main Content -->
<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="grid lg:grid-cols-3 gap-12">
        
        <!-- Articles Grid -->
        <div class="lg:col-span-2">
            <?php if (empty($searchQuery)): ?>
            <div class="flex items-center justify-between mb-8">
                <h2 class="text-3xl font-bold text-gray-900">Berita Terbaru</h2>
                <div class="flex space-x-2">
                    <button class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium">Semua</button>
                    <?php foreach (array_slice($categories, 0, 2) as $category): ?>
                    <a href="<?php echo $base_url; ?>/category/<?php echo htmlspecialchars($category['slug']); ?>" 
                       class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg font-medium transition-colors">
                        <?php echo htmlspecialchars($category['name']); ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php if (!empty($articles)): ?>
            <div class="grid md:grid-cols-2 gap-8">
                <?php foreach($articles as $index => $article): ?>
                <article class="bg-white rounded-2xl shadow-lg hover-lift stagger-<?php echo ($index % 4) + 1; ?>">
                    <div class="relative">
                        <img src="<?php echo htmlspecialchars($article['featured_image'] ?? $article['image']); ?>" 
                             alt="<?php echo htmlspecialchars($article['title']); ?>" 
                             class="w-full h-48 object-cover rounded-t-2xl"
                             onerror="this.src='https://images.unsplash.com/photo-1518770660439-4636190af475?w=400&h=250&fit=crop'">
                        <span class="absolute top-4 left-4 bg-white/90 text-gray-800 px-3 py-1 rounded-full text-sm font-medium">
                            <?php echo htmlspecialchars($article['category_name'] ?? $article['category']); ?>
                        </span>
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-900 mb-3 line-clamp-2 hover:text-blue-600 transition-colors">
                            <a href="<?php echo $base_url; ?>/article/<?php echo htmlspecialchars($article['slug']); ?>">
                                <?php echo htmlspecialchars($article['title']); ?>
                            </a>
                        </h3>
                        <p class="text-gray-600 mb-4 line-clamp-3">
                            <?php echo htmlspecialchars($article['excerpt']); ?>
                        </p>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <div class="w-8 h-8 bg-gradient-to-r from-blue-400 to-purple-500 rounded-full flex items-center justify-center">
                                    <span class="text-xs font-bold text-white">
                                        <?php echo strtoupper(substr($article['author_name'] ?? $article['author'], 0, 1)); ?>
                                    </span>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($article['author_name'] ?? $article['author']); ?></p>
                                    <p class="text-xs text-gray-500"><?php echo htmlspecialchars($article['date']); ?></p>
                                </div>
                            </div>
                            <a href="<?php echo $base_url; ?>/article/<?php echo htmlspecialchars($article['slug']); ?>" 
                               class="text-blue-600 hover:text-blue-700 font-medium text-sm">
                                Baca →
                            </a>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>

            <!-- Load More Button -->
            <div class="text-center mt-12">
                <button onclick="loadMoreArticles()" 
                        class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-8 py-3 rounded-full font-medium transition-colors duration-200">
                    Muat Lebih Banyak
                </button>
            </div>
            <?php else: ?>
            <div class="text-center py-12">
                <p class="text-gray-600">Belum ada artikel tersedia.</p>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <aside class="lg:col-span-1">
            <!-- Search refinement for search results -->
            <?php if (!empty($searchQuery)): ?>
            <div class="bg-white rounded-2xl shadow-lg p-6 mb-8">
                <h3 class="text-xl font-bold text-gray-900 mb-4">Perbaiki Pencarian</h3>
                <form method="GET" action="<?php echo $base_url; ?>" class="space-y-3">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($searchQuery); ?>" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="Ubah kata kunci...">
                    <button type="submit" 
                            class="w-full bg-blue-600 text-white py-2 rounded-lg font-medium hover:bg-blue-700 transition-colors">
                        Cari Lagi
                    </button>
                    <a href="<?php echo $base_url; ?>" 
                       class="block w-full text-center bg-gray-100 text-gray-700 py-2 rounded-lg font-medium hover:bg-gray-200 transition-colors">
                        Hapus Pencarian
                    </a>
                </form>
            </div>
            <?php endif; ?>

            <!-- Trending Topics -->
            <?php if (!empty($trending_topics)): ?>
            <div class="bg-white rounded-2xl shadow-lg p-6 mb-8 animate-fade-in">
                <h3 class="text-xl font-bold text-gray-900 mb-6">Topik Trending</h3>
                <div class="space-y-3">
                    <?php foreach($trending_topics as $topic): ?>
                    <a href="<?php echo $base_url; ?>?search=<?php echo urlencode($topic['name']); ?>" 
                       class="block p-3 bg-gray-50 hover:bg-blue-50 rounded-lg transition-colors duration-200 group">
                        <span class="text-gray-700 group-hover:text-blue-600 font-medium">
                            #<?php echo htmlspecialchars($topic['name']); ?>
                        </span>
                        <span class="text-xs text-gray-500 ml-2"><?php echo $topic['usage_count']; ?>x</span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Newsletter Signup -->
            <div id="newsletter-form" class="bg-gradient-to-br from-blue-600 to-purple-600 rounded-2xl p-6 text-white animate-fade-in">
                <h3 class="text-xl font-bold mb-3">Dapatkan Update Terbaru</h3>
                <p class="text-blue-100 mb-6">Berlangganan newsletter kami untuk mendapatkan berita inovasi terdepan</p>
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

            <!-- Quick Stats -->
            <div class="bg-white rounded-2xl shadow-lg p-6 mt-8 animate-fade-in">
                <h3 class="text-xl font-bold text-gray-900 mb-6">Statistik Platform</h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Total Artikel</span>
                        <span class="font-bold text-2xl text-blue-600">
                            <?php echo number_format($statistics['total_articles'] ?? 0); ?>
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Pembaca Aktif</span>
                        <span class="font-bold text-2xl text-green-600">
                            <?php echo number_format($statistics['active_readers'] ?? 0); ?>
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Startup Featured</span>
                        <span class="font-bold text-2xl text-purple-600">
                            <?php echo number_format($statistics['featured_startups'] ?? 0); ?>
                        </span>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</main>

<script>
// Load more articles function (would need to be implemented with AJAX)
function loadMoreArticles() {
    // Implementation would involve AJAX call to load more articles
    console.log('Loading more articles...');
    // For now, just show a message
    alert('Fitur "Muat Lebih Banyak" akan diimplementasikan dengan AJAX untuk memuat artikel tambahan dari database.');
}

// Search enhancement functions
document.addEventListener('DOMContentLoaded', function() {
    // Highlight search terms in results
    const searchQuery = '<?php echo addslashes($searchQuery ?? ''); ?>';
    if (searchQuery) {
        highlightSearchTerms(searchQuery);
    }
    
    // Auto-focus search input if on search results page
    const searchInput = document.querySelector('input[name="search"]');
    if (searchQuery && searchInput) {
        searchInput.focus();
        searchInput.setSelectionRange(searchInput.value.length, searchInput.value.length);
    }
});

function highlightSearchTerms(query) {
    if (!query) return;
    
    const terms = query.toLowerCase().split(' ').filter(term => term.length > 2);
    const articles = document.querySelectorAll('article h3, article p');
    
    articles.forEach(element => {
        let html = element.innerHTML;
        terms.forEach(term => {
            const regex = new RegExp(`(${term})`, 'gi');
            html = html.replace(regex, '<mark class="bg-yellow-200 px-1 rounded">$1</mark>');
        });
        element.innerHTML = html;
    });
}
</script>