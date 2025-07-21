<?php 
session_start(); // Démarrage de la session utilisateur

require_once 'includes/config.php'; // Connexion à la base de données via PDO

// --- Gestion de la connexion utilisateur ---
$erreur_connexion = '';

// Si un formulaire de connexion est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $nom_utilisateur = trim($_POST['nom_utilisateur'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';

    // Vérifie que les champs ne sont pas vides
    if (!empty($nom_utilisateur) && !empty($mot_de_passe)) {
        // Recherche de l'utilisateur dans la base
        $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE nom_utilisateur = ?");
        $stmt->execute([$nom_utilisateur]);
        $utilisateur = $stmt->fetch();

        // Vérification du mot de passe
        if ($utilisateur && password_verify($mot_de_passe, $utilisateur['mot_de_passe'])) {
            // Stocke les infos de l'utilisateur dans la session
            $_SESSION['utilisateur_id'] = $utilisateur['id'];
            $_SESSION['nom_utilisateur'] = $utilisateur['nom_utilisateur'];
            $_SESSION['email'] = $utilisateur['email'];
            $_SESSION['role'] = $utilisateur['role'];
            $_SESSION['nom_complet'] = trim(($utilisateur['prenom'] ?? '') . ' ' . ($utilisateur['nom'] ?? ''));

            // Redirection vers la même page après connexion
            header('Location: index.php');
            exit;
        } else {
            $erreur_connexion = "Nom d'utilisateur ou mot de passe incorrect.";
        }
    } else {
        $erreur_connexion = "Veuillez remplir tous les champs.";
    }
}

// --- Pagination des livres ---
$livres_par_page = 5; // Nombre de livres par page
$page_actuelle = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1; // Page actuelle
$debut = ($page_actuelle - 1) * $livres_par_page; // Index de début pour SQL

// Nombre total de livres disponibles
$total_livres = $pdo->query("SELECT COUNT(*) FROM livres WHERE nombre_exemplaires_disponibles > 0")->fetchColumn();
$total_pages = ceil($total_livres / $livres_par_page); // Calcule le nombre total de pages

// Requête pour récupérer les livres avec leurs auteurs et catégories
$stmt = $pdo->prepare("
    SELECT l.titre, l.nombre_exemplaires_disponibles, a.nom AS auteur_nom, a.prenom AS auteur_prenom, c.nom_categorie
    FROM livres l
    LEFT JOIN auteurs a ON l.auteur_id = a.id
    LEFT JOIN categories c ON l.categorie_id = c.id
    WHERE l.nombre_exemplaires_disponibles > 0
    ORDER BY l.titre ASC
    LIMIT :debut, :limite
");
$stmt->bindValue(':debut', $debut, PDO::PARAM_INT);
$stmt->bindValue(':limite', $livres_par_page, PDO::PARAM_INT);
$stmt->execute();
$livres = $stmt->fetchAll(); // Résultat : tableau de livres
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bibliothèque de Vacances</title>
    <script src="https://cdn.tailwindcss.com"></script> <!-- Tailwind CSS -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body class="bg-gradient-to-br from-blue-100 to-blue-200 min-h-screen">

<!-- En-tête avec menu -->
<header class="bg-blue-600 text-white px-6 py-4 flex justify-between items-center flex-wrap">
    <h1 class="text-xl font-bold">📚 Ma Bibliothèque de Vacances</h1>
    <nav class="space-x-4">
        <?php if (isset($_SESSION['utilisateur_id'])): ?>
            <span class="font-medium">Bienvenue <?= htmlspecialchars($_SESSION['nom_complet'] ?? $_SESSION['nom_utilisateur']) ?> !</span>
            <a href="dashboard.php" class="hover:underline">Tableau de Bord</a>
            <a href="logout.php" class="hover:underline">Déconnexion</a>
        <?php else: ?>
            <a href="login.php" class="hover:underline">Connexion</a>
            <a href="register.php" class="hover:underline">Inscription</a>
        <?php endif; ?>
    </nav>
</header>

<!-- Affiche un message d'erreur si la connexion a échoué -->
<?php if (!empty($erreur_connexion)): ?>
    <div class="text-center text-red-600 font-semibold mt-4"><?= htmlspecialchars($erreur_connexion) ?></div>
<?php endif; ?>

<!-- Liste des livres disponibles -->
<main class="max-w-5xl mx-auto bg-white mt-10 p-8 rounded-lg shadow-lg">
    <!-- Champ de recherche -->
    <div class="mb-6 flex flex-col sm:flex-row gap-4 items-center justify-center">
        <input type="text" id="searchInput" placeholder="🔍 Rechercher un livre ou un auteur..."
               class="w-full sm:w-2/3 px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
    </div>

    <h2 class="text-2xl font-bold mb-4">📖 Livres disponibles</h2>

    <?php if (empty($livres)): ?>
        <p class="text-center text-gray-600">Aucun livre trouvé.</p>
    <?php else: ?>
        <!-- Tableau des livres -->
        <div class="overflow-x-auto">
            <table id="livresTable" class="w-full table-auto border border-gray-200">
                <thead class="bg-blue-100">
                <tr>
                    <th class="px-4 py-3 text-left">Titre</th>
                    <th class="px-4 py-3 text-left">Auteur</th>
                    <th class="px-4 py-3 text-left">Catégorie</th>
                    <th class="px-4 py-3 text-left">Exemplaires</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($livres as $livre): ?>
                    <tr class="border-t">
                        <td class="px-4 py-3"><?= htmlspecialchars($livre['titre']) ?></td>
                        <td class="px-4 py-3"><?= htmlspecialchars($livre['auteur_prenom'] . ' ' . $livre['auteur_nom']) ?></td>
                        <td class="px-4 py-3"><?= htmlspecialchars($livre['nom_categorie']) ?></td>
                        <td class="px-4 py-3"><?= intval($livre['nombre_exemplaires_disponibles']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="flex justify-center mt-6 space-x-2">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?= $i ?>"
                       class="px-4 py-2 rounded-md text-white <?= $i === $page_actuelle ? 'bg-blue-700' : 'bg-blue-500 hover:bg-blue-600' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</main>

<!-- Script de recherche en temps réel (client-side) -->
<script>
document.getElementById('searchInput').addEventListener('keyup', function () {
    const filter = this.value.toLowerCase(); // Texte tapé
    const rows = document.querySelectorAll('#livresTable tbody tr'); // Toutes les lignes du tableau

    // Affiche ou cache les lignes selon le filtre
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});
</script>

</body>
</html>
