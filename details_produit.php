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
    <header>
        <nav>
            <?php require_once "navbar.php" ?>
        </nav>
    </header>
        <div class="container">
            <h1 class="text-info">Détails du produit</h1>
        </div>
<?php
    $host = "localhost";
    $db_name = "base_test";
    $user = "root";
    $password = "";
    try {
        //Instance de la classe PDO + hote + nom de la base de données + encodage des caractères EUROPE
        // +  utilisateur PhpMyAdmin + Mot de passe PhpMyAdmin
        $bdd_connexion = new PDO('mysql:host='.$host.';dbname='.$db_name.';charset=UTF8', $user, $password);
        echo "Connexion à la base de données base_test : SUCCES !";
    } catch (PDOException $e) {
        // Erreur de connexion à PDO MySQL
        echo "Erreur de connexion a PDO MySQL !" . $e->getMessage();
    }
    //Ecrire la requète SQL avec le prediquat WHERE + cle de l'url soit : 
    /*
        <a href="details_produit.php?id_produit=<?= $row["id_produit"] ?>" class="btn btn-info">Détails du produit</a>
    */
    $sql = "SELECT * FROM produits WHERE id_produit=?";
    //Creer une requète préparée pour eviter les injections SQL
    $query = $bdd_connexion->prepare($sql);
    //Récuperer l'id_produit dans l'url
    $id_produit = $_GET["id_produit"];
    //Debug de l'id_produit concerné
    var_dump($id_produit);
    //Lié l'id de l'URL a la requète = Bind Params
    $query->bindParam(1, $id_produit);
    //Executer la requète
    $query->execute();
    //Parcourir le resultat avec la fonction fetch() PHP
    $produit = $query->fetch();
        //Afficher les champs de la table
        ?>
            <div class="container">
                <ul class="list-group">
                <li class="list-group-item">ID du produit : <?=  $produit["id_produit"] ?></li>
                <li class="list-group-item">Nom du produit : <?=  $produit["nom_produit"] ?></li>
                <li class="list-group-item">Description du produit : <?=  $produit["description_produit"] ?></li>
                <li class="list-group-item">Prix du produit : <?=  $produit["prix_produit"] ?></li>
                 <li class="list-group-item">
                    <a class="btn btn-info" href="editer_produit.php?id_produit=<?= $produit["id_produit"] ?>">Editer le produit</a>
                </li>
                <li class="list-group-item">
                    <a class="btn btn-warning" href="supprimer_produit.php?id_produit=<?= $produit["id_produit"] ?>">Supprimer</a>
                </li>
                <li class="list-group-item">
                    <a href="index.php" class="btn btn-danger">Retour</a>
                </li>
            </ul>
            </div>
        <?php
?>
</body>
</html>