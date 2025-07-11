<?php

require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/DatabaseFactory.php';

class DatabaseSessionHandler implements SessionHandlerInterface
{
    private $db;
    private $table = 'sessions';
    private $lifetime = 21600; // 6 saat

    public function __construct()
    {
        $this->db = database();
    }

    public function open($path, $name): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read($id)
    {
        try {
            $current_time = gmdate('Y-m-d\TH:i:s\Z');
            $result = $this->db->select(
                'sessions',
                ['id' => ['=', $id], 'expires_at' => ['>', $current_time]],
                'data',
                ['limit' => 1]
            );

            if ($result && !empty($result)) {
                return $result[0]['data'] ?? '';
            }

            return '';
        } catch (Exception $e) {
            error_log("Session read error for ID $id: " . $e->getMessage());
            return '';
        }
    }

    public function write($id, $data): bool
    {
        try {
            $user_id = null;
            if (strpos($data, 'user_session') !== false) {
                if (preg_match('/user_session.*?user.*?id.*?s:\d+:"([^"]+)"/', $data, $matches)) {
                    $user_id = $matches[1];
                }
            }

            if ($user_id) {
                $expires_at = gmdate('Y-m-d\TH:i:s\Z', time() + $this->lifetime);
                $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
                $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;

                $session_data = [
                    'id' => $id,
                    'data' => $data,
                    'expires_at' => $expires_at,
                    'user_id' => $user_id,
                    'ip_address' => $ip_address,
                    'user_agent' => $user_agent,
                    'updated_at' => gmdate('Y-m-d\TH:i:s\Z'),
                ];

                $this->db->insert('sessions', $session_data, ['on_conflict' => 'id']);
            } else {
                // If there is no user_id, it's a guest.
                // We destroy any session that might exist in the DB with this ID
                // to handle logout cases correctly.
                $this->destroy($id);
            }

            return true;
        } catch (Exception $e) {
            error_log("Session write error for ID $id: " . $e->getMessage());
            return false;
        }
    }

    public function destroy($id): bool
    {
        try {
            $this->db->delete('sessions', ['id' => $id]);
            error_log("Session destroyed: " . $id);
            return true;
        } catch (Exception $e) {
            error_log("Session destroy error: " . $e->getMessage());
            return false;
        }
    }

    public function gc($max_lifetime)
    {
        try {
            // Expired session'ları sil
            $current_time = gmdate('Y-m-d\TH:i:s\Z');
            $this->db->delete('sessions', [
                'expires_at' => ['lt', $current_time]
            ]);
            error_log("Session garbage collection completed");
            return 0;
        } catch (Exception $e) {
            error_log("Session GC error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Clean expired sessions manually
     */
    public function cleanExpiredSessions(): bool
    {
        try {
            $current_time = gmdate('Y-m-d\TH:i:s\Z');
            $this->db->delete('sessions', [
                'expires_at' => ['lt', $current_time]
            ]);
            return true;
        } catch (Exception $e) {
            error_log("Session cleanup error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get active sessions for a user
     */
    public function getUserSessions($userId): array
    {
        try {
            $current_time = gmdate('Y-m-d\TH:i:s\Z');
            $result = $this->db->select('sessions', [
                'user_id' => $userId,
                'expires_at' => ['gt', $current_time]
            ], 'id,created_at,updated_at,expires_at,ip_address,user_agent');
            
            return $result ?? [];
        } catch (Exception $e) {
            error_log("Get user sessions error: " . $e->getMessage());
            return [];
        }
    }
}