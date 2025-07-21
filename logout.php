<?php
session_start();

// Détruire toutes les variables de session
$_SESSION = [];

// Si tu utilises des cookies pour la session, les supprimer aussi
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Détruire la session
session_destroy();

// Redirection vers index.php
header("Location: index.php");
exit;
