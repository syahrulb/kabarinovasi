<!-- Article Detail Page -->
<main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- Breadcrumb -->
    <nav class="mb-8">
        <ol class="flex items-center space-x-2 text-sm text-gray-500">
            <li><a href="<?php echo $base_url; ?>" class="hover:text-blue-600 transition-colors">Beranda</a></li>
            <li><span class="mx-2">/</span></li>
            <li><a href="<?php echo $base_url; ?>/category/<?php echo htmlspecialchars($article['category_slug']); ?>" class="hover:text-blue-600 transition-colors"><?php echo htmlspecialchars($article['category_name']); ?></a></li>
            <li><span class="mx-2">/</span></li>
            <li class="text-gray-900 font-medium truncate"><?php echo htmlspecialchars($article['title']); ?></li>
        </ol>
    </nav>

    <!-- Article Header -->
    <header class="mb-8 animate-fade-in">
        <div class="mb-4">
            <span class="inline-block px-4 py-2 rounded-full text-sm font-medium text-white" 
                  style="background-color: <?php echo htmlspecialchars($article['category_color']); ?>;">
                <?php echo htmlspecialchars($article['category_name']); ?>
            </span>
        </div>
        
        <h1 class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 leading-tight mb-6">
            <?php echo htmlspecialchars($article['title']); ?>
        </h1>
        
        <p class="text-xl text-gray-600 leading-relaxed mb-8">
            <?php echo htmlspecialchars($article['excerpt']); ?>
        </p>

        <!-- Article Meta -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between border-b border-gray-200 pb-6 gap-4">
            <div class="flex items-center space-x-4">
                <img src="<?php echo htmlspecialchars($article['author_avatar'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($article['author_name']) . '&background=3b82f6&color=fff'); ?>" 
                     alt="<?php echo htmlspecialchars($article['author_name']); ?>"
                     class="w-12 h-12 rounded-full object-cover">
                <div>
                    <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($article['author_name']); ?></p>
                    <div class="flex items-center space-x-2 text-sm text-gray-500">
                        <time datetime="<?php echo date('Y-m-d', strtotime($article['published_at'])); ?>">
                            <?php echo date('d F Y', strtotime($article['published_at'])); ?>
                        </time>
                        <span>•</span>
                        <span><?php echo htmlspecialchars($article['read_time']); ?> menit baca</span>
                        <span>•</span>
                        <span><?php echo number_format($article['views_count']); ?> views</span>
                    </div>
                </div>
            </div>
            
            <!-- Social Share Buttons -->
            <div class="flex items-center space-x-3">
                <button onclick="shareArticle('twitter')" 
                        class="p-2 bg-blue-100 hover:bg-blue-200 text-blue-600 rounded-full transition-colors duration-200" 
                        title="Share di Twitter">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/>
                    </svg>
                </button>
                <button onclick="shareArticle('facebook')" 
                        class="p-2 bg-blue-100 hover:bg-blue-200 text-blue-600 rounded-full transition-colors duration-200"
                        title="Share di Facebook">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                </button>
                <button onclick="shareArticle('linkedin')" 
                        class="p-2 bg-blue-100 hover:bg-blue-200 text-blue-600 rounded-full transition-colors duration-200"
                        title="Share di LinkedIn">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                    </svg>
                </button>
                <button onclick="shareArticle('whatsapp')" 
                        class="p-2 bg-green-100 hover:bg-green-200 text-green-600 rounded-full transition-colors duration-200"
                        title="Share di WhatsApp">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"/>
                    </svg>
                </button>
                <button onclick="copyArticleLink()" 
                        class="p-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-full transition-colors duration-200"
                        title="Copy Link">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                </button>
            </div>
        </div>
    </header>

    <!-- Featured Image -->
    <div class="mb-10 animate-fade-in">
        <div class="relative rounded-2xl overflow-hidden shadow-lg">
            <img src="<?php echo htmlspecialchars($article['featured_image']); ?>" 
                 alt="<?php echo htmlspecialchars($article['title']); ?>"
                 class="w-full h-64 md:h-96 object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-black/10 to-transparent"></div>
        </div>
    </div>

    <!-- Article Content -->
    <article class="prose prose-lg max-w-none mb-12 animate-slide-up">
        <div class="text-gray-700 leading-relaxed text-lg">
            <?php 
            // Split content into paragraphs and add proper formatting
            $paragraphs = explode("\n\n", $article['content']);
            foreach ($paragraphs as $paragraph) {
                if (trim($paragraph)) {
                    echo '<p class="mb-6">' . nl2br(htmlspecialchars(trim($paragraph))) . '</p>';
                }
            }
            ?>
        </div>
    </article>

    <!-- Article Stats -->
    <div class="bg-gray-50 rounded-2xl p-6 mb-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
            <div>
                <div class="text-2xl font-bold text-blue-600"><?php echo number_format($article['views_count']); ?></div>
                <div class="text-sm text-gray-600">Views</div>
            </div>
            <div>
                <div class="text-2xl font-bold text-green-600"><?php echo htmlspecialchars($article['read_time']); ?></div>
                <div class="text-sm text-gray-600">Menit Baca</div>
            </div>
            <div>
                <div class="text-2xl font-bold text-purple-600"><?php echo date('d', strtotime($article['published_at'])); ?></div>
                <div class="text-sm text-gray-600"><?php echo date('M Y', strtotime($article['published_at'])); ?></div>
            </div>
            <div>
                <div class="text-2xl font-bold" style="color: <?php echo htmlspecialchars($article['category_color']); ?>;">
                    <?php echo htmlspecialchars($article['category_name']); ?>
                </div>
                <div class="text-sm text-gray-600">Kategori</div>
            </div>
        </div>
    </div>

    <!-- Article Tags -->
    <?php if (!empty($article['tags'])): ?>
    <div class="mb-8">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Tags:</h3>
        <div class="flex flex-wrap gap-2">
            <?php foreach (explode(', ', $article['tags']) as $tag): ?>
            <span class="bg-gray-100 hover:bg-blue-100 text-gray-700 hover:text-blue-700 px-3 py-1 rounded-full text-sm transition-colors cursor-pointer">
                #<?php echo htmlspecialchars(trim($tag)); ?>
            </span>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Author Bio -->
    <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-2xl p-6 mb-12">
        <div class="flex items-start space-x-4">
            <img src="<?php echo htmlspecialchars($article['author_avatar'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($article['author_name']) . '&background=3b82f6&color=fff'); ?>" 
                 alt="<?php echo htmlspecialchars($article['author_name']); ?>"
                 class="w-16 h-16 rounded-full object-cover shadow-lg">
            <div class="flex-1">
                <h3 class="text-xl font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($article['author_name']); ?></h3>
                <p class="text-gray-600 leading-relaxed mb-4">
                    <?php if (!empty($article['author_email'])): ?>
                        Penulis berpengalaman dalam bidang <?php echo htmlspecialchars($article['category_name']); ?> dengan fokus pada inovasi dan teknologi terdepan.
                    <?php else: ?>
                        Penulis ahli di bidang <?php echo htmlspecialchars($article['category_name']); ?> yang selalu mengikuti perkembangan terbaru industri.
                    <?php endif; ?>
                </p>
                <?php if (!empty($article['author_email'])): ?>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-500 flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <?php echo htmlspecialchars($article['author_email']); ?>
                    </span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex flex-col sm:flex-row justify-center items-center space-y-4 sm:space-y-0 sm:space-x-4 mb-12">
        <a href="<?php echo $base_url; ?>/category/<?php echo htmlspecialchars($article['category_slug']); ?>" 
           class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-full transition-colors duration-200">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
            </svg>
            Lihat <?php echo htmlspecialchars($article['category_name']); ?> Lainnya
        </a>
        
        <a href="<?php echo $base_url; ?>" 
           class="inline-flex items-center px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-full transition-colors duration-200">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Kembali ke Beranda
        </a>
    </div>

    <!-- Related Articles Placeholder -->
    <div class="border-t border-gray-200 pt-12">
        <h2 class="text-2xl font-bold text-gray-900 mb-8 text-center">Artikel Terkait</h2>
        <div class="bg-gray-50 rounded-2xl p-8 text-center">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-6h.01M9 11h4m0 4h.01"/>
            </svg>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">Artikel Terkait Akan Segera Hadir</h3>
            <p class="text-gray-600">Kami sedang menyiapkan rekomendasi artikel terkait untuk Anda</p>
        </div>
    </div>
</main>

<script>
function shareArticle(platform) {
    const url = encodeURIComponent(window.location.href);
    const title = encodeURIComponent('<?php echo addslashes($article['title']); ?>');
    const description = encodeURIComponent('<?php echo addslashes($article['excerpt']); ?>');
    
    let shareUrl = '';
    
    switch(platform) {
        case 'twitter':
            shareUrl = `https://twitter.com/intent/tweet?url=${url}&text=${title}`;
            break;
        case 'facebook':
            shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${url}`;
            break;
        case 'linkedin':
            shareUrl = `https://www.linkedin.com/sharing/share-offsite/?url=${url}`;
            break;
        case 'whatsapp':
            shareUrl = `https://wa.me/?text=${title}%20${url}`;
            break;
    }
    
    if (shareUrl) {
        window.open(shareUrl, '_blank', 'width=600,height=400,scrollbars=yes,resizable=yes');
    }
}

function copyArticleLink() {
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(window.location.href).then(function() {
            showNotification('Link artikel berhasil disalin!', 'success');
        }).catch(function(err) {
            console.error('Could not copy text: ', err);
            fallbackCopyTextToClipboard(window.location.href);
        });
    } else {
        fallbackCopyTextToClipboard(window.location.href);
    }
}

function fallbackCopyTextToClipboard(text) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.left = '-999999px';
    textArea.style.top = '-999999px';
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        const successful = document.execCommand('copy');
        if (successful) {
            showNotification('Link artikel berhasil disalin!', 'success');
        } else {
            showNotification('Gagal menyalin link artikel', 'error');
        }
    } catch (err) {
        console.error('Fallback: Oops, unable to copy', err);
        showNotification('Gagal menyalin link artikel', 'error');
    }
    
    document.body.removeChild(textArea);
}

function showNotification(message, type) {
    // Remove existing notifications
    const existing = document.querySelectorAll('.notification');
    existing.forEach(el => el.remove());
    
    const notification = document.createElement('div');
    notification.className = `notification fixed top-4 right-4 z-50 max-w-sm p-4 rounded-lg shadow-lg transition-all duration-300 ${
        type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
    }`;
    notification.innerHTML = `
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                ${type === 'success' 
                    ? '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>'
                    : '<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>'
                }
            </svg>
            ${message}
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Auto hide after 3 seconds
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateY(-20px)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

// Add reading progress indicator
document.addEventListener('DOMContentLoaded', function() {
    const progressBar = document.createElement('div');
    progressBar.className = 'fixed top-0 left-0 w-0 h-1 bg-blue-600 z-50 transition-all duration-150';
    document.body.appendChild(progressBar);
    
    window.addEventListener('scroll', function() {
        const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
        const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
        const scrolled = (winScroll / height) * 100;
        progressBar.style.width = scrolled + '%';
    });
    
    // Smooth fade-in animations
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, { threshold: 0.1 });
    
    document.querySelectorAll('.animate-fade-in, .animate-slide-up').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'all 0.6s ease-out';
        observer.observe(el);
    });
});
</script>