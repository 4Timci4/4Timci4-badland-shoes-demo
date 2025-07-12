<?php



require_once __DIR__ . '/env.php';


require_once __DIR__ . '/../lib/DatabaseFactory.php';


require_once __DIR__ . '/../services/ProductService.php';


require_once __DIR__ . '/../services/CategoryService.php';


require_once __DIR__ . '/../services/BlogService.php';


require_once __DIR__ . '/../services/ContactService.php';


function get_colors()
{
    try {

        return database()->select('colors', [], ['id', 'name', 'hex_code', 'created_at']);
    } catch (Exception $e) {
        error_log("Renkleri getirme hatası: " . $e->getMessage());
        return [];
    }
}


function get_sizes()
{
    try {

        return database()->select('sizes', [], ['id', 'size_value', 'size_type', 'created_at']);
    } catch (Exception $e) {
        error_log("Bedenleri getirme hatası: " . $e->getMessage());
        return [];
    }
}
