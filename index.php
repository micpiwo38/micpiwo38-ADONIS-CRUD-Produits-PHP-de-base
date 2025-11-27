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
    <div class="container">
        <h1 class="text-info">Gestion de vos produits :</h1>
        <h2 class="text-warning">Un CRUD Basic de produits PHP</h2>
        <p>Ceci est sur la banche user_login></p>
        <a href="ajouter_produit.php" class="btn btn-success">Ajouter un produit</a>
                <br><br>
<?php
    $host = "localhost";
    $db_name = "base_test";
    $user = "root";
    $password = "";
    try {
        //Instance de la classe PDO + hote + nom de la base de données + encodage des caractères EUROPE
        // +  utilisateur PhpMyAdmin + Mot de passe PhpMyAdmin
        $bdd_connexion = new PDO('mysql:host='.$host.';dbname='.$db_name.';charset=UTF8', $user, $password);
        //Debug
        $bdd_connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "Connexion à la base de données base_test : SUCCES !";
    } catch (PDOException $e) {
        // Erreur de connexion à PDO MySQL
        echo "Erreur de connexion a PDO MySQL !" . $e->getMessage();
    }
    //Ecrire la requète SQL
    $sql = $bdd_connexion->query("SELECT * FROM produits");
    //Debug de la variable
    var_dump($sql);
    //Boucle de parcours du tableau
    foreach($sql as $row){
        //Afficher le champ de la table
        ?>
            <div class="container">
                <ul class="list-group">
                        <li class="list-group-item">ID du produit : <?=  $row["id_produit"] ?></li>
                        <li class="list-group-item">Nom du produit : <?=  $row["nom_produit"] ?></li>
                        <li class="list-group-item">Description du produit : <?=  $row["description_produit"] ?></li>
                        <li class="list-group-item">Prix du produit : <?=  $row["prix_produit"] ?></li>
                        <li class="list-group-item">
                            <a href="details_produit.php?id_produit=<?= $row["id_produit"] ?>" class="btn btn-info">Détails du produit</a>
                        </li>
                        <div class="mt-3"></div>
                </ul>
            </div>

        <?php
    }
?>
</div>
</body>
</html>