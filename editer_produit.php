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
        <h1 class="text-danger">Editer un produit</h1>
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
        //echo "Connexion à la base de données base_test : SUCCES !";
    } catch (PDOException $e) {
        // Erreur de connexion à PDO MySQL
        echo "Erreur de connexion a PDO MySQL !" . $e->getMessage();
    }

    //Recuperer les données en fonction de l'id du produit
    $sql = "SELECT * FROM produits WHERE id_produit=?";
    //Creer une requète préparée pour eviter les injection SQL
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
        <form  method="POST">
        <div class="mt-3">
            <input type="text" name="nom_produit" placeholder="<?= $produit["nom_produit"] ?>" class="form-control">
        </div>
        <div class="mt-3">
            <textarea rows="5" name="description_produit" class="form-control">
                <?= $produit["description_produit"] ?>
            </textarea>
        </div>
        <div class="mt-3">
            <input type="number" step="0.01" name="prix_produit" placeholder="<?= $produit["prix_produit"] ?>" class="form-control"
        </div>
        <br>
        <button type="submit" class="btn btn-secondary" name="bouton_editer">Editer le produit</button>
        <br><br>
        <a href="index.php" class="btn btn-success">Retour</a>
    </form>
    </div>

    <?php
    //Test de validation du formulaire => on recupere l'attribut name du bouton de validation
    if(isset($_POST["bouton_editer"])){
        //On recupére l'id du produit passée dans l'url => ci-dessus $id_produit = $_GET["id_produit"];
        $sql = "UPDATE produits SET nom_produit = ?, description_produit = ? ,prix_produit = ? WHERE id_produit = ?";
        //On creer la requète préparée pour lutter contre les injection SQL
        $query = $bdd_connexion->prepare($sql);
        //On lie les valeurs des champs du formulaire aux champ de la table via bindParams SQL
        $query->bindParam(1, $_POST["nom_produit"]); //Le 1er ? = <input name='nom_produit>'
        $query->bindParam(2, $_POST["description_produit"]); //Le second ? <textarea name='description_produit></textarea>'
        $query->bindParam(3, $_POST["prix_produit"]);// le dernier ? <input name='prix_produit>'
        $query->bindParam(4, $id_produit);
         //Executer le requète
    try{
        $query->execute();
        //Si ca marche => on appel un alert javascript + une redirection PHP
        ?>
            <script>
                alert("Votre produit a bien été mis a jour !");
            </script>
        <?php
    }catch(PDOException $e){
        echo "Erreur lors de la mise a jour du produit !" . $e->getMessage();
    }
    } 
?>
</body>
</html>