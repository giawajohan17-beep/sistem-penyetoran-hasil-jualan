<?php
session_start();
session_destroy();
header("Location: index.php"); // Pastikan index.php adalah file login kamu
exit();
?>