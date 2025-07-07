<?php
/**
 * Ürün Servisi
 * 
 * Bu dosya, ürün verilerine erişim sağlayan servisi içerir.
 */

// Gerekli dosyaları dahil et
require_once __DIR__ . '/../lib/SupabaseClient.php';

/**
 * Ürün servis sınıfı
 * 
 * Ürünlerle ilgili tüm veritabanı işlemlerini içerir
 */
class ProductService {
    private $supabase;
    
    /**
     * ProductService sınıfını başlatır
     */
    public function __construct() {
        $this->supabase = supabase();
    }
    
    /**
     * Ürün modellerini getir
     * 
     * @param int $limit Limit
     * @param int $offset Offset
     * @param array $filters Filtreler
     * @return array Ürün modelleri
     */
    public function getProductModels($limit = 20, $offset = 0, $filters = []) {
        try {
            $query = [
                'select' => '*,categories(name)',
                'order' => 'created_at.desc',
                'limit' => $limit,
                'offset' => $offset
            ];
            
            // Filtreler
            if (!empty($filters['category_id'])) {
                $category_id = intval($filters['category_id']);
                // Kategori ID'ye göre ürünleri getir
                $product_ids_response = $this->supabase->request("product_categories?select=product_id&category_id=eq.$category_id");
                
                if (!empty($product_ids_response['body'])) {
                    $product_ids = array_column($product_ids_response['body'], 'product_id');
                    $query['id'] = 'in.(' . implode(',', $product_ids) . ')';
                } else {
                    // Eşleşen ürün yoksa boş dizi döndür
                    return [];
                }
            }
            
            if (!empty($filters['category_slug'])) {
                $category_slug = $filters['category_slug'];
                
                // Önce kategori ID'sini bul
                $category_response = $this->supabase->request("categories?select=id&slug=eq.$category_slug");
                
                if (!empty($category_response['body'])) {
                    $category_id = $category_response['body'][0]['id'];
                    
                    // Sonra bu kategoriye ait ürünleri getir
                    $product_ids_response = $this->supabase->request("product_categories?select=product_id&category_id=eq.$category_id");
                    
                    if (!empty($product_ids_response['body'])) {
                        $product_ids = array_column($product_ids_response['body'], 'product_id');
                        $query['id'] = 'in.(' . implode(',', $product_ids) . ')';
                    } else {
                        // Eşleşen ürün yoksa boş dizi döndür
                        return [];
                    }
                } else {
                    // Kategori bulunamadıysa boş dizi döndür
                    return [];
                }
            }
            
            // Cinsiyet filtresi
            if (!empty($filters['gender_id'])) {
                $gender_id = intval($filters['gender_id']);
                // Cinsiyet ID'ye göre ürünleri getir
                $product_ids_response = $this->supabase->request("product_genders?select=product_id&gender_id=eq.$gender_id");
                
                if (!empty($product_ids_response['body'])) {
                    $product_ids = array_column($product_ids_response['body'], 'product_id');
                    
                    // Eğer zaten başka bir filtre uygulanmışsa, kesişim al
                    if (isset($query['id'])) {
                        $existing_ids = explode(',', str_replace(['in.(', ')'], '', $query['id']));
                        $product_ids = array_intersect($product_ids, $existing_ids);
                    }
                    
                    if (!empty($product_ids)) {
                        $query['id'] = 'in.(' . implode(',', $product_ids) . ')';
                    } else {
                        // Eşleşen ürün yoksa boş dizi döndür
                        return [];
                    }
                } else {
                    // Eşleşen ürün yoksa boş dizi döndür
                    return [];
                }
            }
            
            if (!empty($filters['gender_slug'])) {
                $gender_slug = $filters['gender_slug'];
                
                // Önce cinsiyet ID'sini bul
                $gender_response = $this->supabase->request("genders?select=id&slug=eq.$gender_slug");
                
                if (!empty($gender_response['body'])) {
                    $gender_id = $gender_response['body'][0]['id'];
                    
                    // Sonra bu cinsiyete ait ürünleri getir
                    $product_ids_response = $this->supabase->request("product_genders?select=product_id&gender_id=eq.$gender_id");
                    
                    if (!empty($product_ids_response['body'])) {
                        $product_ids = array_column($product_ids_response['body'], 'product_id');
                        
                        // Eğer zaten başka bir filtre uygulanmışsa, kesişim al
                        if (isset($query['id'])) {
                            $existing_ids = explode(',', str_replace(['in.(', ')'], '', $query['id']));
                            $product_ids = array_intersect($product_ids, $existing_ids);
                        }
                        
                        if (!empty($product_ids)) {
                            $query['id'] = 'in.(' . implode(',', $product_ids) . ')';
                        } else {
                            // Eşleşen ürün yoksa boş dizi döndür
                            return [];
                        }
                    } else {
                        // Eşleşen ürün yoksa boş dizi döndür
                        return [];
                    }
                } else {
                    // Cinsiyet bulunamadıysa boş dizi döndür
                    return [];
                }
            }
            
            $response = $this->supabase->request('product_models?' . http_build_query($query));
            
            $products = $response['body'] ?? [];
            
            // Ürünleri zenginleştir: Kategori ve cinsiyet bilgilerini ekle
            foreach ($products as &$product) {
                // Ana kategori bilgisini almak için product_categories tablosuna sorgu
                $category_response = $this->supabase->request("product_categories?select=categories(name,slug)&product_id=eq.{$product['id']}&limit=1");
                
                if (!empty($category_response['body']) && isset($category_response['body'][0]['categories'])) {
                    $product['category_name'] = $category_response['body'][0]['categories']['name'];
                    $product['category_slug'] = $category_response['body'][0]['categories']['slug'];
                } else {
                    $product['category_name'] = null;
                    $product['category_slug'] = null;
                }
                
                // Cinsiyet bilgilerini almak için product_genders tablosuna sorgu
                $gender_response = $this->supabase->request("product_genders?select=genders(id,name,slug)&product_id=eq.{$product['id']}");
                
                if (!empty($gender_response['body'])) {
                    $genders = [];
                    foreach ($gender_response['body'] as $relation) {
                        if (isset($relation['genders'])) {
                            $genders[] = $relation['genders'];
                        }
                    }
                    $product['genders'] = $genders;
                } else {
                    $product['genders'] = [];
                }
            }
            
            return $products;
        } catch (Exception $e) {
            error_log("ProductService::getProductModels - Exception: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Çoklu kategori desteği ile ürün modelleri getiren metod
     * 
     * @param int $limit Maksimum ürün sayısı
     * @param int $offset Başlangıç indeksi
     * @param array|string|null $category_slugs Kategori filtresi (opsiyonel)
     * @param bool $featured Öne çıkan ürünler filtresi (opsiyonel)
     * @param string|null $sort Sıralama seçeneği (opsiyonel)
     * @return array Ürün modelleri
     */
    public function getProductModelsWithMultiCategory($limit = 10, $offset = 0, $category_slugs = null, $featured = null, $sort = null) {
        try {
            // Ürünleri getirme sorgusu oluştur
            $query = [
                'select' => 'id,name,description,features,base_price,is_featured,created_at',
                'limit' => $limit,
                'offset' => $offset
            ];
            
            // Öne çıkan filtresi
            if ($featured !== null) {
                $query['is_featured'] = 'eq.' . ($featured ? 'true' : 'false');
            }
            
            // Sıralama
            if ($sort) {
                $orderMap = [
                    'price_asc' => 'base_price.asc',
                    'price_desc' => 'base_price.desc',
                    'name_asc' => 'name.asc',
                    'name_desc' => 'name.desc',
                    'newest' => 'created_at.desc'
                ];
                
                $query['order'] = $orderMap[$sort] ?? 'created_at.desc';
            } else {
                $query['order'] = 'created_at.desc';
            }
            
            // Kategori filtresi
            if (!empty($category_slugs)) {
                $category_ids = $this->getCategoryIdsBySlugs($category_slugs);
                
                if (!empty($category_ids)) {
                    $product_ids = $this->getProductIdsByCategories($category_ids);
                    
                    if (!empty($product_ids)) {
                        $query['id'] = 'in.(' . implode(',', $product_ids) . ')';
                    } else {
                        return []; // Eşleşen ürün yoksa boş dizi döndür
                    }
                } else {
                    return []; // Kategori bulunamadıysa boş dizi döndür
                }
            }
            
            $response = $this->supabase->request('product_models?' . http_build_query($query));
            $products = $response['body'] ?? [];
            
            // Her ürün için ek verileri getir
            foreach ($products as &$product) {
                // Kategori bilgilerini getir
                $cat_response = $this->supabase->request('product_categories?select=categories(id,name,slug,parent_id)&product_id=eq.' . $product['id']);
                $cat_relations = $cat_response['body'] ?? [];
                
                // Alt kategori ve ana kategori bilgilerini ayır
                $subcategory = null;
                $main_category = null;
                
                if (!empty($cat_relations)) {
                    // Alt kategori bulmaya çalış (parent_id'si olan)
                    foreach ($cat_relations as $relation) {
                        if (!empty($relation['categories']) && !empty($relation['categories']['parent_id'])) {
                            $subcategory = $relation['categories'];
                            break;
                        }
                    }
                    
                    // Eğer alt kategori bulunamazsa, ana kategori varsa onu kullan
                    if (!$subcategory) {
                        foreach ($cat_relations as $relation) {
                            if (!empty($relation['categories']) && empty($relation['categories']['parent_id'])) {
                                $main_category = $relation['categories'];
                                break;
                            }
                        }
                    }
                    
                    // Kategori bilgilerini ürüne ekle
                    if ($subcategory) {
                        $product['category_name'] = $subcategory['name'];
                        $product['category_slug'] = $subcategory['slug'];
                        $product['subcategory_id'] = $subcategory['id'];
                    }
                    
                    if ($main_category) {
                        $product['main_category_name'] = $main_category['name'];
                        $product['main_category_slug'] = $main_category['slug'];
                        $product['main_category_id'] = $main_category['id'];
                    }
                    
                    // Tüm kategorileri de ekle
                    $product['categories'] = array_map(function($rel) {
                        return $rel['categories'];
                    }, $cat_relations);
                }
                
                // Görsel bilgisi
                $img_query = [
                    'select' => 'image_url',
                    'model_id' => 'eq.' . $product['id'],
                    'is_primary' => 'eq.true',
                    'limit' => 1
                ];
                $img_response = $this->supabase->request('product_images?' . http_build_query($img_query));
                $img_result = $img_response['body'] ?? [];
                
                if (!empty($img_result)) {
                    $product['image_url'] = $img_result[0]['image_url'];
                }
                
                // Fiyat alanını tutarlı tutmak için
                $product['price'] = $product['base_price'];
            }
            
            return $products;
            
        } catch (Exception $e) {
            error_log("Çoklu kategori ürün getirme hatası: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * API için ürünleri getiren, filtreleme, sıralama ve sayfalama destekli metod
     * 
     * @param array $params Filtreleme, sıralama ve sayfalama parametreleri
     * @return array Ürünler ve toplam ürün sayısı
     */
    public function getProductsForApi($params = []) {
        try {
            // Varsayılan parametreler
            $defaults = [
                'page' => 1,
                'limit' => 9,
                'categories' => [],
                'genders' => [],
                'sort' => 'created_at-desc'
            ];
            
            // Parametreleri birleştir
            $params = array_merge($defaults, $params);
            
            // Sayfalama parametrelerini hesapla
            $page = max(1, intval($params['page']));
            $limit = max(1, intval($params['limit']));
            $offset = ($page - 1) * $limit;
            
            // Sorgu oluştur - Tek seferde tüm ilişkili verileri çekecek şekilde
            $select_parts = [
                'id,name,description,base_price,is_featured,created_at',
                'product_categories:product_categories(categories:categories(id,name,slug,parent_id))',
                'product_genders:product_genders(genders:genders(id,name,slug))',
                'product_images:product_images(image_url,is_primary)'
            ];
            
            $query = [
                'select' => implode(',', $select_parts)
            ];
            
            // Filtreleri uygula
            
            // 1. Kategori filtresi
            if (!empty($params['categories'])) {
                $category_ids = $this->getCategoryIdsBySlugs($params['categories']);
                if (!empty($category_ids)) {
                    $product_ids = $this->getProductIdsByCategories($category_ids);
                    if (!empty($product_ids)) {
                        $query['id'] = 'in.(' . implode(',', $product_ids) . ')';
                    } else {
                        // Kategorilere ait ürün bulunamadı
                        return [
                            'products' => [],
                            'total' => 0,
                            'page' => $page,
                            'limit' => $limit,
                            'pages' => 0
                        ];
                    }
                }
            }
            
            // 2. Cinsiyet filtresi
            if (!empty($params['genders'])) {
                // Cinsiyet ID'lerini al
                $gender_slugs = is_array($params['genders']) ? $params['genders'] : [$params['genders']];
                $gender_ids = [];
                
                foreach ($gender_slugs as $gender_slug) {
                    $gender_response = $this->supabase->request("genders?select=id&slug=eq.$gender_slug");
                    if (!empty($gender_response['body'])) {
                        $gender_ids[] = $gender_response['body'][0]['id'];
                    }
                }
                
                if (!empty($gender_ids)) {
                    // Bu cinsiyetlere ait ürün ID'lerini al
                    $gender_filter = 'in.(' . implode(',', $gender_ids) . ')';
                    $gender_products_response = $this->supabase->request("product_genders?select=product_id&gender_id=$gender_filter");
                    
                    if (!empty($gender_products_response['body'])) {
                        $gender_product_ids = array_column($gender_products_response['body'], 'product_id');
                        
                        // Eğer zaten kategori filtresi uygulanmışsa, kesişim al
                        if (isset($query['id'])) {
                            $existing_ids = explode(',', str_replace(['in.(', ')'], '', $query['id']));
                            $gender_product_ids = array_intersect($gender_product_ids, $existing_ids);
                            
                            if (empty($gender_product_ids)) {
                                // Filtrelerle eşleşen ürün yok
                                return [
                                    'products' => [],
                                    'total' => 0,
                                    'page' => $page,
                                    'limit' => $limit,
                                    'pages' => 0
                                ];
                            }
                        }
                        
                        $query['id'] = 'in.(' . implode(',', $gender_product_ids) . ')';
                    } else {
                        // Bu cinsiyetlere ait ürün yok
                        return [
                            'products' => [],
                            'total' => 0,
                            'page' => $page,
                            'limit' => $limit,
                            'pages' => 0
                        ];
                    }
                }
            }
            
            // 3. Sıralama
            if (!empty($params['sort'])) {
                $sort_parts = explode('-', $params['sort']);
                if (count($sort_parts) == 2) {
                    $sort_column = $sort_parts[0];
                    $sort_direction = $sort_parts[1];
                    
                    // Sıralama sütununu ayarla
                    if ($sort_column == 'price') {
                        $sort_column = 'base_price';
                    }
                    
                    // Sıralama sorgusu oluştur
                    $query['order'] = $sort_column . '.' . $sort_direction;
                }
            } else {
                $query['order'] = 'created_at.desc';
            }
            
            // Önce toplam ürün sayısını almak için sorgu yap
            $count_query = $query;
            unset($count_query['select']);
            $count_response = $this->supabase->request('product_models?' . http_build_query($count_query), 'GET', null, ['Prefer' => 'count=exact']);
            
            $total_count = 0;
            if (isset($count_response['headers']['content-range'])) {
                $range = explode('/', $count_response['headers']['content-range']);
                $total_count = isset($range[1]) ? intval($range[1]) : 0;
            } else {
                // Fallback olarak tüm ürünleri say
                $total_count = count($count_response['body'] ?? []);
            }
            
            // Sayfalama parametrelerini ekle
            $query['limit'] = $limit;
            $query['offset'] = $offset;
            
            // Ürünleri getir
            $response = $this->supabase->request('product_models?' . http_build_query($query));
            $products = $response['body'] ?? [];
            
            // Ürün verilerini formatla
            $formatted_products = [];
            foreach ($products as $product) {
                $formatted_product = [
                    'id' => $product['id'],
                    'name' => $product['name'],
                    'description' => $product['description'],
                    'base_price' => $product['base_price'],
                    'price' => $product['base_price'], // Tutarlılık için
                    'is_featured' => $product['is_featured'],
                    'created_at' => $product['created_at']
                ];
                
                // Kategori bilgilerini ekle
                if (!empty($product['product_categories'])) {
                    $main_category = null;
                    $subcategory = null;
                    
                    foreach ($product['product_categories'] as $cat_relation) {
                        if (!empty($cat_relation['categories'])) {
                            $category = $cat_relation['categories'];
                            
                            // Alt kategori bulmaya çalış (parent_id'si olan)
                            if (!empty($category['parent_id'])) {
                                $subcategory = $category;
                            } elseif ($main_category === null) {
                                // Ana kategori
                                $main_category = $category;
                            }
                        }
                    }
                    
                    // Öncelik alt kategoriye, yoksa ana kategoriye
                    if ($subcategory) {
                        $formatted_product['category_name'] = $subcategory['name'];
                        $formatted_product['category_slug'] = $subcategory['slug'];
                    } elseif ($main_category) {
                        $formatted_product['category_name'] = $main_category['name'];
                        $formatted_product['category_slug'] = $main_category['slug'];
                    }
                    
                    // Tüm kategorileri de ekle
                    $formatted_product['categories'] = array_map(function($rel) {
                        return $rel['categories'];
                    }, $product['product_categories']);
                }
                
                // Cinsiyet bilgilerini ekle
                if (!empty($product['product_genders'])) {
                    $formatted_product['genders'] = array_map(function($rel) {
                        return $rel['genders'];
                    }, $product['product_genders']);
                } else {
                    $formatted_product['genders'] = [];
                }
                
                // Görsel bilgisini ekle
                if (!empty($product['product_images'])) {
                    // Önce birincil resmi ara
                    foreach ($product['product_images'] as $image) {
                        if (isset($image['is_primary']) && $image['is_primary'] === true) {
                            $formatted_product['image_url'] = $image['image_url'];
                            break;
                        }
                    }
                    
                    // Eğer birincil resim yoksa, ilk resmi kullan
                    if (!isset($formatted_product['image_url']) && !empty($product['product_images'][0]['image_url'])) {
                        $formatted_product['image_url'] = $product['product_images'][0]['image_url'];
                    }
                }
                
                $formatted_products[] = $formatted_product;
            }
            
            // Toplam sayfa sayısını hesapla
            $total_pages = $limit > 0 ? ceil($total_count / $limit) : 0;
            
            // Sonuçları döndür
            return [
                'products' => $formatted_products,
                'total' => $total_count,
                'page' => $page,
                'limit' => $limit,
                'pages' => $total_pages
            ];
            
        } catch (Exception $e) {
            error_log("API için ürün getirme hatası: " . $e->getMessage());
            return [
                'products' => [],
                'total' => 0,
                'page' => $params['page'] ?? 1,
                'limit' => $params['limit'] ?? 9,
                'pages' => 0
            ];
        }
    }
    
    /**
     * Kategori slug'larından ID'leri getir
     */
    private function getCategoryIdsBySlugs($category_slugs) {
        try {
            $slugs = is_array($category_slugs) ? $category_slugs : [$category_slugs];
            $slug_query = 'in.(' . implode(',', array_map(function($slug) { return '"' . $slug . '"'; }, $slugs)) . ')';
            
            $response = $this->supabase->request('categories?select=id&slug=' . $slug_query);
            $categories = $response['body'] ?? [];
            
            return array_column($categories, 'id');
        } catch (Exception $e) {
            error_log("Kategori ID getirme hatası: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Belirli kategorilere ait ürün ID'lerini getir
     */
    private function getProductIdsByCategories($category_ids) {
        try {
            if (empty($category_ids)) {
                return [];
            }
            
            $ids_query = 'in.(' . implode(',', $category_ids) . ')';
            $response = $this->supabase->request('product_categories?select=product_id&category_id=' . $ids_query);
            $relations = $response['body'] ?? [];
            
            return array_unique(array_column($relations, 'product_id'));
        } catch (Exception $e) {
            error_log("Kategori ürün ID getirme hatası: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Yedek ürün getirme metodu (API hata verdiğinde)
     */
    private function getProductModelsWithFallback($limit, $offset, $category_slugs, $featured, $sort) {
        $basic_query = [
            'select' => '*',
            'limit' => $limit,
            'offset' => $offset
        ];
        
        // Kategori filtresi - çoklu kategori desteği
        if (!empty($category_slugs)) {
            $category_ids = $this->getCategoryIdsBySlugs($category_slugs);
            if (!empty($category_ids)) {
                $product_ids = $this->getProductIdsByCategories($category_ids);
                if (!empty($product_ids)) {
                    $basic_query['id'] = 'in.(' . implode(',', $product_ids) . ')';
                }
            }
        }
        
        // Öne çıkan filtresi
        if ($featured !== null) {
            $basic_query['is_featured'] = 'eq.' . ($featured ? 'true' : 'false');
        }
        
        // Sıralama
        if ($sort) {
            $orderMap = [
                'price_asc' => 'base_price.asc',
                'price_desc' => 'base_price.desc',
                'name_asc' => 'name.asc',
                'name_desc' => 'name.desc',
                'newest' => 'created_at.desc'
            ];
            
            if (isset($orderMap[$sort])) {
                $basic_query['order'] = $orderMap[$sort];
            }
        }
        
        $products_response = $this->supabase->request('product_models?' . http_build_query($basic_query));
        $products = $products_response['body'] ?? [];
        
        // Kategori ve görsel bilgilerini ekle
        foreach ($products as &$product) {
            // Çoklu kategori bilgisini getir
            $cat_response = $this->supabase->request('product_categories?select=category_id,categories(name,slug)&product_id=eq.' . $product['id']);
            $cat_relations = $cat_response['body'] ?? [];
            
            if (!empty($cat_relations)) {
                $product['category_name'] = $cat_relations[0]['categories']['name'] ?? '';
                $product['category_slug'] = $cat_relations[0]['categories']['slug'] ?? '';
            }
            
            // Görsel bilgisi
            $img_query = [
                'select' => 'image_url',
                'model_id' => 'eq.' . $product['id'],
                'is_primary' => 'eq.true',
                'limit' => 1
            ];
            $img_response = $this->supabase->request('product_images?' . http_build_query($img_query));
            $img_result = $img_response['body'] ?? [];
            
            if (!empty($img_result)) {
                $product['image_url'] = $img_result[0]['image_url'];
            }
        }
        
        return $products;
    }
    
    /**
     * Belirli bir ürün modelini ID'ye göre getiren metod
     * 
     * @param int $model_id Ürün model ID'si
     * @return array|null Ürün modeli veya bulunamazsa boş dizi
     */
    public function getProductModel($model_id) {
        try {
            // Çoklu kategori desteği ile ürün getir
            return $this->getProductModelWithMultiCategory($model_id);
        } catch (Exception $e) {
            error_log("Ürün modeli getirme hatası: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Çoklu kategori desteği ile ürün modeli getirme metodu
     */
    private function getProductModelWithMultiCategory($model_id) {
        $query = [
            'select' => 'id,name,description,features,base_price,is_featured,created_at',
            'id' => 'eq.' . $model_id,
            'limit' => 1
        ];
        
        $product_response = $this->supabase->request('product_models?' . http_build_query($query));
        $product_result = $product_response['body'] ?? [];
        
        if (empty($product_result)) {
            return [];
        }
        
        $product = $product_result[0];
        $product['price'] = $product['base_price']; // Tutarlılık için
        
        // Çoklu kategori bilgilerini getir
        $cat_response = $this->supabase->request('product_categories?select=category_id,categories(name,slug)&product_id=eq.' . $model_id);
        $cat_relations = $cat_response['body'] ?? [];
        
        if (!empty($cat_relations)) {
            // İlk kategoriyi ana kategori olarak kullan (backward compatibility)
            $product['category_name'] = $cat_relations[0]['categories']['name'] ?? '';
            $product['category_slug'] = $cat_relations[0]['categories']['slug'] ?? '';
            
            // Tüm kategorileri de ekle
            $product['categories'] = array_map(function($rel) {
                return $rel['categories'];
            }, $cat_relations);
        }
        
        // Ürün görselini al
        $image_query = [
            'select' => 'image_url',
            'model_id' => 'eq.' . $model_id,
            'is_primary' => 'eq.true',
            'limit' => 1
        ];
        
        $image_response = $this->supabase->request('product_images?' . http_build_query($image_query));
        $image_result = $image_response['body'] ?? [];
        
        if (!empty($image_result)) {
            $product['image_url'] = $image_result[0]['image_url'];
        }
        
        return [$product];
    }
    
    /**
     * Bir ürün modeline ait varyantları getiren metod
     * 
     * @param int $model_id Ürün model ID'si
     * @return array Ürün varyantları
     */
    public function getProductVariants($model_id) {
        try {
            $response = $this->supabase->request('product_variants?model_id=eq.' . $model_id);
            return $response['body'] ?? [];
        } catch (Exception $e) {
            error_log("Ürün varyantları getirme hatası: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Bir ürün modeline ait görselleri getiren metod
     * 
     * @param int $model_id Ürün model ID'si
     * @return array Ürün görselleri
     */
    public function getProductImages($model_id) {
        try {
            $response = $this->supabase->request('product_images?model_id=eq.' . $model_id);
            return $response['body'] ?? [];
        } catch (Exception $e) {
            error_log("Ürün görselleri getirme hatası: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Toplam ürün sayısını getiren metod
     *
     * @param array|string|null $category_slugs Kategori filtresi (opsiyonel)
     * @param bool $featured Öne çıkan ürünler filtresi (opsiyonel)
     * @return int Toplam ürün sayısı
     */
    public function getTotalProductCount($category_slugs = null, $featured = null) {
        try {
            $query_parts = [];
            
            // Kategori filtresi - çoklu kategori desteği
            if (!empty($category_slugs)) {
                $category_ids = $this->getCategoryIdsBySlugs($category_slugs);
                if (!empty($category_ids)) {
                    $product_ids = $this->getProductIdsByCategories($category_ids);
                    if (!empty($product_ids)) {
                        $query_parts[] = 'id=in.(' . implode(',', $product_ids) . ')';
                    } else {
                        return 0; // Kategoriye ait ürün yok
                    }
                }
            }
            
            if ($featured !== null) {
                $query_parts[] = 'is_featured=eq.' . ($featured ? 'true' : 'false');
            }
            
            $query_string = empty($query_parts) ? '' : '?' . implode('&', $query_parts);
            $response = $this->supabase->request('product_models' . $query_string);
            
            return count($response['body'] ?? []);
        } catch (Exception $e) {
            error_log("Toplam ürün sayısı getirme hatası: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Admin panel için ürünleri getiren metod - Optimize Edilmiş
     * 
     * @param int $limit Limit
     * @param int $offset Offset
     * @return array Ürünler ve pagination bilgisi
     */
    public function getAdminProductsOptimized($limit = 20, $offset = 0) {
        try {
            // Tek bir sorguda ürünleri, kategorileri, cinsiyetleri ve görselleri çek
            $query_parts = [
                'select=id,name,base_price,is_featured,created_at',
                'product_categories:product_categories(categories:categories(name)),',
                'product_genders:product_genders(genders:genders(id,name,slug)),',
                'product_images:product_images(image_url,is_primary)',
                'order=created_at.desc',
                'limit=' . intval($limit),
                'offset=' . intval($offset)
            ];
            
            $query_string = implode('&', $query_parts);
            $response = $this->supabase->request('product_models?' . $query_string, 'GET', null, ['Prefer' => 'count=exact']);
            $products = $response['body'] ?? [];
            
            // Sonuçları formatla
            foreach ($products as &$product) {
                // Kategori bilgisini ekle
                if (!empty($product['product_categories']) && !empty($product['product_categories'][0]['categories'])) {
                    $product['categories'] = $product['product_categories'][0]['categories'];
                } else {
                    $product['categories'] = ['name' => 'Kategorisiz'];
                }
                
                // Cinsiyet bilgilerini ekle
                $product['genders'] = [];
                if (!empty($product['product_genders'])) {
                    foreach ($product['product_genders'] as $gender_relation) {
                        if (isset($gender_relation['genders'])) {
                            $product['genders'][] = $gender_relation['genders'];
                        }
                    }
                }
                
                // Ana ürün görselini ekle
                $product['image_url'] = null;
                if (!empty($product['product_images'])) {
                    foreach ($product['product_images'] as $image) {
                        if (isset($image['is_primary']) && $image['is_primary'] === true) {
                            $product['image_url'] = $image['image_url'];
                            break;
                        }
                    }
                    
                    // Eğer birincil resim yoksa, ilk resmi kullan
                    if ($product['image_url'] === null && !empty($product['product_images'][0]['image_url'])) {
                        $product['image_url'] = $product['product_images'][0]['image_url'];
                    }
                }
                
                // İlişkili veri dizilerini kaldır (temizlik için)
                unset($product['product_categories']);
                unset($product['product_genders']);
                unset($product['product_images']);
            }
            
            $total_count = 0;
            if (isset($response['headers']['content-range'])) {
                $range = explode('/', $response['headers']['content-range']);
                $total_count = isset($range[1]) ? intval($range[1]) : 0;
            }
            
            return [
                'products' => $products,
                'total' => $total_count,
                'limit' => $limit,
                'offset' => $offset
            ];
            
        } catch (Exception $e) {
            error_log("Admin ürünleri getirme hatası (optimize): " . $e->getMessage());
            return [
                'products' => [],
                'total' => 0,
                'limit' => $limit,
                'offset' => $offset
            ];
        }
    }
    
    /**
     * Admin panel için ürünleri getiren metod
     * 
     * @param int $limit Limit
     * @param int $offset Offset
     * @return array Ürünler ve pagination bilgisi
     */
    public function getAdminProducts($limit = 20, $offset = 0) {
        // Optimize edilmiş metodu çağır
        return $this->getAdminProductsOptimized($limit, $offset);
    }
    
    /**
     * Ürün silme metodu - Cascade delete ile bağlantılı verileri de siler
     * 
     * @param int $product_id Ürün ID
     * @return bool Başarı durumu
     */
    public function deleteProduct($product_id) {
        try {
            $product_id = intval($product_id);
            
            if ($product_id <= 0) {
                throw new Exception("Geçersiz ürün ID: $product_id");
            }
            
            // 1. Önce ürün kategorilerini sil (çoklu kategori sistemi)
            $categories_response = $this->supabase->request(
                'product_categories?product_id=eq.' . $product_id, 
                'DELETE'
            );
            
            // 2. Ürün görsellerini sil
            $images_response = $this->supabase->request(
                'product_images?model_id=eq.' . $product_id, 
                'DELETE'
            );
            
            // 3. Ürün varyantlarını sil
            $variants_response = $this->supabase->request(
                'product_variants?model_id=eq.' . $product_id, 
                'DELETE'
            );
            
            // 4. En son ana ürün modelini sil
            $model_response = $this->supabase->request(
                'product_models?id=eq.' . $product_id, 
                'DELETE'
            );
            
            // En azından ana ürün silme işlemi başarılı olmalı
            return !empty($model_response);
            
        } catch (Exception $e) {
            error_log("Ürün silme hatası (ID: $product_id): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Ürün durumu güncelleme metodu
     * 
     * @param int $product_id Ürün ID
     * @param bool $is_featured Öne çıkan durumu
     * @return bool Başarı durumu
     */
    public function updateProductStatus($product_id, $is_featured) {
        try {
            $data = ['is_featured' => $is_featured];
            $response = $this->supabase->request('product_models?id=eq.' . intval($product_id), 'PATCH', $data);
            return !empty($response);
        } catch (Exception $e) {
            error_log("Ürün durumu güncelleme hatası: " . $e->getMessage());
            return false;
        }
    }
}

// ProductService sınıfı singleton örneği
function product_service() {
    static $instance = null;
    
    if ($instance === null) {
        $instance = new ProductService();
    }
    
    return $instance;
}

// Geriye uyumluluk için fonksiyonlar
/**
 * Ürün modellerini getiren fonksiyon
 * 
 * @param int $limit Maksimum ürün sayısı
 * @param int $offset Başlangıç indeksi
 * @param array|string|null $category_slugs Kategori filtresi (opsiyonel)
 * @param bool $featured Öne çıkan ürünler filtresi (opsiyonel)
 * @param string|null $sort Sıralama seçeneği (opsiyonel)
 * @return array Ürün modelleri
 */
function get_product_models($limit = 10, $offset = 0, $category_slugs = null, $featured = null, $sort = null) {
    return product_service()->getProductModelsWithMultiCategory($limit, $offset, $category_slugs, $featured, $sort);
}

/**
 * Belirli bir ürün modelini ID'ye göre getiren fonksiyon
 * 
 * @param int $model_id Ürün model ID'si
 * @return array|null Ürün modeli veya bulunamazsa null
 */
function get_product_model($model_id) {
    return product_service()->getProductModel($model_id);
}

/**
 * Bir ürün modeline ait varyantları getiren fonksiyon
 * 
 * @param int $model_id Ürün model ID'si
 * @return array Ürün varyantları
 */
function get_product_variants($model_id) {
    return product_service()->getProductVariants($model_id);
}

/**
 * Bir ürün modeline ait görselleri getiren fonksiyon
 * 
 * @param int $model_id Ürün model ID'si
 * @return array Ürün görselleri
 */
function get_product_images($model_id) {
    return product_service()->getProductImages($model_id);
}

/**
 * Toplam ürün sayısını getiren fonksiyon
 *
 * @param array|string|null $category_slugs Kategori filtresi (opsiyonel)
 * @param bool $featured Öne çıkan ürünler filtresi (opsiyonel)
 * @return int Toplam ürün sayısı
 */
function get_total_product_count($category_slugs = null, $featured = null) {
    return product_service()->getTotalProductCount($category_slugs, $featured);
}

/**
 * Admin için tüm ürünleri getiren fonksiyon
 * 
 * @param int $limit Limit
 * @param int $offset Offset
 * @param string $search Arama terimi
 * @param string $category_filter Kategori filtresi
 * @param string $status_filter Durum filtresi
 * @return array Ürünler ve pagination bilgisi
 */
function get_admin_products($limit = 20, $offset = 0) {
    return product_service()->getAdminProducts($limit, $offset);
}

/**
 * Ürün silme fonksiyonu
 * 
 * @param int $product_id Ürün ID
 * @return bool Başarı durumu
 */
function delete_product($product_id) {
    return product_service()->deleteProduct($product_id);
}

/**
 * Ürün durumu güncelleme fonksiyonu
 * 
 * @param int $product_id Ürün ID
 * @param bool $is_featured Öne çıkan durumu
 * @return bool Başarı durumu
 */
function update_product_status($product_id, $is_featured) {
    return product_service()->updateProductStatus($product_id, $is_featured);
}

/**
 * API için ürünleri filtreleme, sıralama ve sayfalama ile getiren fonksiyon
 * 
 * @param array $params Filtreleme, sıralama ve sayfalama parametreleri
 * @return array Ürünler ve toplam sayfa bilgisi
 */
function get_products_for_api($params = []) {
    return product_service()->getProductsForApi($params);
}
