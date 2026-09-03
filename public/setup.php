<?php

declare(strict_types=1);

// Script d'installation automatique de la base de données Prysmian Tunisia
$host = '127.0.0.1';
$port = 3306;
$user = 'root';
$pass = '';
$dbName = 'prysmian_symfony_project';

$sqlFile = __DIR__ . '/../sql/01_create_database.sql';

header('Content-Type: text/html; charset=utf-8');

try {
    if (!file_exists($sqlFile)) {
        throw new Exception("Fichier SQL introuvable : " . $sqlFile);
    }

    $sqlContent = file_get_contents($sqlFile);

    // Connexion PDO initiale à MySQL
    $pdo = new PDO("mysql:host={$host};port={$port};charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_MULTI_STATEMENTS => true,
    ]);

    // Exécution du script SQL complet
    $pdo->exec($sqlContent);

    echo <<<HTML
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <title>Installation Prysmian DB - Succès</title>
        <style>
            body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f0f2f5; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
            .card { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); text-align: center; max-width: 550px; width: 90%; }
            .icon { font-size: 64px; color: #10b981; margin-bottom: 10px; }
            h1 { color: #1f2937; font-size: 24px; margin-bottom: 10px; }
            p { color: #4b5563; font-size: 15px; line-height: 1.6; margin-bottom: 25px; }
            .badge { display: inline-block; background: #ecfdf5; color: #065f46; padding: 6px 12px; border-radius: 20px; font-weight: 600; font-size: 13px; margin-bottom: 20px; }
            .btn { display: inline-block; background: #0284c7; color: white; padding: 12px 28px; text-decoration: none; border-radius: 8px; font-weight: 600; transition: background 0.2s; }
            .btn:hover { background: #0369a1; }
            .details { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; text-align: left; margin-bottom: 25px; font-size: 13px; color: #334155; }
        </style>
    </head>
    <body>
        <div class="card">
            <div class="icon">✅</div>
            <h1>Base de données initialisée !</h1>
            <div class="badge">prysmian_symfony_project</div>
            <p>Toutes les tables et les <strong>50 câbles de démonstration</strong> ont été créés avec succès dans WAMP.</p>

            <div class="details">
                <strong>Comptes utilisateurs prêts :</strong><br>
                • Admin : <code>admin@prysmian.tn</code> / <code>password123</code><br>
                • Superviseur : <code>supervisor@prysmian.tn</code> / <code>password123</code><br>
                • Technicien : <code>tech1@prysmian.tn</code> / <code>password123</code>
            </div>

            <a href="/" class="btn">🚀 Ouvrir l'application Prysmian</a>
        </div>
    </body>
    </html>
HTML;

} catch (Exception $e) {
    echo <<<HTML
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <title>Erreur d'installation DB</title>
        <style>
            body { font-family: sans-serif; background: #fef2f2; padding: 40px; }
            .card { background: white; padding: 30px; border-radius: 8px; border-left: 6px solid #ef4444; }
            pre { background: #1e293b; color: #f8fafc; padding: 15px; border-radius: 6px; overflow-x: auto; }
        </style>
    </head>
    <body>
        <div class="card">
            <h1 style="color: #dc2626;">Erreur d'initialisation de la base de données</h1>
            <p>Consultez le message d'erreur ci-dessous :</p>
            <pre>{$e->getMessage()}</pre>
        </div>
    </body>
    </html>
HTML;
}
