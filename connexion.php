<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/bootstrap.css">
    <title>Connexion</title>
</head>
<body>
    <header>
        <nav>
            <?php require_once "navbar.php" ?>
        </nav>
    </header>
    <?php
    // 1. Démarrer la session pour enregistrer l'utilisateur
    session_start();
    
    // Inclure la connexion à la base de données
    require_once "db_connexion.php";
    
    // Déclaration de variables pour les messages
    $error = '';

    // Vérification de la soumission du formulaire
    if (isset($_POST["btn_login"])) {
        
        // 2. Récupération et nettoyage des données
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        // Validation simple des champs
        if (empty($email) || empty($password)) {
            $error = "Veuillez entrer votre email et votre mot de passe.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Le format de l'email est invalide.";
        } else {
            
            // 3. Rechercher l'utilisateur dans la base de données
            try {
                // Utiliser une requête préparée pour la sécurité
                $check = $bdd_connexion->prepare('SELECT id_user, email_user, password_user, role_user FROM Users WHERE email_user = ?');
                $check->execute(array($email));
                $user = $check->fetch(PDO::FETCH_ASSOC);

                // Vérifier si un utilisateur a été trouvé
                if ($user) {
                    
                    // 4. Vérification du mot de passe haché (ÉTAPE CRUCIALE)
                    // Utiliser password_verify() pour comparer le mot de passe soumis 
                    // avec le hash stocké dans la colonne 'password_user'.
                    if (password_verify($password, $user['password_user'])) {
                        
                        // Mot de passe correct : Connexion réussie
                        
                        // 5. Enregistrer les informations de l'utilisateur dans la session
                        $_SESSION['user_id'] = $user['id_user'];
                        $_SESSION['user_email'] = $user['email_user'];
                        $_SESSION['user_role'] = $user['role_user'];

                        // 6. Redirection vers la page d'accueil ou tableau de bord
                        // Vous pouvez adapter la redirection selon le rôle
                        if ($user['role_user'] == 1) { // Exemple pour un rôle 'utilisateur'
                             header('Location: tableau_de_bord.php');
                        } else {
                             header('Location: index.php');
                        }
                        exit();
                        
                    } else {
                        // Mot de passe incorrect
                        $error = "Email ou mot de passe incorrect.";
                    }
                } else {
                    // Utilisateur non trouvé
                    $error = "Email ou mot de passe incorrect.";
                }

            } catch (PDOException $e) {
                // Gestion des erreurs PDO
                $error = "Erreur lors de la connexion : " . $e->getMessage();
            }
        }
    }
    ?>

<div class="container mt-5 shadow rounded w-25 p-3">
    <h1 class="text-primary">Connexion</h1>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    
    <form action="" method="post">
        <div class="mt-3">
            <label for="email">Email</label>
            <input type="email" required class="form-control" name="email" id="email" 
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
        <div class="mt-3">
            <label for="password">Mot de passe</label>
            <input type="password" required class="form-control" name="password" id="password">
        </div>

        <div class="mt-3">
            <button type="submit" name="btn_login" class="btn btn-primary">Se connecter</button>
        </div>
    </form>
</div>
    
</body>
</html>