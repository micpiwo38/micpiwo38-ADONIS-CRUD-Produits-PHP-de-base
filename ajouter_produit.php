<?php 
    declare(strict_types = 1);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/bootstrap.css">
    <title>Afficher les produits</title>
</head>
<body>
        
<?php
    $host = "localhost";
    $db_name = "base_test";
    $user = "root";
    $password = "";
    try {
        //Instance de la classe PDO + hote + nom de la base de données + encodage des caractères EUROPE
        // +  utilisateur PhpMyAdmin + Mot de passe PhpMyAdmin
        $bdd_connexion = new PDO('mysql:host='.$host.';dbname='.$db_name.';charset=UTF8', $user, $password);
        $bdd_connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        // Erreur de connexion à PDO MySQL
        echo "Erreur de connexion a PDO MySQL !" . $e->getMessage();
    }
    ?>
    <div class="container">
        <h1 class="text-warning">Ajouter un produit</h1>
        <h2 class="text-success">On utilise la super globale $_POST['attribut name du formulaire']</h2>
        <form action="" method="POST">
        <div class="mt-3">
            <input type="text" name="nom_produit" placeholder="Nom du produit" class="form-control">
        </div>
        <div class="mt-3">
            <textarea rows="5" name="description_produit" class="form-control"></textarea>
        </div>
        <div class="mt-3">
            <input type="number" step="0.01" name="prix_produit" placeholder="Prix du produit" class="form-control">
        </div>
        <br>
        <button type="submit" class="btn btn-success" name="bouton_ajouter">Ajouter le produit</button>
        <a href="index.php" class="btn btn-danger">Retour</a>
    </form>
    </div>

    <?php
    //Test de validation du formulaire => on recupere l'attribut name du bouton de validation
    if(isset($_POST["bouton_ajouter"])){
        //On insert les 3 valeurs inconues ? ? ? dans la table produit dans les champs => `nom_produit`, `description_produit`, `prix_produit`
        $sql = "INSERT INTO produits(nom_produit, description_produit, prix_produit) VALUES (?,?,?)";
        //On creer la requète préparée pour lutter contre les injections SQL
        $query = $bdd_connexion->prepare($sql);

        // On lie les valeurs, en utilisant bindParam ou execute avec un tableau
        // L'utilisation de execute avec un tableau est souvent plus simple pour des requêtes simples
        // trim — Supprime les espaces (ou d'autres caractères) en début et fin de chaîne
        // htmlspecialchars — Convertit des caractères spéciaux en entités HTML

        $data = [
            trim(htmlspecialchars($_POST["nom_produit"])),
            trim(htmlspecialchars($_POST["description_produit"])),
            trim(htmlspecialchars($_POST["prix_produit"]))
        ];

        //Executer le requète
        try{
            // $query->execute($data); // Vous pouvez aussi utiliser $query->execute(array($data));
            // Si vous préférez garder bindParam :
            $query->bindParam(1, $_POST["nom_produit"]);
            $query->bindParam(2, $_POST["description_produit"]);
            $query->bindParam(3, $_POST["prix_produit"]);
            $query->execute();

            // Si ca marche => redirection PHP et arrêt du script
            header('Location: http://localhost/test_php/index.php');
            exit();
        }catch(PDOException $e){
            echo "Erreur lors de l'ajout du produit !" . $e->getMessage();
        }
    }
?>
</body>
</html>