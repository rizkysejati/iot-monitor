<?php
// auth/logout.php
session_start();
$_SESSION = [];       // kosongkan data session
session_destroy();    // hancurkan session
header('Location: login.php');
exit;
