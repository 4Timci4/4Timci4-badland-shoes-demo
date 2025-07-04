<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supabase Bağlantı Testi (JavaScript)</title>
    <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
    <style>
        body { font-family: sans-serif; line-height: 1.6; padding: 20px; }
        h1, h2 { border-bottom: 1px solid #ccc; padding-bottom: 10px; }
        ul { padding-left: 20px; }
        .status { font-weight: bold; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <h1>Supabase Bağlantı Testi (JavaScript İstemcisi)</h1>

    <h2>Bağlantı Bilgileri</h2>
    <p>Supabase URL: <span id="supabase-url"></span></p>
    <p>API Key: <span id="supabase-key"></span></p>
    
    <h2>Bağlantı Durumu</h2>
    <p id="connection-status" class="status">Bağlantı kuruluyor...</p>
    <pre id="error-details" class="error"></pre>

    <h2>Kategoriler</h2>
    <div id="categories-list"><p>Yükleniyor...</p></div>

    <h2>Renkler</h2>
    <div id="colors-list"><p>Yükleniyor...</p></div>

    <h2>Bedenler</h2>
    <div id="sizes-list"><p>Yükleniyor...</p></div>

    <h2>Ürün Modelleri</h2>
    <div id="products-list"><p>Yükleniyor...</p></div>

    <script>
        // PHP'den Supabase bilgilerini al
        <?php require_once 'config/database.php'; ?>
        const supabaseUrl = '<?php echo SUPABASE_URL; ?>';
        const supabaseKey = '<?php echo SUPABASE_KEY; ?>';

        document.getElementById('supabase-url').textContent = supabaseUrl;
        document.getElementById('supabase-key').textContent = supabaseKey.substring(0, 10) + '...' + supabaseKey.substring(supabaseKey.length - 5);

        // Supabase istemcisini oluştur
        const { createClient } = supabase;
        const _supabase = createClient(supabaseUrl, supabaseKey);

        async function testConnection() {
            const statusEl = document.getElementById('connection-status');
            const errorEl = document.getElementById('error-details');
            
            try {
                // Basit bir sorgu ile bağlantıyı test et
                const { error } = await _supabase.from('categories').select('id').limit(1);

                if (error) {
                    throw error;
                }

                statusEl.textContent = '✅ Supabase bağlantısı başarılı!';
                statusEl.className = 'status success';
                errorEl.textContent = '';
                
                // Tüm verileri getir
                fetchCategories();
                fetchColors();
                fetchSizes();
                fetchProducts();

            } catch (error) {
                statusEl.textContent = '❌ Supabase bağlantısı başarısız!';
                statusEl.className = 'status error';
                errorEl.textContent = 'Hata: ' + error.message;
                
                // Diğer listeleri de hata mesajıyla güncelle
                document.getElementById('categories-list').innerHTML = '<p class="error">Kategoriler alınamadı.</p>';
                document.getElementById('colors-list').innerHTML = '<p class="error">Renkler alınamadı.</p>';
                document.getElementById('sizes-list').innerHTML = '<p class="error">Bedenler alınamadı.</p>';
                document.getElementById('products-list').innerHTML = '<p class="error">Ürünler alınamadı.</p>';
            }
        }

        async function fetchCategories() {
            const listEl = document.getElementById('categories-list');
            const { data, error } = await _supabase.from('categories').select('*');
            if (error) {
                listEl.innerHTML = `<p class="error">Hata: ${error.message}</p>`;
                return;
            }
            if (data && data.length > 0) {
                const ul = document.createElement('ul');
                data.forEach(item => {
                    const li = document.createElement('li');
                    li.textContent = `${item.name} (Slug: ${item.slug})`;
                    ul.appendChild(li);
                });
                listEl.innerHTML = '';
                listEl.appendChild(ul);
            } else {
                listEl.innerHTML = '<p>Kategori bulunamadı.</p>';
            }
        }

        async function fetchColors() {
            const listEl = document.getElementById('colors-list');
            const { data, error } = await _supabase.from('colors').select('*');
            if (error) {
                listEl.innerHTML = `<p class="error">Hata: ${error.message}</p>`;
                return;
            }
            if (data && data.length > 0) {
                const ul = document.createElement('ul');
                data.forEach(item => {
                    const li = document.createElement('li');
                    li.innerHTML = `<strong style="color:${item.hex_code};">&#9632;</strong> ${item.name} (${item.hex_code})`;
                    ul.appendChild(li);
                });
                listEl.innerHTML = '';
                listEl.appendChild(ul);
            } else {
                listEl.innerHTML = '<p>Renk bulunamadı.</p>';
            }
        }

        async function fetchSizes() {
            const listEl = document.getElementById('sizes-list');
            const { data, error } = await _supabase.from('sizes').select('*');
            if (error) {
                listEl.innerHTML = `<p class="error">Hata: ${error.message}</p>`;
                return;
            }
            if (data && data.length > 0) {
                const ul = document.createElement('ul');
                data.forEach(item => {
                    const li = document.createElement('li');
                    li.textContent = `${item.size_value} (${item.size_type})`;
                    ul.appendChild(li);
                });
                listEl.innerHTML = '';
                listEl.appendChild(ul);
            } else {
                listEl.innerHTML = '<p>Beden bulunamadı.</p>';
            }
        }

        async function fetchProducts() {
            const listEl = document.getElementById('products-list');
            const { data, error } = await _supabase.from('product_models').select('*').limit(5);
            if (error) {
                listEl.innerHTML = `<p class="error">Hata: ${error.message}</p>`;
                return;
            }
            if (data && data.length > 0) {
                const ul = document.createElement('ul');
                data.forEach(item => {
                    const li = document.createElement('li');
                    li.textContent = `${item.name} (Fiyat: ${item.base_price})`;
                    ul.appendChild(li);
                });
                listEl.innerHTML = '';
                listEl.appendChild(ul);
            } else {
                listEl.innerHTML = '<p>Ürün bulunamadı.</p>';
            }
        }

        // Sayfa yüklendiğinde bağlantıyı test et
        document.addEventListener('DOMContentLoaded', testConnection);
    </script>
</body>
</html>