<?php

/*
 * Manages database connection
 */
class Database {
  protected $pdo;

  /*
   * Constructor
   */
  public function __construct() {
    $db = $this->get_credentials();
    $this->pdo = new PDO($db['adapter'] . ':host=' . $db['host'] . ';dbname=' . $db['name'], $db['user'], $db['pass']);
    $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    //$this->pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
  }

  /*
   * Returns database connection credentials using config file or environment variables
   * returns db_config array
   */
  protected function get_credentials() {
    // check if config file is present, then use config file
    $config_file = dirname(__FILE__) . '/../../config.php';
    if (is_readable($config_file)) {
      require $config_file;

      $db_config = Array(
        'adapter' => 'mysql',
        'host' => $config['db']['host'],
        'name' => $config['db']['dbname'],
        'user' => $config['db']['user'],
        'pass' => $config['db']['pass'],
        'port' => $config['db']['port'],
      );
    }

    // check if deployed to heroku
    if (getenv('DATABASE_URL') !== false) {
      $herokudb = parse_url(getenv('DATABASE_URL'));
      $db_config = Array(
        'adapter' => 'pgsql',
        'host' => $herokudb['host'],
        'name' => ltrim($herokudb['path'], '/'),
        'user' => $herokudb['user'],
        'pass' => $herokudb['pass'],
        'port' => $herokudb['port'],
      );
    }
    return $db_config;
  }

  /*
   * Returns pdo connection
   * returns pdo object
   */
  public function get_connection() {
    return $this->pdo;
  }

  /*
   * Returns db name
   */
  public function get_db_name() {
    return $this->pdo->query('SELECT DATABASE()')->fetchColumn(0);
  }
}
