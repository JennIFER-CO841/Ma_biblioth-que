# Ma petite bibliotheque personnelle en ligne

Singh Jennifer 

Framework CSS utilisé : TailwindCSS

Description du projet :
    Ce projet est une application web de gestion de bibliothèque.
    Il permet aux utilisateurs de consulter, ajouter, emprunter et gérer les livres selon leur rôles('lecteur' ou 'admin').
Technologies utilisées:
    Backend : PHP, PDO, MySQL
    Frontend : HTML5, Tailwind CSS et un peu du Javascript
    Base de données : MySQL



Instructions pour installer l'application

1. Liens vers mon github : https://github.com/JennIFER-CO841/Ma_biblioth-que.git
2. Après Télécharger ce projet en cliquant sur "Code" puis "Download ZIP".
3. Extraire ce dossier ZIP dans le sous dossier dédié de votre serveur PHP, par exemple le sous dossier "www" du serveur WAMP, ou le sous dossier "htdocs" de serveur XAMPP.
4.  Configurer la base de données :        
- Dans votre logiciel de base de données (par exemple MySQL), importer le fichier ma_bibliotheque_vacances.sql fourni.
5. Configurer la connexion dans includes/config.php si nécessaire :
    $host = 'localhost';
    $dbname = 'ma_bibliotheque_vacances';
    $user = 'root';
    $pass = '';
6.Lancer le serveur (par exemple XAMPP, MAMP ou WAMP).
7. Visiter la page d'acceuil http://localhost/ma_biblioth-que-main/index.php dans le navigateur afin d'utiliser cette application.



Identifiants de connexion

    Comptes par défaut pour se connecter :
    Admin : 
        Email : elisa@hotmail.com
        Mot de passe : elisaa

    Lecteur : 
        Email : alia@hotmail.com
        Mot de passe : aliaaa



Fonctionnalités 

Côté utilisateurs (lecteur):
- S'inscrire et se connecter,
- Peut voir la liste des livres disponibles,
- Rechercher un livre par titre, auteur ou catégorie,
- Consulter les détails d’un livre,
- Emprunter un livre (si disponible).

Côté administrateur
- S'inscrire et se connecter,
- Peut voir la liste des livres disponibles,
- Rechercher un livre par titre, auteur ou catégorie,
- Gérer les livres (ajout, modification, suppression),
- Gérer les auteurs et catégories(ajout, modification, suppression),
- Gérer les utilisateurs (modification et suppression),
- Suivre et gérer tous les emprunts (retours inclus).





Difficultés rencontrées et solutions

1. Gestion des rôles et sécurisation des pages
Problème : Certaines pages étaient accessibles sans vérification stricte du rôle utilisateur.
Solution : Vérification systématique des sessions et des rôles (admin ou lecteur) en haut de chaque fichier PHP critique.

2. Design
Problème : L’interface était difficile à utiliser et manquait de clarté.
Solution : Adoption de Tailwind CSS et ajout d’éléments visuels simples (boutons colorés, icônes, messages de confirmation).

3. Gestion des messages d’alerte
Problème : Les messages de succès ou d'erreur restaient affichés indéfiniment après une action (modification, suppression, etc.).
Solution : Ajout d’un petit script JavaScript pour faire disparaître automatiquement ces messages après 30 secondes.

4. Gestion des auteurs et des livres liés
Problème : Impossible de supprimer un auteur s’il avait encore des livres associés.
Solution : Réaffectation automatique des livres à un auteur par défaut nommé « Inconnu » avant la suppression.

5.Cohérence des stocks de livres
Problème : Le nombre d’exemplaires disponibles ne se mettait pas toujours à jour correctement lors des emprunts ou des retours.
Solution : Utilisation de transactions SQL et mises à jour automatiques (nombre_exemplaires_disponibles ± 1) pour garantir la cohérence des données.

6.Organisation du code et gestion des erreurs PDO
Problème : Difficile de repérer certaines erreurs SQL pendant le développement.
Solution : Activation du mode PDO::ERRMODE_EXCEPTION pour afficher des messages clairs en cas d’erreur.

7. Navigation et expérience utilisateur
Problème : Les utilisateurs avaient du mal à revenir au tableau de bord après certaines actions.
Solution : Ajout de boutons « Retour » visibles sur chaque page de gestion.

8. Problème d’accès non autorisé
Problème : Certaines pages pouvaient être ouvertes sans connexion.
Solution : Ajout de vérifications session_start() et rôle utilisateur sur chaque page.
