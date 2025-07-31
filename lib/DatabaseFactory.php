<?php


require_once __DIR__ . '/DatabaseInterface.php';
require_once __DIR__ . '/SupabaseClient.php';
require_once __DIR__ . '/../config/env.php';

class DatabaseFactory
{

    public static function create($type = null, $config = [])
    {
        // No database connection will be created.
        return null;
    }

    public static function getCurrentType()
    {
        return null;
    }


    public static function getSupportedTypes()
    {
        return [];
    }
}


function database()
{
    // No database connection will be created.
    return null;
}


function supabase()
{
    return database();
}
