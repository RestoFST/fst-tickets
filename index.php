<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle file upload
ob_start();
try {
    
    // Undefined | Multiple Files | $_FILES Corruption Attack
    // If this request falls under any of them, treat it invalid.
    if (
        !isset($_FILES['file']['error']) ||
        is_array($_FILES['file']['error'])
    ) {
        throw new RuntimeException('Paramètres invalides.');
    }

    // Check $_FILES['file']['error'] value.
    switch ($_FILES['file']['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            throw new RuntimeException('Pas de fichier envoyé.');
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            throw new RuntimeException('Taille limite de fichier atteinte.');
        default:
            throw new RuntimeException('Erreur inconnue.');
    }

    // You should also check filesize here. 
    if ($_FILES['file']['size'] > 100000000) {
        throw new RuntimeException('Taille limite de fichier atteinte.');
    }

    // DO NOT TRUST $_FILES['file']['mime'] VALUE !!
    // Check MIME Type by yourself.
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    if (false === $ext = array_search(
        $finfo->file($_FILES['file']['tmp_name']),
        array(
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'tiff' => 'image/tiff',
            'webp' => 'image/webp'
        ),
        true
    )) {
        throw new RuntimeException('Format de fichier invalide');
    }

    // You should name it uniquely.
    // DO NOT USE $_FILES['file']['name'] WITHOUT ANY VALIDATION !!
    // On this example, obtain safe unique name from its binary data.
    if (!move_uploaded_file(
        $_FILES['file']['tmp_name'],
        sprintf('./uploads/%s.%s',
            sha1_file($_FILES['file']['tmp_name']),
            $ext
        )
    )) {
        throw new RuntimeException('Impossible d\'enregistrer le fichier.');
    }

    echo 'Le fichier à bien été enregistré.';

} catch (RuntimeException $e) {

    echo $e->getMessage();

}
$data = ob_get_clean();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Métas PWA pour iOS (Apple) -->                                                                                                                
    <meta name="apple-mobile-web-app-capable" content="yes">                                                                                           
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">                                                                    

    <title>Photos Evenements</title>
    
    <!-- Bootstrap 3.4.1 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/css/bootstrap.min.css">
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Flatpickr (Timepicker with clock/time selector) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/fr.js"></script>
    
    <style>
        body {
            background-color: #f8fafc;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            padding-bottom: 60px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        main {
            flex: 1;
        }

        .navbar-inverse {
            background-color: #1e293b;
            border-color: #0f172a;
        }

        .navbar-inverse .navbar-brand {
            color: #f8fafc;
            font-weight: bold;
        }

        .navbar-inverse .navbar-brand:hover {
            color: #38bdf8;
        }

        .navbar-inverse .navbar-nav > li > a {
            color: #cbd5e1;
        }

        .navbar-inverse .navbar-nav > li > a:hover {
            color: #f8fafc;
        }

        .navbar-inverse .navbar-nav > .active > a, 
        .navbar-inverse .navbar-nav > .active > a:hover, 
        .navbar-inverse .navbar-nav > .active > a:focus {
            background-color: #0f172a;
            color: #38bdf8;
        }

        .panel-primary {
            border-color: #6366f1;
        }

        .panel-primary > .panel-heading {
            background-color: #6366f1;
            border-color: #6366f1;
        }

        footer {
            background-color: #1e293b;
            color: #94a3b8;
            padding: 20px 0;
            margin-top: auto;
            border-top: 1px solid #0f172a;
        }
        @media (max-width: 768px) {
            footer .footer-content {
                flex-direction: column;
                text-align: center;
                gap: 8px !important;
            }
        }

        /* Styles responsives pour les cartes de service et la page d'accueil */
        @media (max-width: 767px) {
            /* En-tête des panels jours */
            .panel-heading {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 10px;
            }
            .panel-heading .label {
                align-self: flex-start;
            }
            .panel-heading form {
                width: 100%;
            }
            .panel-heading button {
                width: 100%;
                text-align: center;
                padding: 6px 12px !important;
                font-size: 13px !important;
            }
            
            /* Cartes d'activité */
            .thumbnail {
                min-height: auto !important;
                margin-bottom: 15px;
            }
            
            /* Actions du bas de carte */
            .service-card-actions {
                display: flex;
                flex-direction: column;
                width: 100%;
                gap: 8px;
            }
            .service-card-actions form {
                width: 100%;
            }
            .service-card-actions button,
            .service-card-actions .btn {
                width: 100% !important;
                display: block !important;
                text-align: center !important;
                font-size: 13px !important;
                padding: 8px 12px !important;
            }
        }
    </style>
</head>
<body>

    <main class="container">
        <?if (isset($data)): ?>
            <div class="alert alert-info text-center" style="margin-top: 20px; border: solid 2px #17a2b8;" role="alert">
                <?= $data ?>
            </div>
        <?endif; ?>
        <h1 class="text-center" style="margin-top: 20px; margin-bottom: 20px;">Photos Evenements</h1>
        <form action="" method="POST" class="form-inline" style="margin-top: 20px; margin-bottom: 20px;" enctype="multipart/form-data">
            <div class="form-group">
                <label for="file" style="margin-right: 10px;">Sélectionnez un fichier :</label>
                <input type="file" id="file" name="file" class="form-control" placeholder="Choisir un fichier" required accept=".png, .jpg, .jpeg, .gif, .bmp, .tiff, .webp">
            </div><br>
            <div class="form-group">
                <label for="rgpd">RGPD: J'accepte la conservation temporaire pendant 7 jours de la photo pour partager votre satisfaction</label>
                <input type="checkbox" name="rgpd" id="rgpd" required aria-required="true">
            </div><br>
            <div class="form-group">
                <button type="submit" class="btn btn-primary" style="margin-left: 10px;">Envoyer</button>
            </div>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-content" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                <p style="margin: 0; font-weight: 500;">&copy; 2026 - Photos Evenements  <a hidden href="{{ path('cgu') }}" style="color: #64748b; text-decoration: none; font-weight: 600; margin-left: 5px;">| <i class="fa-solid fa-scale-balanced"></i> CGU & RGPD</a> | <a href="https://github.com/RestoFST/fst-tickets" target="_blank" style="color: #64748b; text-decoration: none; font-weight: 600; margin-left: 5px;"><i class="fa-brands fa-github"></i> GitHub</a></p>
                <p style="margin: 0; font-weight: 500;">v1.0.0</p>
            </div>
        </div>
    </footer>

    <!-- jQuery and Bootstrap 3.4.1 JS -->
    <script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/js/bootstrap.min.js"></script>
</body>
</html>