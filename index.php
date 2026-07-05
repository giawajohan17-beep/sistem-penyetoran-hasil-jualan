<?php
session_start();
session_destroy();

// Ubah index.php menjadi login.php karena file index tidak ada di folder Anda
header("Location: login.php"); 
exit();
?>