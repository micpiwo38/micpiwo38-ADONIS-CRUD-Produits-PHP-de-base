<?php

declare(strict_types=1);

// ------- CONNEXION BDD -------
$host = "localhost";
$db_name = "base_test";
$user = "root";
$password = "";

try {
    $bdd = new PDO("mysql:host=$host;dbname=$db_name;charset=UTF8", $user, $password);
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("<div class='alert alert-danger'>Erreur connexion : " . $e->getMessage() . "</div>");
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/bootstrap.css">
    <title>Afficher les produits</title>
</head>

<body>

    <header>
        <nav>
            <?php require_once "navbar.php"; ?>
        </nav>
    </header>

    <div class="container mt-4">
        <h1 class="text-info">Liste des produits</h1>
        <a href="ajouter_produit.php" class="btn btn-success mb-4">Ajouter un produit</a>

        <?php
        // ------- REQUÊTE JOIN COMPLÈTE -------
        $sql = $bdd->prepare("
            SELECT 
                p.id_produit,
                p.nom_produit,
                p.description_produit,
                p.prix_produit,

                c.categorie_nom,
                r.reference_nom,

                i.images_nom AS image

            FROM produits p

            LEFT JOIN categories c 
                ON p.produit_categorie = c.categorie_id

            LEFT JOIN produit_references r 
                ON p.produit_reference = r.reference_id

            LEFT JOIN produits_images pi 
                ON p.id_produit = pi.produits_id

            LEFT JOIN images i 
                ON pi.image_id = i.image_id

            ORDER BY p.id_produit DESC
            ");
        $sql->execute();

        $produits = $sql->fetchAll(PDO::FETCH_ASSOC);

        if (!$produits) {
            echo "<div class='alert alert-warning'>Aucun produit trouvé.</div>";
        }

        // ======= AFFICHAGE AVEC GROUPEMENT DES IMAGES =======

        $current_id = null;
        $images = [];

        foreach ($produits as $row):
            // Quand on passe à un autre produit => afficher la carte précédente
            if ($current_id !== $row["id_produit"]) {

                if ($current_id !== null) {
                    // --- AFFICHAGE DE LA CARTE PRODUIT ---
        ?>
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header">
                            <h3 class="text-primary"><?= htmlspecialchars($current_row["nom_produit"]) ?></h3>
                        </div>

                        <div class="card-body">
                            <p><strong>Description :</strong> <?= nl2br(htmlspecialchars($current_row["description_produit"])) ?></p>
                            <p><strong>Prix :</strong> <?= number_format($current_row["prix_produit"], 2) ?> €</p>
                            <p><strong>Catégorie :</strong> <?= htmlspecialchars($current_row["categorie_nom"] ?? "Non définie") ?></p>
                            <p><strong>Référence :</strong> <?= htmlspecialchars($current_row["reference_nom"] ?? "Non définie") ?></p>

                            <h5 class="mt-3">Images :</h5>

                            <?php if (!empty($images)): ?>
                                <div class="d-flex flex-wrap">
                                    <?php foreach ($images as $img): ?>
                                        <img src="<?= $img ?>" width="150" class="me-2 mb-2 border rounded">
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-muted">Aucune image.</div>
                            <?php endif; ?>

                            <a href="details_produit.php?id_produit=<?= $current_row['id_produit'] ?>"
                                class="btn btn-info mt-3">Détails</a>
                        </div>
                    </div>
            <?php
                }

                // Nouveau produit → reset images
                $images = [];
                $current_id = $row["id_produit"];
                $current_row = $row;
            }

            // Ajoute l’image si elle existe
            if (!empty($row["image"])) {
                $images[] = $row["image"];
            }

        endforeach;

        // ======= AFFICHAGE DU DERNIER PRODUIT =======
        if ($current_id !== null):
            ?>
            <div class="card mb-4 shadow-sm">
                <div class="card-header">
                    <h3 class="text-primary"><?= htmlspecialchars($current_row["nom_produit"]) ?></h3>
                </div>

                <div class="card-body">
                    <p><strong>Description :</strong> <?= nl2br(htmlspecialchars($current_row["description_produit"])) ?></p>
                    <p><strong>Prix :</strong> <?= number_format($current_row["prix_produit"], 2) ?> €</p>
                    <p><strong>Catégorie :</strong> <?= htmlspecialchars($current_row["categorie_nom"] ?? "Non définie") ?></p>
                    <p><strong>Référence :</strong> <?= htmlspecialchars($current_row["reference_nom"] ?? "Non définie") ?></p>

                    <h5 class="mt-3">Images :</h5>

                    <?php if (!empty($images)): ?>
                        <div class="d-flex flex-wrap">
                            <?php foreach ($images as $img): ?>
                                <img src="<?= $img ?>" width="150" class="me-2 mb-2 border rounded">
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-muted">Aucune image.</div>
                    <?php endif; ?>

                    <a href="details_produit.php?id_produit=<?= $current_row['id_produit'] ?>"
                        class="btn btn-info mt-3">Détails</a>
                </div>
            </div>
        <?php endif; ?>

    </div>
</body>

</html>