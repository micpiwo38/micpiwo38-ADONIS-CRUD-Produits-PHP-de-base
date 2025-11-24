
        
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
    //Requete SQL methode delete + id de url a binder (lié)
    $sql = "DELETE FROM `produits`  WHERE id_produit=?";
    //La requète préparée pour eviter les injection SQL
    $query = $bdd_connexion->prepare($sql);
    //Recuperer l'id du produit dans URL grace a la super globale $_GET["id_produit"]
    $id_produit = $_GET["id_produit"];
    //Lié les paramètres = id du produit de l'url a la variable de la requète SQL => "DELETE FROM `produits`  WHERE id_produit=?";
    $query->bindParam(1, $id_produit);

    //Executer le requète
    try{
        //Si ca marche => on appel un alert javascript + une redirection PHP
        ?>
        <script>
            alert("Confirmer la supression du produit : <?= $id_produit ?>");
            window.location.href = 'http://localhost/test_php/index.php'; // Redirection JS
        </script>
        <?php
        $query->execute();
    }catch(PDOException $e){
        echo "Erreur de suppresion du produit !" . $e->getMessage();
    }
?>
