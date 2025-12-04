   <?php
    $host = "localhost";
    $db_name = "base_test";
    $user = "root";
    $password = "";
    $upload_dir = 'assets/images/'; // Dossier où stocker les images

    // Vérifier l'existence du dossier d'upload
    if (!is_dir($upload_dir)) {
        // Tente de créer le dossier si nécessaire
        mkdir($upload_dir, 0777, true);
    }

    try {
        $bdd_connexion = new PDO('mysql:host=' . $host . ';dbname=' . $db_name . ';charset=UTF8', $user, $password);
        $bdd_connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        echo "<div class='container alert alert-danger'>Erreur de connexion a PDO MySQL ! " . $e->getMessage() . "</div>";
        exit(); // Arrêter le script si la connexion échoue
    }

    // Fonction pour récupérer les catégories
    function getCategories($bdd)
    {
        $query = $bdd->query('SELECT categorie_id, categorie_nom FROM categories ORDER BY categorie_nom ASC');
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
    $categories = getCategories($bdd_connexion);

    // --- GESTION DU FORMULAIRE ---
    if (isset($_POST["bouton_ajouter"])) {

        // Démarrage d'une transaction pour garantir la cohérence des données
        $bdd_connexion->beginTransaction();

        try {
            // 1. Nettoyage des données du formulaire
            $nom_produit = trim(htmlspecialchars($_POST["nom_produit"]));
            $description_produit = trim(htmlspecialchars($_POST["description_produit"]));
            $prix_produit = floatval($_POST["prix_produit"]);
            $produit_categorie = intval($_POST["produit_categorie"]);
            $reference_nom = trim(htmlspecialchars($_POST["reference_nom"]));

            // 2. Gestion et Insertion de la Référence
            // Vérifier si la référence existe déjà (pour la relation 1:1)
            $check_ref = $bdd_connexion->prepare("SELECT reference_id FROM produit_references WHERE reference_nom = ?");
            $check_ref->execute([$reference_nom]);
            $ref_row = $check_ref->fetch(PDO::FETCH_ASSOC);

            $produit_reference_id = 0; // Initialisation

            if ($ref_row) {
                // La référence existe, on récupère son ID existant
                $produit_reference_id = $ref_row['reference_id'];
            } else {
                // La référence n'existe pas, on l'insère

                // Correction de la syntaxe SQL (parenthèses autour de la colonne)
                $insert_ref = $bdd_connexion->prepare("INSERT INTO produit_references (reference_nom) VALUES (?)");
                $insert_ref->execute([$reference_nom]);

                // On récupère l'ID inséré 
                $produit_reference_id = $bdd_connexion->lastInsertId();
            }

            // Vérification critique (si l'AUTO_INCREMENT est désactivé)
            if (empty($produit_reference_id) || $produit_reference_id == 0) {
                // Ceci interceptera l'erreur 1452 si reference_id n'est pas A.I. ou si l'insertion a échoué.
                throw new Exception("Erreur de BDD : L'ID de référence n'a pas été généré ou est nul. Vérifiez l'AUTO_INCREMENT.");
            }

            // 3. Insertion du Produit (Utilise l'ID entier de la référence)
            $sql_prod = "INSERT INTO produits(nom_produit, description_produit, prix_produit, produit_categorie, produit_reference) 
                         VALUES (?, ?, ?, ?, ?)";

            $query_prod = $bdd_connexion->prepare($sql_prod);
            $query_prod->execute([
                $nom_produit,
                $description_produit,
                $prix_produit,
                $produit_categorie,
                $produit_reference_id // Utilisation de l'ID entier (INT)
            ]);

            // Récupérer l'ID du produit nouvellement inséré
            $produit_id = $bdd_connexion->lastInsertId();

            // 4. Gestion de l'Upload des Images et de la relation N:M
            if (isset($_FILES['product_images']) && !empty($_FILES['product_images']['name'][0])) {

                $files = $_FILES['product_images'];
                $total_files = count($files['name']);

                for ($i = 0; $i < $total_files; $i++) {

                    $file_name = $files['name'][$i];
                    $file_tmp = $files['tmp_name'][$i];
                    $file_error = $files['error'][$i];

                    if ($file_error === 0) {
                        $extension = pathinfo($file_name, PATHINFO_EXTENSION);
                        // Créer un nom unique et sécurisé pour le fichier
                        $new_file_name = uniqid('img_', true) . '.' . $extension;
                        $destination = $upload_dir . $new_file_name;

                        // Déplacer le fichier téléchargé
                        if (move_uploaded_file($file_tmp, $destination)) {

                            // A. Insertion de l'Image (chemins) dans la table 'images'
                            $image_path = $destination;
                            $insert_img = $bdd_connexion->prepare('INSERT INTO images (images_nom) VALUES (?)');
                            $insert_img->execute([$image_path]);
                            $image_id = $bdd_connexion->lastInsertId();

                            // B. Insertion du lien (N:M) dans la table 'produits_images'
                            $insert_link = $bdd_connexion->prepare('INSERT INTO produits_images (produits_id, image_id) VALUES (?, ?)');
                            $insert_link->execute([$produit_id, $image_id]);
                        }
                    }
                }
            }

            // Si tout s'est bien passé, on valide la transaction
            $bdd_connexion->commit();

            // Redirection après succès
            header('Location: index.php?success=add');
            exit();
        } catch (PDOException $e) {
            // Annuler toutes les modifications BDD en cas d'erreur SQL
            $bdd_connexion->rollBack();
            echo "<div class='container alert alert-danger mt-3'>Erreur critique (SQL) : " . $e->getMessage() . "</div>";
        } catch (Exception $e) {
            // Annuler en cas d'erreur de logique (comme l'ID manquant)
            $bdd_connexion->rollBack();
            echo "<div class='container alert alert-danger mt-3'>Erreur de logique : " . $e->getMessage() . "</div>";
        }
    }
    ?>
   <!DOCTYPE html>
   <html lang="en">

   <head>
       <meta charset="UTF-8">
       <meta name="viewport" content="width=device-width, initial-scale=1.0">
       <link rel="stylesheet" href="assets/css/bootstrap.css">
       <title>Ajouter un produit (Admin)</title>
   </head>

   <body>
       <header>
           <nav>
               <?php require_once "navbar.php"  ?>
           </nav>
       </header>
       <div class="container w-50 shadow rounded p-3">
           <h1 class="text-warning">Ajouter un produit</h1>

           <form action="" method="POST" enctype="multipart/form-data">
               <h2 class="text-secondary">Informations Produit</h2>

               <div class="mt-3">
                   <label for="nom_produit">Nom du produit</label>
                   <input type="text" name="nom_produit" id="nom_produit" placeholder="Nom du produit" class="form-control" required>
               </div>

               <div class="mt-3">
                   <label for="description_produit">Description</label>
                   <textarea rows="5" name="description_produit" id="description_produit" class="form-control" required></textarea>
               </div>

               <div class="mt-3">
                   <label for="prix_produit">Prix du produit</label>
                   <input type="number" step="0.01" name="prix_produit" id="prix_produit" placeholder="Prix du produit" class="form-control" required>
               </div>

               <div class="mt-3">
                   <label for="reference_nom">Référence (Saisie Admin)</label>
                   <input type="text" name="reference_nom" id="reference_nom" placeholder="Ex: REF-ABC-123" class="form-control" required>
               </div>

               <div class="mt-3">
                   <label for="produit_categorie">Catégorie</label>
                   <select name="produit_categorie" id="produit_categorie" class="form-control" required>
                       <option value="">-- Choisir une catégorie --</option>
                       <?php foreach ($categories as $cat): ?>
                           <option value="<?= $cat['categorie_id'] ?>">
                               <?= htmlspecialchars($cat['categorie_nom']) ?>
                           </option>
                       <?php endforeach; ?>
                   </select>
               </div>

               <h2 class="text-secondary mt-5">Gestion des Images</h2>
               <div class="mt-3">
                   <label for="product_images">Images du produit (format .jpg, .png)</label>
                   <input type="file" name="product_images[]" id="product_images" class="form-control" multiple accept="image/*" required>
               </div>

               <br>
               <button type="submit" class="btn btn-success" name="bouton_ajouter">Ajouter le produit</button>
               <a href="index.php" class="btn btn-danger">Retour</a>
           </form>
       </div>
   </body>

   </html>