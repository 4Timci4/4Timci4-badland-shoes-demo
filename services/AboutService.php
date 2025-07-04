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
        $data = $this->client->request('about_settings?select=meta_key,meta_value');
        
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
    }

    private function getSettings() {
        $data = $this->client->request('about_settings?select=*');
        $settings = [];
        foreach ($data as $item) {
            if (isset($item['meta_key'], $item['meta_value'])) {
                $settings[$item['meta_key']] = $item['meta_value'];
            }
        }
        return $settings;
    }

    private function getContentBlocks($section) {
        $query = [
            'select' => '*',
            'section' => 'eq.' . $section,
            'order' => 'sort_order.asc'
        ];
        return $this->client->request('about_content_blocks?' . http_build_query($query));
    }
}
