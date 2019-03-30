<?php
require_once 'src/classes/Database.php';
$db = new Database;

return [
    "paths" => [
        "migrations" => "database/migrations"
    ],
    "environments" => [
        "default_migration_table" => "phinxlog",
        "default_database" => "dev",
        "dev" => [
          "name" => $db->get_db_name(),
          "connection" => $db->get_connection()
        ]
    ]
];
