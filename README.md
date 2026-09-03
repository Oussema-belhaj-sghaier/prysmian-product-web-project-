# Smart Cable Management Platform — Prysmian Tunisia

Plateforme web professionnelle pour la gestion de 1000+ installations de câbles électriques dans 50 régions tunisiennes.

## 🏗️ Architecture

- **Backend**: Symfony 6.4+ (PHP 8.2+)
- **ORM**: Doctrine
- **Database**: SQLite (dev) / PostgreSQL (prod)
- **Auth**: JWT (API) + Session (Web) + RBAC
- **ML**: Régression linéaire PHP native (pas de dépendance Python)

## 📦 Installation

```bash
# 1. Cloner et installer
cd smart-cable-management
composer install

# 2. Configurer la base
cp .env .env.local
# Modifier DATABASE_URL si besoin

# 3. Créer la base et les migrations
php bin/console doctrine:database:create
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate

# 4. Générer les clés JWT
php bin/console lexik:jwt:generate-keypair

# 5. Créer un admin
php bin/console doctrine:fixtures:load
# ou manuellement via SQL

# 6. Lancer le serveur
symfony server:start
```

## 🔌 API Endpoints

| Endpoint | Méthode | Description | Rôles |
|----------|---------|-------------|-------|
| `/api/login_check` | POST | Authentification JWT | Public |
| `/api/dashboard` | GET | Stats dashboard | All |
| `/api/cables` | GET/POST | Liste / Créer câbles | All |
| `/api/cables/{id}` | GET/PUT/DELETE | CRUD câble | All |
| `/api/maintenances` | GET/POST | Maintenances | Admin, Tech |
| `/api/alerts` | GET | Liste alertes | All |
| `/api/alerts/{id}/acknowledge` | POST | Reconnaître alerte | All |
| `/api/alerts/{id}/resolve` | POST | Résoudre alerte | All |
| `/api/ml/predict/{id}` | POST | Prédiction single | Admin, Sup |
| `/api/ml/batch` | POST | Batch ML | Admin, Sup |
| `/api/reports/monthly` | GET | Rapport mensuel | Admin, Sup |
| `/api/reports/costs` | GET | Rapport coûts | Admin, Sup |

## 🤖 ML — Régression Linéaire

Le modèle calcule un score d'urgence (0-100) basé sur :
- Âge du câble
- Température moyenne
- Courant moyen
- Jours depuis dernière maintenance
- Tendance température
- Fréquence maintenance historique

**Seuil**: Score > 70 = Maintenance recommandée

### Commandes CRON
```bash
# Toutes les nuits à 2h — Batch prédictions ML
0 2 * * * cd /var/www/prysmian && php bin/console app:ml:batch-predict

# Toutes les 15 minutes — Scan alertes
*/15 * * * * cd /var/www/prysmian && php bin/console app:alerts:scan
```

## 🔐 RBAC

| Rôle | Permissions |
|------|-------------|
| **ADMIN** | Tout (CRUD, users, config) |
| **SUPERVISOR** | Rapports, stats, approuver maintenances |
| **TECHNICIAN** | Câbles région assignée, log maintenances |

## 🗄️ Entités

1. **Cable** — Installations de câbles
2. **User** — Utilisateurs (techniciens, superviseurs, admins)
3. **MaintenanceLog** — Journal des maintenances
4. **Alert** — Alertes et anomalies
5. **MLPrediction** — Prédictions maintenance

## 📝 License

Propriétaire — Prysmian Group Tunisia
