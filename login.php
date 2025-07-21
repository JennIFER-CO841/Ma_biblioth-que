<?php
// Démarre la session pour pouvoir stocker les informations de l'utilisateur
session_start();

// Inclusion du fichier de configuration (connexion à la base de données)
require_once 'includes/config.php';

// Tableau pour stocker les erreurs éventuelles
$erreurs = [];

// Si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Récupération des champs email et mot de passe avec protection minimale
    $email = trim($_POST['email'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';

    // Vérification de l’email
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreurs[] = "Une adresse email valide est requise.";
    }

    // Vérification du mot de passe
    if (empty($mot_de_passe)) {
        $erreurs[] = "Le mot de passe est requis.";
    }

    // Si aucune erreur de validation, on tente la connexion
    if (empty($erreurs)) {
        try {
            // Prépare la requête pour rechercher l'utilisateur avec cet email
            $stmt = $pdo->prepare("SELECT id, nom_utilisateur, mot_de_passe, role FROM utilisateurs WHERE email = :email");
            $stmt->execute(['email' => $email]);
            $utilisateur = $stmt->fetch(); // Récupère l’utilisateur s’il existe

            // Si l’utilisateur est trouvé et le mot de passe est correct
            if ($utilisateur && password_verify($mot_de_passe, $utilisateur['mot_de_passe'])) {
                // Stocke les infos utilisateur dans la session
                $_SESSION['utilisateur_id'] = $utilisateur['id'];
                $_SESSION['nom_utilisateur'] = $utilisateur['nom_utilisateur'];
                $_SESSION['role'] = $utilisateur['role'];

                // Redirige vers la page d’accueil
                header("Location: index.php");
                exit;
            } else {
                // Mauvais identifiants
                $erreurs[] = "Identifiants invalides. Vérifiez votre email et votre mot de passe.";
            }
        } catch (PDOException $e) {
            // En cas d’erreur avec la base de données
            $erreurs[] = "Erreur lors de la connexion : " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Utilisation de Tailwind CSS pour le design -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-r from-blue-100 to-purple-100 min-h-screen flex items-center justify-center px-4">

    <div class="bg-white shadow-md rounded-lg w-full max-w-md p-8">
        <h2 class="text-2xl font-bold text-center text-blue-700 mb-6">Se connecter</h2>

        <!-- Affichage des erreurs, s’il y en a -->
        <?php if (!empty($erreurs)) : ?>
            <?php foreach ($erreurs as $erreur) : ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-2">
                    ❌ <?= htmlspecialchars($erreur) ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- Formulaire de connexion -->
        <form method="POST" action="">
            <label for="email" class="block mb-2 font-medium text-gray-700">Adresse Email</label>
            <input type="email" name="email" id="email" required
                   class="w-full px-4 py-2 border rounded-lg mb-4 focus:outline-none focus:ring-2 focus:ring-blue-400">

            <label for="mot_de_passe" class="block mb-2 font-medium text-gray-700">Mot de passe</label>
            <input type="password" name="mot_de_passe" id="mot_de_passe" required
                   class="w-full px-4 py-2 border rounded-lg mb-6 focus:outline-none focus:ring-2 focus:ring-blue-400">

            <button type="submit"
                    class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-200">
                Se connecter
            </button>
        </form>

        <!-- Lien vers la page d’inscription -->
        <p class="text-center text-sm mt-6">
            Pas encore de compte ?
            <a href="register.php" class="text-blue-600 font-medium hover:underline">Créez-en un ici</a>
        </p>
    </div>

</body>
</html>
