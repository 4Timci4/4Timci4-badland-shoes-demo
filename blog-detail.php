<?php include 'includes/header.php'; ?>

<?php
// Blog ID'sini al
$blog_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Blog yazıları (gerçek uygulamada veritabanından çekilir)
$blog_posts = [
    1 => [
        'id' => 1,
        'title' => '2025 Yaz Ayakkabı Trendleri',
        'excerpt' => 'Bu yazımızda 2025 yazında öne çıkan ayakkabı trendlerini ve sezonun en popüler modellerini inceliyoruz.',
        'content' => '<p>2025 yaz sezonu, ayakkabı dünyasında yenilikçi tasarımlar ve cesur renklerin öne çıktığı bir dönem olarak karşımıza çıkıyor. Bu sezon ayakkabı trendlerinde minimalist tasarımlardan gösterişli modellere kadar geniş bir yelpaze sunuluyor.</p>
        <h3>1. Platform Sandalet ve Terlikler</h3>
        <p>90\'ların nostaljik havası, platform sandalet ve terliklerin geri dönüşüyle devam ediyor. Özellikle pastel tonlardaki platform sandaletler ve kalın tabanlı terlikler, 2025 yazının öne çıkan parçaları arasında yer alıyor.</p>
        <p>Bu modeller hem rahat hem de şık bir görünüm sağladığı için günlük kullanımda ve özel etkinliklerde tercih edilebiliyor. Canlı renkler ve metalik detaylar, bu sezonun platform ayakkabılarında sıkça karşımıza çıkan özellikler.</p>
        <h3>2. Sürdürülebilir Malzemeli Ayakkabılar</h3>
        <p>Çevre bilinci ve sürdürülebilirlik trendi, 2025 yazında ayakkabı dünyasında da etkisini gösteriyor. Geri dönüştürülmüş malzemelerden üretilen ayakkabılar, vegan deri alternatifler ve doğa dostu üretim süreçleriyle tasarlanan modeller bu yaz çok popüler.</p>
        <p>Özellikle bambu, kenevir ve organik pamuk gibi sürdürülebilir malzemelerden üretilen espadril ve kanvas ayakkabılar, yaz gardırobunun vazgeçilmezleri arasında yer alıyor.</p>
        <h3>3. Maksi Bantlı Sandaletler</h3>
        <p>İnce ve minimal bantlı sandaletler yerini daha kalın ve gösterişli bantlı modellere bırakıyor. Ayak bileğinden dolanan maksi bantlar ve çapraz tasarımlar, 2025 yazının en çok tercih edilen sandalet modellerini oluşturuyor.</p>
        <p>Bu tarz sandaletler özellikle akşam davetlerinde ve özel etkinliklerde şık bir seçenek olarak karşımıza çıkıyor. Metalik tonlar, canlı renkler ve desenli modeller öne çıkıyor.</p>
        <h3>4. Şeffaf Detaylı Ayakkabılar</h3>
        <p>Şeffaf malzemeler ve vinil detaylar, 2025 yaz sezonunun dikkat çeken trendleri arasında. Şeffaf topuklar, bantlar veya ayakkabının tamamen şeffaf olduğu modeller büyük ilgi görüyor.</p>
        <p>Bu trend, ayakkabılara modern ve futuristik bir hava katarken, aynı zamanda zarif bir görünüm de sağlıyor. Şeffaf detaylar genellikle metalik renkler ve neon tonlarla kombinleniyor.</p>
        <h3>5. Örgü Dokulu Ayakkabılar</h3>
        <p>El yapımı görünümlü örgü dokulu ayakkabılar, 2025 yazının yükselen trendleri arasında. Hasır dokulu topuklar, örgü bantlar ve makrame detaylar, yaz ayakkabılarına bohem bir hava katıyor.</p>
        <p>Bu tarz ayakkabılar özellikle plaj partileri ve yaz düğünleri için ideal bir seçenek oluşturuyor. Doğal tonlar ve toprak renkleri bu modellerde sıkça kullanılıyor.</p>
        <p>Sonuç olarak, 2025 yaz sezonu hem rahat hem de şık ayakkabı modelleriyle dolu. Sürdürülebilirlik, nostalji ve modern detayların bir araya geldiği bu sezonda, her tarza ve ihtiyaca uygun bir ayakkabı trendi bulmak mümkün.</p>',
        'image' => 'https://images.unsplash.com/photo-1535043934128-cf0b28d52f95?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxzZWFyY2h8NXx8c2hvZXMlMjBzdW1tZXJ8ZW58MHx8MHx8&auto=format&fit=crop&w=500&q=60',
        'date' => '15 Haziran 2025',
        'author' => 'Zeynep Kaya',
        'category' => 'Trendler',
        'tags' => ['Yaz Modası', 'Trendler', 'Sandalet', 'Platform Ayakkabı', 'Sürdürülebilirlik']
    ],
    2 => [
        'id' => 2,
        'title' => 'Doğru Ayakkabı Seçiminin Önemi',
        'excerpt' => 'Ayak sağlığınız için doğru ayakkabı seçiminin neden önemli olduğunu ve nelere dikkat etmeniz gerektiğini anlatıyoruz.',
        'content' => '<p>Ayakkabı seçimi, sadece estetik bir konu değil, aynı zamanda sağlığımızı da doğrudan etkileyen önemli bir faktördür. Yanlış ayakkabı seçimi, ayak ağrılarından başlayarak sırt ve bel problemlerine kadar uzanan birçok sağlık sorununa yol açabilir.</p>
        <h3>Ayak Sağlığı ve Ayakkabı İlişkisi</h3>
        <p>Ayaklarımız vücudumuzu taşıyan en önemli yapılardır ve günde ortalama 8.000-10.000 adım attığımızı düşünürsek, doğru ayakkabı seçiminin önemi daha iyi anlaşılır. Yanlış ayakkabılar, nasır, bunyon, tırnak batması gibi lokal problemlere neden olabildiği gibi, duruş bozuklukları ve eklem ağrıları gibi daha kapsamlı sorunlara da yol açabilir.</p>
        <h3>Doğru Ayakkabı Nasıl Seçilir?</h3>
        <p>Doğru ayakkabı seçerken dikkat edilmesi gereken bazı önemli noktalar şunlardır:</p>
        <h4>1. Doğru Beden</h4>
        <p>Ayakkabı ne çok dar ne de çok geniş olmalıdır. Ayak parmaklarınızın rahatça hareket edebildiği, aynı zamanda topuğunuzu sıkıca saran bir model tercih edilmelidir. Gün içinde ayaklarımız şiştiği için, ayakkabı alışverişini öğleden sonra yapmak daha doğrudur.</p>
        <h4>2. Ayak Yapısına Uygunluk</h4>
        <p>Herkesin ayak yapısı farklıdır. Düz tabanlı, yüksek kemerli veya pronasyon (içe basma) problemi olan kişiler, kendi ayak yapılarına uygun ayakkabılar tercih etmelidir. Özellikle spor ayakkabı alırken bu faktör çok önemlidir.</p>
        <h4>3. Kaliteli Malzeme</h4>
        <p>Ayakkabının malzemesi nefes alabilir özellikte olmalıdır. Doğal malzemeler genellikle sentetik olanlara göre daha sağlıklıdır. Kaliteli bir iç taban ve şok emici bir dış taban, ayak sağlığı için önemlidir.</p>
        <h4>4. Topuk Yüksekliği</h4>
        <p>Sürekli yüksek topuklu ayakkabı giymek, ayak, diz ve bel problemlerine yol açabilir. Topuk yüksekliği idealde 3-4 cm\'yi geçmemelidir. Yüksek topuklu giyilmesi gerekiyorsa, gün içinde daha düz tabanlı ayakkabılarla değiştirmek faydalı olacaktır.</p>
        <h4>5. Aktiviteye Uygunluk</h4>
        <p>Her aktivite için farklı ayakkabılar tercih edilmelidir. Koşu ayakkabısı ile basketbol oynamak veya günlük ayakkabı ile uzun yürüyüşlere çıkmak doğru değildir. Her spor dalı için özel tasarlanmış ayakkabılar kullanılmalıdır.</p>
        <h3>Çocuklarda Ayakkabı Seçimi</h3>
        <p>Çocuklarda ayakkabı seçimi daha da önemlidir çünkü ayakları hala gelişim aşamasındadır. Çocuk ayakkabıları esnek, hafif ve doğal malzemeden yapılmış olmalıdır. Ayak gelişimini destekleyecek bir taban yapısına sahip olması da önemlidir.</p>
        <h3>Yaşlılarda Ayakkabı Seçimi</h3>
        <p>Yaşlı bireylerde denge problemleri ve düşme riski arttığı için, kaymaz tabanlı, bağcıklı veya cırt cırtlı, stabiliteyi artıran ayakkabılar tercih edilmelidir. Ayrıca ayak problemleri yaşla birlikte artabildiği için, geniş ve rahat modeller seçilmelidir.</p>
        <p>Sonuç olarak, doğru ayakkabı seçimi hem ayak sağlığımız hem de genel vücut sağlığımız için çok önemlidir. Stil ve moda kadar, konfor ve uygunluk da ayakkabı seçerken göz önünde bulundurulması gereken faktörlerdir. Sağlıklı ayaklar için doğru ayakkabılar tercih edin.</p>',
        'image' => 'https://images.unsplash.com/photo-1515347619252-60a4bf4fff4f?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxzZWFyY2h8M3x8c2hvZXMlMjBoZWFsdGh8ZW58MHx8MHx8&auto=format&fit=crop&w=500&q=60',
        'date' => '02 Haziran 2025',
        'author' => 'Dr. Ahmet Öz',
        'category' => 'Sağlık',
        'tags' => ['Ayak Sağlığı', 'Doğru Ayakkabı', 'Sağlık', 'Ayakkabı Seçimi']
    ],
    3 => [
        'id' => 3,
        'title' => 'Ayakkabı Bakımı İçin 10 İpucu',
        'excerpt' => 'Ayakkabılarınızın ömrünü uzatmak ve her zaman güzel görünmelerini sağlamak için uygulayabileceğiniz bakım ipuçları.',
        'content' => '<p>Kaliteli bir ayakkabı, doğru bakımla yıllarca kullanılabilir. Ayakkabılarınızın hem ömrünü uzatmak hem de her zaman yeni gibi görünmelerini sağlamak için uygulayabileceğiniz 10 etkili bakım ipucunu sizlerle paylaşıyoruz.</p>
        <h3>1. Düzenli Temizlik</h3>
        <p>Ayakkabılarınızı her kullanımdan sonra hafifçe temizleyin. Toz ve kirlerin birikmesi, malzemenin zaman içinde yıpranmasına neden olur. Deri ayakkabılar için nemli bir bez kullanabilir, süet ve nubuk gibi malzemelerde ise özel fırçalar tercih edebilirsiniz.</p>
        <h3>2. Doğru Temizleme Ürünleri Kullanın</h3>
        <p>Her ayakkabı malzemesi için özel temizleme ürünleri vardır. Deri için deri temizleyici, süet için süet temizleyici gibi doğru ürünleri kullanmak, ayakkabılarınızın malzemesinin zarar görmesini engeller.</p>
        <h3>3. Su ve Leke Koruyucu Spreyler</h3>
        <p>Yeni aldığınız ayakkabılara ve düzenli olarak kullanılanlara su ve leke koruyucu sprey uygulamak, onları dış etkenlerden korur. Bu spreyler özellikle deri, süet ve kumaş ayakkabılar için önemlidir.</p>
        <h3>4. Nem Kontrolü</h3>
        <p>Ayakkabılar ıslandığında doğal bir şekilde kurumasını sağlayın. Direkt ısı kaynaklarından (kalorifer, soba vb.) uzak tutun çünkü hızlı kurutma, malzemenin çatlamasına veya deforme olmasına neden olabilir. İçlerine gazete kağıdı yerleştirmek nemi çekmede yardımcı olur.</p>
        <h3>5. Ayakkabı Kalıpları Kullanın</h3>
        <p>Kullanmadığınız zamanlarda ayakkabılarınızı, şekillerini korumaları için ahşap ayakkabı kalıplarıyla saklayın. Bu, özellikle deri ayakkabılar için önemlidir çünkü derinin kırışmasını ve formunu kaybetmesini engeller.</p>
        <h3>6. Düzenli Cilalama</h3>
        <p>Deri ayakkabılar için düzenli cilalama, hem koruyucu bir tabaka oluşturur hem de görünümlerini yeniler. Ayakkabınızın rengine uygun bir cila seçin ve yumuşak bir bezle uygulayın.</p>
        <h3>7. Rotasyon Yapın</h3>
        <p>Aynı ayakkabıyı her gün giymek yerine, birkaç çift arasında rotasyon yapın. Bu, her çiftin havalanması ve dinlenmesi için zaman tanır, böylece ömürlerini uzatır.</p>
        <h3>8. Doğru Saklama Koşulları</h3>
        <p>Ayakkabılarınızı direkt güneş ışığı almayan, kuru ve havadar bir yerde saklayın. Nem, küf oluşumuna neden olabilir; güneş ışığı ise renk solmasına yol açabilir.</p>
        <h3>9. Zamanında Tamir Ettirin</h3>
        <p>Küçük sorunlar (aşınan taban, gevşeyen dikiş vb.) büyümeden bir ayakkabı tamircisine gösterin. Zamanında yapılan küçük tamirler, daha büyük ve pahalı tamirleri önleyebilir.</p>
        <h3>10. Ayakkabı Bakım Kiti Bulundurun</h3>
        <p>Temel bir ayakkabı bakım kiti içinde çeşitli fırçalar, cilalar, temizleyiciler ve koruyucu spreyler bulundurmak, ayakkabılarınızın düzenli bakımını kolaylaştırır.</p>
        <p>Bu bakım ipuçlarını düzenli olarak uygulayarak, ayakkabılarınızın hem daha uzun ömürlü olmasını hem de her zaman şık görünmesini sağlayabilirsiniz. Unutmayın, kaliteli bir ayakkabıya yapılan yatırım, doğru bakımla katlanarak geri döner.</p>',
        'image' => 'https://images.unsplash.com/photo-1553341640-4ba55ddee5c4?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxzZWFyY2h8MTF8fHNob2UlMjBjYXJlfGVufDB8fDB8fA%3D%3D&auto=format&fit=crop&w=500&q=60',
        'date' => '25 Mayıs 2025',
        'author' => 'Mehmet Öztürk',
        'category' => 'Bakım',
        'tags' => ['Ayakkabı Bakımı', 'Temizlik', 'Deri Bakımı', 'Süet Bakımı', 'İpuçları']
    ]
];

// Eğer blog yazısı bulunamazsa geri yönlendir
if (!isset($blog_posts[$blog_id])) {
    header("Location: blog.php");
    exit;
}

// Blog yazısı bilgilerini al
$post = $blog_posts[$blog_id];
?>

<!-- Blog Detay -->
<section class="blog-detail">
    <div class="container">
        <div class="blog-header">
            <h1><?php echo $post['title']; ?></h1>
            <div class="blog-meta">
                <span class="category"><?php echo $post['category']; ?></span>
                <span class="date"><i class="far fa-calendar"></i> <?php echo $post['date']; ?></span>
                <span class="author"><i class="far fa-user"></i> <?php echo $post['author']; ?></span>
            </div>
        </div>
        
        <div class="blog-featured-image">
            <img src="<?php echo $post['image']; ?>" alt="<?php echo $post['title']; ?>">
        </div>
        
        <div class="blog-content">
            <?php echo $post['content']; ?>
        </div>
        
        <?php if (isset($post['tags']) && !empty($post['tags'])): ?>
        <div class="blog-tags">
            <h3>Etiketler:</h3>
            <div class="tags">
                <?php foreach($post['tags'] as $tag): ?>
                    <a href="/blog.php?tag=<?php echo urlencode($tag); ?>" class="tag"><?php echo $tag; ?></a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Paylaşım Linkleri -->
        <div class="blog-share">
            <h3>Bu Yazıyı Paylaş:</h3>
            <div class="social-share">
                <a href="#" class="facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="twitter"><i class="fab fa-twitter"></i></a>
                <a href="#" class="linkedin"><i class="fab fa-linkedin-in"></i></a>
                <a href="#" class="whatsapp"><i class="fab fa-whatsapp"></i></a>
                <a href="#" class="email"><i class="far fa-envelope"></i></a>
            </div>
        </div>
        
        <!-- Yazar Bilgisi -->
        <div class="author-box">
            <div class="author-image">
                <img src="https://i.pravatar.cc/150?u=<?php echo $post['author']; ?>" alt="<?php echo $post['author']; ?>">
            </div>
            <div class="author-info">
                <h3><?php echo $post['author']; ?></h3>
                <p>Bandland Shoes markasının deneyimli yazarlarından biri olan <?php echo $post['author']; ?>, ayakkabı dünyasındaki trendler ve yenilikler hakkında düzenli olarak içerikler üretmektedir.</p>
                <div class="author-social">
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
        </div>
        
        <!-- Benzer Yazılar -->
        <div class="related-posts">
            <h2>Benzer Yazılar</h2>
            <div class="related-grid">
                <?php
                // Aynı kategorideki diğer yazıları göster
                $related_posts = [];
                foreach($blog_posts as $related_post) {
                    if ($related_post['id'] != $post['id'] && $related_post['category'] == $post['category']) {
                        $related_posts[] = $related_post;
                    }
                    if (count($related_posts) >= 3) break; // En fazla 3 benzer yazı göster
                }
                
                foreach($related_posts as $related): 
                ?>
                    <div class="related-card">
                        <div class="related-image">
                            <img src="<?php echo $related['image']; ?>" alt="<?php echo $related['title']; ?>">
                        </div>
                        <div class="related-content">
                            <h3><?php echo $related['title']; ?></h3>
                            <div class="date"><?php echo $related['date']; ?></div>
                            <a href="/blog-detail.php?id=<?php echo $related['id']; ?>" class="read-more">Devamını Oku</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Yorumlar (Gerçek uygulamada veritabanından çekilir) -->
        <div class="comments-section">
            <h2>Yorumlar (2)</h2>
            
            <div class="comments-list">
                <div class="comment">
                    <div class="comment-avatar">
                        <img src="https://i.pravatar.cc/80?u=user1" alt="Kullanıcı Avatar">
                    </div>
                    <div class="comment-content">
                        <div class="comment-header">
                            <h4>Mehmet Yılmaz</h4>
                            <span class="date">10 Haziran 2025</span>
                        </div>
                        <div class="comment-text">
                            <p>Çok faydalı bir yazı olmuş. Özellikle platform sandaletler hakkındaki bilgiler çok işime yaradı. Teşekkürler!</p>
                        </div>
                        <div class="comment-actions">
                            <a href="#" class="reply">Yanıtla</a>
                        </div>
                    </div>
                </div>
                
                <div class="comment">
                    <div class="comment-avatar">
                        <img src="https://i.pravatar.cc/80?u=user2" alt="Kullanıcı Avatar">
                    </div>
                    <div class="comment-content">
                        <div class="comment-header">
                            <h4>Ayşe Demir</h4>
                            <span class="date">12 Haziran 2025</span>
                        </div>
                        <div class="comment-text">
                            <p>Yaz trendleri hakkında çok bilgilendirici bir yazı olmuş. Sürdürülebilir ayakkabılar konusunda daha fazla bilgi verirseniz sevinirim.</p>
                        </div>
                        <div class="comment-actions">
                            <a href="#" class="reply">Yanıtla</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Yorum Formu -->
            <div class="comment-form">
                <h3>Yorum Yapın</h3>
                <form action="#" method="post">
                    <div class="form-group">
                        <label for="name">İsim *</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">E-posta *</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="comment">Yorumunuz *</label>
                        <textarea id="comment" name="comment" rows="5" required></textarea>
                    </div>
                    <button type="submit" class="btn">Yorum Gönder</button>
                </form>
            </div>
        </div>
    </div>
</section>

<link rel="stylesheet" href="/assets/css/blog-detail.css">

<?php include 'includes/footer.php'; ?>