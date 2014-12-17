<?php
if (is_file($sREQFile = dirname(__DIR__) . '/public' . $_SERVER['REQUEST_URI']) && file_exists($sREQFile)) {
    return false;
} else {
    require dirname(__DIR__) . '/public/page_dev.php';
}
