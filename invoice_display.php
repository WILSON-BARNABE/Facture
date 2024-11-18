<?php
// Création du dossier uploads s'il n'existe pas
$uploadDir = 'uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Récupération des données du formulaire
$invoiceNumber = $_POST['invoiceNumber'];
$customerName = $_POST['customerName'];
$date = $_POST['date'];
$items = $_POST['itemDescription'];
$quantities = $_POST['quantity'];
$unitPrices = $_POST['unitPrice'];

// Informations de l'entreprise
$companyName = "Spécialiste en Pompe à Injection et Moteur Diesel";
$companyDetails = <<<DETAILS
Adresse : VlX4X-PL-TP-Maintenance Groupe Électrogène
Téléphone : +2315 26 69 22 17 / 99 29 22 17
Email : servicediesel24@gmail.com
NIF : 9007319-N
Compte bancaire (ECOBANK) : 32550000859
DETAILS;

// Gestion des images (signature et tampon)
$signaturePath = $uploadDir . basename($_FILES['signatureInput']['name']);
$stampPath = $uploadDir . basename($_FILES['stampInput']['name']);

// Déplacement des fichiers uploadés dans le dossier uploads
if (!move_uploaded_file($_FILES['signatureInput']['tmp_name'], $signaturePath) ||
    !move_uploaded_file($_FILES['stampInput']['tmp_name'], $stampPath)) {
    echo "Erreur lors du téléchargement des images de signature et/ou tampon.";
    exit;
}

// Calcul du total
$total = 0;
foreach ($quantities as $index => $quantity) {
    $total += $quantity * $unitPrices[$index];
}

// Affichage de la facture
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture - <?php echo htmlspecialchars($companyName); ?></title>
    <link rel="icon" href="logo.jpeg" type="image/x-icon">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }

        .invoice-container {
            max-width: 800px;
            margin: auto;
            border: 1px solid #ccc;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header img {
            width: 150px;
            height: auto;
            margin-bottom: 10px;
        }

        .company-info {
            font-size: 14px;
            line-height: 1.6;
            white-space: pre-line; /* Permet d'afficher les sauts de ligne */
        }

        .invoice-title {
            font-size: 28px;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
            color: #4CAF50;
        }

        .items-table {
            border-collapse: collapse;
            width: 100%;
        }

        .items-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .items-table th, .items-table td {
            border: 1px solid #ddd;
            text-align: left;
            padding: 8px;
        }

        .totals {
            font-size: 20px;
            font-weight: bold;
            margin-top: 10px;
            text-align: right;
        }

        .footer {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .signature, .stamp {
            width: 120px;
            height: auto;
        }

        .download-button {
            margin-top: 20px;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            background-color: #4CAF50;
            color: white;
            border: none;
        }
    </style>
</head>
<body>

<div class="invoice-container" id="invoiceContent">
    <!-- En-tête avec le logo et les informations de l'entreprise -->
    <div class="header">
        <img src="logo.jpeg" alt="Logo de l'entreprise">
        <div class="company-info">
            <strong><?php echo htmlspecialchars($companyName); ?></strong><br>
            <?php echo htmlspecialchars($companyDetails); ?>
        </div>
    </div>

    <div class="invoice-title">Facture</div>

    <!-- Détails de la facture et du client -->
    <div class="invoice-details">
        <strong>Numéro de la facture :</strong> <?php echo htmlspecialchars($invoiceNumber); ?><br>
        <strong>Date :</strong> <?php echo htmlspecialchars($date); ?><br>
        <strong>Client :</strong> <?php echo htmlspecialchars($customerName); ?><br>
    </div>

    <!-- Tableau des articles -->
    <table class="items-table">
        <thead>
            <tr>
                <th>Description</th>
                <th>Quantité</th>
                <th>Prix Unitaire</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $index => $description) : ?>
                <tr>
                    <td><?php echo htmlspecialchars($description); ?></td>
                    <td><?php echo htmlspecialchars($quantities[$index]); ?></td>
                    <td><?php echo number_format($unitPrices[$index], 2, ',', ' '); ?> FCFA</td>
                    <td><?php echo number_format($quantities[$index] * $unitPrices[$index], 2, ',', ' '); ?> FCFA</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Total -->
    <div class="totals">
        <strong>Total :</strong> <?php echo number_format($total, 2, ',', ' '); ?> FCFA
    </div>

    <!-- Signature et tampon -->
    <div class="footer">
        <?php if (file_exists($signaturePath)): ?>
            <div>
                <div class="signature-label">Signature du vendeur</div>
                <img src="<?php echo $signaturePath; ?>" alt="Signature" class="signature">
            </div>
        <?php endif; ?>
        <?php if (file_exists($stampPath)): ?>
            <img src="<?php echo $stampPath; ?>" alt="Tampon" class="stamp">
        <?php endif; ?>
    </div>
</div>

<!-- Boutons pour télécharger en PDF et imprimer -->
<div style="text-align: center; margin-top: 20px;">
    <button class="download-button" onclick="downloadInvoice()">Télécharger la facture en PDF</button>
    <button class="download-button" onclick="printInvoice()">Imprimer la facture</button>
</div>

<!-- Script pour générer le PDF et imprimer -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.min.js"></script>
<script>
function downloadInvoice() {
    const invoice = document.getElementById("invoiceContent");
    html2pdf(invoice, {
        margin:       0.5,
        filename:     'Facture_<?php echo htmlspecialchars($invoiceNumber); ?>.pdf',
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { scale: 2 },
        jsPDF:        { unit: 'in', format: 'a4', orientation: 'portrait' }
    });
}

function printInvoice() {
    const invoice = document.getElementById("invoiceContent").outerHTML;
    const printWindow = window.open('', '', 'width=800,height=600');
    printWindow.document.write('<html><head><title>Facture</title></head><body>');
    printWindow.document.write(invoice);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.print();
}
</script>

</body>
</html>
