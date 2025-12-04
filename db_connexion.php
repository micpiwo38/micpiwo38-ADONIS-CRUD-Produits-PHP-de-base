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
        //echo "Connexion à la base de données base_test : SUCCES !";
    } catch (PDOException $e) {
        // Erreur de connexion à PDO MySQL
        echo "Erreur de connexion a PDO MySQL !" . $e->getMessage();
    }

?>