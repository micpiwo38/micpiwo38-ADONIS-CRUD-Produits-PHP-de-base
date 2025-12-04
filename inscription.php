<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/bootstrap.css"> 
    <title>Inscription</title>
</head>
<body>
    <header>
        <nav>
            <?php require_once "navbar.php" ?>
        </nav>
    </header>
    <?php
    // Inclure la connexion a la base de données
    require_once "db_connexion.php";
    
    // Déclaration de variables pour les messages
    $error = '';
    $success = '';

    // Vérification de la soumission du formulaire
    if (isset($_POST["btn_register_user"])) {
        
        // 1. Récupération et nettoyage des données du formulaire
        // On utilise trim() pour supprimer les espaces inutiles autour
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        $password_repeat = $_POST['password_repeat'];
        // Regex exigeant :
        // - Au moins un caractère majuscule (?=.*?[A-Z])
        // - Au moins un caractère minuscule (?=.*?[a-z])
        // - Au moins un chiffre (?=.*?[0-9])
        // - Au moins un caractère spécial (?=.*?[#?!@$%^&*-])
        // - Minimum 8 caractères au total (.{8,}$)
        $password_regex = "/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,}$/";
        // Valeur par défaut pour le rôle, correspondant à la colonne 'role_user' INT
        $default_role = 1; 

        // 2. Validation des données
        if (empty($email) || empty($password) || empty($password_repeat)) {
            $error = "Tous les champs doivent être remplis.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "L'adresse email n'est pas valide.";
        } elseif ($password !== $password_repeat) {
            $error = "Les mots de passe ne correspondent pas.";
        } elseif (strlen($password) < 6) {
             $error = "Le mot de passe doit contenir au moins 6 caractères.";
        }elseif(!preg_match($password_regex, $password)){
            $error = "Le mot de passe doit contenir au moins 8 caractères, dont une majuscule, une minuscule, un chiffre et un caractère spécial.";
        }else {
            // 3. Vérification de l'existence de l'email
            try {
                // Requête préparée pour éviter les injections SQL
                $check = $bdd_connexion->prepare('SELECT email_user FROM Users WHERE email_user = ?');
                $check->execute(array($email));
                $row = $check->fetch();
                
                if ($row) {
                    $error = "Cette adresse email est déjà utilisée.";
                } else {
                    
                    // 4. Hachage sécurisé du mot de passe
                    // **password_hash()** utilise par défaut l'algorithme BCRYPT, le plus sûr pour les mots de passe.
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    // 5. Insertion de l'utilisateur dans la base de données
                    $insert = $bdd_connexion->prepare(
                        'INSERT INTO Users (email_user, password_user, role_user) VALUES (:email, :password, :role)'
                    );
                    $insert->execute(array(
                        'email' => $email,
                        'password' => $hashed_password,
                        'role' => $default_role // Vous pouvez ajuster cette valeur si besoin
                    ));
                    
                    $success = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
                    // Optionnel : Redirection vers une page de connexion
                    header('Location: connexion.php');
                    exit();
                }

            } catch (PDOException $e) {
                // Gestion des erreurs PDO
                $error = "Erreur lors de l'inscription : " . $e->getMessage();
            }
        }
    }
    ?>

<div class="container mt-5 shadow rounded w-25 p-3">
    <h1 class="text-success">Inscription</h1>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
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
            <label for="password_repeat">Confirmer le mot de passe</label>
            <input type="password" required class="form-control" name="password_repeat" id="password_repeat">
        </div>

        <div class="mt-3">
            <button type="submit" name="btn_register_user" class="btn btn-info">S'inscrire</button>
        </div>
    </form>
</div>
    
</body>
</html>