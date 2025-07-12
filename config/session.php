<?php


class SessionConfig
{


    public static function init()
    {

        if (session_status() !== PHP_SESSION_NONE) {
            return;
        }


        ini_set('session.use_strict_mode', 1);
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 1 : 0);
        ini_set('session.cookie_samesite', 'Strict');
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_lifetime', 0);


        ini_set('session.gc_maxlifetime', 7200);
        ini_set('session.gc_probability', 1);
        ini_set('session.gc_divisor', 100);


        session_name('BANDLAND_SESSID');


        session_start();


        self::validateSession();
    }


    private static function validateSession()
    {

        if (!isset($_SESSION['user_ip'])) {
            $_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'] ?? '';
        } elseif ($_SESSION['user_ip'] !== ($_SERVER['REMOTE_ADDR'] ?? '')) {

            self::destroySession();
            return;
        }


        if (!isset($_SESSION['user_agent'])) {
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
        } elseif ($_SESSION['user_agent'] !== ($_SERVER['HTTP_USER_AGENT'] ?? '')) {

            self::destroySession();
            return;
        }


        if (!isset($_SESSION['last_regeneration'])) {
            $_SESSION['last_regeneration'] = time();
        } elseif (time() - $_SESSION['last_regeneration'] > 1800) {
            self::regenerateSession();
        }
    }


    public static function regenerateSession()
    {

        $oldSessionData = $_SESSION;


        if (session_regenerate_id(true)) {

            $_SESSION = $oldSessionData;
            $_SESSION['last_regeneration'] = time();


            error_log("Session regenerated for user: " . ($_SESSION['user_id'] ?? 'anonymous'));

            return true;
        }

        return false;
    }


    public static function destroySession($isUserLogout = false)
    {

        $_SESSION = [];


        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }


        session_destroy();


        if ($isUserLogout) {
            error_log("Session destroyed by user logout request");
        } else {
            error_log("Session destroyed due to security validation failure");
        }
    }


    public static function checkTimeout($timeout = 1800)
    {

        if (session_status() !== PHP_SESSION_ACTIVE) {
            return true;
        }


        if (!isset($_SESSION['user_last_activity'])) {
            $_SESSION['user_last_activity'] = time();
            return true;
        }


        $inactiveTime = time() - $_SESSION['user_last_activity'];


        if ($inactiveTime > $timeout) {
            self::destroySession();
            return false;
        }


        if ($inactiveTime > 300) {
            $_SESSION['user_last_activity'] = time();
        }

        return true;
    }


    public static function checkConcurrentSession($userId, $db)
    {
        if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
            return true;
        }


        if (isset($_SESSION['last_concurrent_check']) && (time() - $_SESSION['last_concurrent_check'] < 300)) {
            return true;
        }


        $currentSessionId = session_id();
        $userSessions = $db->select('user_sessions', ['user_id' => $userId], '*');


        $_SESSION['last_concurrent_check'] = time();

        foreach ($userSessions as $session) {
            if ($session['session_id'] !== $currentSessionId) {

                self::destroySession();
                return false;
            }
        }

        return true;
    }


    public static function saveUserSession($userId, $db)
    {
        $sessionId = session_id();
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $loginTime = date('Y-m-d H:i:s');


        $db->delete('user_sessions', ['user_id' => $userId]);


        $db->insert('user_sessions', [
            'user_id' => $userId,
            'session_id' => $sessionId,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'login_time' => $loginTime,
            'last_activity' => $loginTime
        ]);
    }


    public static function updateSessionActivity($userId, $db)
    {

        if (isset($_SESSION['last_activity_update']) && (time() - $_SESSION['last_activity_update'] < 300)) {
            return;
        }

        $sessionId = session_id();
        $db->update(
            'user_sessions',
            ['last_activity' => date('Y-m-d H:i:s')],
            ['user_id' => $userId, 'session_id' => $sessionId]
        );


        $_SESSION['last_activity_update'] = time();
    }


    public static function clearUserSession($userId, $db)
    {
        $db->delete('user_sessions', ['user_id' => $userId]);
    }


    public static function getDebugInfo()
    {
        return [
            'session_id' => session_id(),
            'session_name' => session_name(),
            'session_status' => session_status(),
            'session_timeout' => ini_get('session.gc_maxlifetime'),
            'cookie_secure' => ini_get('session.cookie_secure'),
            'cookie_httponly' => ini_get('session.cookie_httponly'),
            'use_strict_mode' => ini_get('session.use_strict_mode'),
            'last_activity' => $_SESSION['user_last_activity'] ?? null,
            'last_regeneration' => $_SESSION['last_regeneration'] ?? null,
            'user_ip' => $_SESSION['user_ip'] ?? null,
            'user_agent_hash' => isset($_SESSION['user_agent']) ? md5($_SESSION['user_agent']) : null
        ];
    }
}


SessionConfig::init();