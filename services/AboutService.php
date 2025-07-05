<?php

require_once __DIR__ . '/../lib/SupabaseClient.php';

class AboutService {
    private $client;

    public function __construct() {
        $this->client = supabase();
    }

    public function getAboutPageContent() {
        $settings = $this->getSettings();
        $values = $this->getContentBlocks('values');
        $team = $this->getContentBlocks('team');

        return [
            'settings' => $settings,
            'values' => $values,
            'team' => $team,
        ];
    }

    public function getHomePageAboutSection() {
        try {
            $response = $this->client->request('about_settings?select=meta_key,meta_value');
            $data = $response['body'] ?? [];

            $result = [];
            $allowed_keys = [
                'story_content_title',
                'story_content_p1',
                'story_content_p2',
                'story_image_url',
                'story_content_homepage'
            ];

            foreach ($data as $item) {
                if (isset($item['meta_key'], $item['meta_value']) && in_array($item['meta_key'], $allowed_keys)) {
                    $result[$item['meta_key']] = $item['meta_value'];
                }
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("Anasayfa Hakkımızda bölümü getirme hatası: " . $e->getMessage());
            return [];
        }
    }

    private function getSettings() {
        try {
            $response = $this->client->request('about_settings?select=*');
            $data = $response['body'] ?? [];
            $settings = [];
            foreach ($data as $item) {
                if (isset($item['meta_key'], $item['meta_value'])) {
                    $settings[$item['meta_key']] = $item['meta_value'];
                }
            }
            return $settings;
        } catch (Exception $e) {
            error_log("Hakkımızda ayarları getirme hatası: " . $e->getMessage());
            return [];
        }
    }

    private function getContentBlocks($section) {
        try {
            $query = 'about_content_blocks?select=*&section=eq.' . urlencode($section) . '&order=sort_order.asc';
            $response = $this->client->request($query);
            return $response['body'] ?? [];
        } catch (Exception $e) {
            error_log("Hakkımızda içerik blokları getirme hatası: " . $e->getMessage());
            return [];
        }
    }
}
