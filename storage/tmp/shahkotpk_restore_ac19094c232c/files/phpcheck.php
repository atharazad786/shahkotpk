<?php

echo 'PHP Version: ' . PHP_VERSION . '<br>';
echo 'PDO: ' . (extension_loaded('pdo') ? 'YES' : 'NO') . '<br>';
echo 'PDO MySQL: ' . (extension_loaded('pdo_mysql') ? 'YES' : 'NO') . '<br>';
echo 'MySQLi: ' . (extension_loaded('mysqli') ? 'YES' : 'NO') . '<br>';
echo 'mbstring: ' . (extension_loaded('mbstring') ? 'YES' : 'NO') . '<br>';