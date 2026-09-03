-- ============================================================
-- Prysmian Tunisia — Plateforme de Gestion Industrielle
-- Fabricant de câbles électriques et fibre optique
-- Script SQL MySQL / MariaDB
-- ============================================================

CREATE DATABASE IF NOT EXISTS prysmian_symfony_project CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE prysmian_symfony_project;

-- ============================================================
-- Suppression dans l'ordre des dépendances
-- ============================================================
DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS ml_predictions;
DROP TABLE IF EXISTS alerts;
DROP TABLE IF EXISTS maintenance_logs;
DROP TABLE IF EXISTS cables;
DROP TABLE IF EXISTS users;

-- ============================================================
-- TABLE: users — Équipe Prysmian Tunisia
-- ============================================================
CREATE TABLE users (
    id CHAR(36) NOT NULL,
    email VARCHAR(180) NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    role VARCHAR(50) NOT NULL DEFAULT 'TECHNICIAN',
    phone VARCHAR(20) DEFAULT NULL,
    region_assigned VARCHAR(100) DEFAULT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'ACTIVE',
    last_login DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_users_email (email),
    KEY idx_users_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: cables — Catalogue Produits Câbles Prysmian
-- ============================================================
CREATE TABLE cables (
    id CHAR(36) NOT NULL,
    reference_code VARCHAR(50) NOT NULL,
    designation VARCHAR(200) NOT NULL,
    cable_type VARCHAR(20) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'IN_STOCK',
    nominal_voltage FLOAT DEFAULT NULL,
    conductor_section FLOAT DEFAULT NULL,
    conductor_material VARCHAR(20) DEFAULT 'COPPER',
    number_of_conductors INT DEFAULT 3,
    insulation VARCHAR(30) DEFAULT 'XLPE',
    standards VARCHAR(255) DEFAULT NULL,
    price_per_meter FLOAT DEFAULT NULL,
    stock_meters FLOAT DEFAULT 0,
    stock_alert_threshold FLOAT DEFAULT 1000,
    factory VARCHAR(100) DEFAULT NULL,
    description LONGTEXT DEFAULT NULL,
    data_sheet_path VARCHAR(255) DEFAULT NULL,
    metadata LONGTEXT DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_cables_ref (reference_code),
    KEY idx_cables_type (cable_type),
    KEY idx_cables_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: maintenance_logs — Ordres de Production
-- ============================================================
CREATE TABLE maintenance_logs (
    id CHAR(36) NOT NULL,
    cable_id CHAR(36) NOT NULL,
    technician_id CHAR(36) DEFAULT NULL,
    maintenance_type VARCHAR(30) NOT NULL,
    order_number VARCHAR(50) DEFAULT NULL,
    target_length_meters FLOAT DEFAULT NULL,
    produced_length_meters FLOAT DEFAULT NULL,
    description LONGTEXT NOT NULL,
    start_date DATETIME NOT NULL,
    end_date DATETIME DEFAULT NULL,
    cost FLOAT NOT NULL DEFAULT 0,
    notes LONGTEXT DEFAULT NULL,
    result_status VARCHAR(20) NOT NULL DEFAULT 'PLANNED',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_maintenance_cable (cable_id),
    KEY idx_maintenance_operator (technician_id),
    KEY idx_maintenance_status (result_status),
    CONSTRAINT fk_maintenance_cable FOREIGN KEY (cable_id) REFERENCES cables (id) ON DELETE CASCADE,
    CONSTRAINT fk_maintenance_operator FOREIGN KEY (technician_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: alerts — Alertes Usine
-- ============================================================
CREATE TABLE alerts (
    id CHAR(36) NOT NULL,
    cable_id CHAR(36) NOT NULL,
    alert_type VARCHAR(30) NOT NULL,
    severity VARCHAR(15) NOT NULL,
    message VARCHAR(255) NOT NULL,
    status VARCHAR(15) NOT NULL DEFAULT 'OPEN',
    acknowledged_by CHAR(36) DEFAULT NULL,
    acknowledged_at DATETIME DEFAULT NULL,
    resolved_at DATETIME DEFAULT NULL,
    resolution LONGTEXT DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_alerts_cable (cable_id),
    KEY idx_alerts_status (status),
    KEY idx_alerts_severity (severity),
    CONSTRAINT fk_alerts_cable FOREIGN KEY (cable_id) REFERENCES cables (id) ON DELETE CASCADE,
    CONSTRAINT fk_alerts_ack FOREIGN KEY (acknowledged_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: ml_predictions — Prédictions Qualité
-- ============================================================
CREATE TABLE ml_predictions (
    id CHAR(36) NOT NULL,
    cable_id CHAR(36) NOT NULL,
    prediction_type VARCHAR(30) NOT NULL,
    predicted_date DATETIME NOT NULL,
    confidence_score FLOAT NOT NULL,
    maintenance_urgency FLOAT NOT NULL,
    reason VARCHAR(255) NOT NULL,
    model_version VARCHAR(20) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_ml_cable (cable_id),
    CONSTRAINT fk_ml_cable FOREIGN KEY (cable_id) REFERENCES cables (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: audit_logs
-- ============================================================
CREATE TABLE audit_logs (
    id INT AUTO_INCREMENT NOT NULL,
    user_id CHAR(36) DEFAULT NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50) DEFAULT NULL,
    entity_id VARCHAR(36) DEFAULT NULL,
    details LONGTEXT DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_audit_user (user_id),
    KEY idx_audit_action (action)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- DONNÉES : Utilisateurs Prysmian Tunisia
-- Mot de passe: password123 (bcrypt hash)
-- ============================================================
INSERT INTO users (id, email, password, first_name, last_name, role, phone, region_assigned, status) VALUES
('u1000000-0000-0000-0000-000000000001', 'admin@prysmian.tn',
 '$2y$13$jM2v.sMTfvHIv4oSnVQ4Iu1nHQBi1C6yb9hbcbzlBb.jOeSdm1kZi',
 'Karim', 'Mansouri', 'ADMIN', '+216 71 000 001', NULL, 'ACTIVE'),
('u1000000-0000-0000-0000-000000000002', 'supervisor@prysmian.tn',
 '$2y$13$jM2v.sMTfvHIv4oSnVQ4Iu1nHQBi1C6yb9hbcbzlBb.jOeSdm1kZi',
 'Sonia', 'Belhadj', 'SUPERVISOR', '+216 71 000 002', 'Bizerte', 'ACTIVE'),
('u1000000-0000-0000-0000-000000000003', 'tech1@prysmian.tn',
 '$2y$13$jM2v.sMTfvHIv4oSnVQ4Iu1nHQBi1C6yb9hbcbzlBb.jOeSdm1kZi',
 'Mehdi', 'Trabelsi', 'TECHNICIAN', '+216 71 000 003', 'Bizerte', 'ACTIVE'),
('u1000000-0000-0000-0000-000000000004', 'tech2@prysmian.tn',
 '$2y$13$jM2v.sMTfvHIv4oSnVQ4Iu1nHQBi1C6yb9hbcbzlBb.jOeSdm1kZi',
 'Amine', 'Hajji', 'TECHNICIAN', '+216 71 000 004', 'Sfax', 'ACTIVE'),
('u1000000-0000-0000-0000-000000000005', 'commercial@prysmian.tn',
 '$2y$13$jM2v.sMTfvHIv4oSnVQ4Iu1nHQBi1C6yb9hbcbzlBb.jOeSdm1kZi',
 'Leila', 'Ghanmi', 'COMMERCIAL', '+216 71 000 005', NULL, 'ACTIVE');

-- ============================================================
-- DONNÉES : Catalogue Produits Câbles Prysmian
-- 40 produits réalistes du catalogue Prysmian
-- ============================================================
INSERT INTO cables (id, reference_code, designation, cable_type, status, nominal_voltage, conductor_section, conductor_material, number_of_conductors, insulation, standards, price_per_meter, stock_meters, stock_alert_threshold, factory, description) VALUES

-- === CÂBLES HAUTE TENSION (HT) ===
('c0000001-0000-0000-0000-000000000001', 'PRY-HT-240-1X', 'Câble HT 220kV 1×240mm² Alu XLPE',
 'HT', 'IN_STOCK', 220.0, 240.0, 'ALUMINUM', 1, 'XLPE', 'IEC 60840, NF C 33-250',
 185.50, 8500.0, 2000.0, 'Usine Bizerte',
 'Câble haute tension 220kV pour lignes de transport. Conducteur aluminium toronné, isolation XLPE, gaine PE.'),

('c0000002-0000-0000-0000-000000000002', 'PRY-HT-630-1X', 'Câble HT 400kV 1×630mm² Alu XLPE',
 'HT', 'IN_STOCK', 400.0, 630.0, 'ALUMINUM', 1, 'XLPE', 'IEC 62067, NF C 33-255',
 420.00, 3200.0, 1000.0, 'Usine Bizerte',
 'Câble très haute tension 400kV pour transport d''énergie longue distance. Haute fiabilité.'),

('c0000003-0000-0000-0000-000000000003', 'PRY-HT-150-3X', 'Câble HT 66kV 3×150mm² Cu XLPE',
 'HT', 'IN_PRODUCTION', 66.0, 150.0, 'COPPER', 3, 'XLPE', 'IEC 60840',
 295.00, 1200.0, 500.0, 'Usine Bizerte',
 'Câble triphasé 66kV pour distribution haute tension urbaine. Conducteur cuivre massif.'),

('c0000004-0000-0000-0000-000000000004', 'PRY-HT-400-1X', 'Câble HT 132kV 1×400mm² Alu XLPE',
 'HT', 'IN_STOCK', 132.0, 400.0, 'ALUMINUM', 1, 'XLPE', 'IEC 60840, HD 620',
 310.00, 5600.0, 1500.0, 'Usine Bizerte',
 'Câble HT 132kV pour interconnexions réseau. Isolation XLPE réticulé à sec.'),

('c0000005-0000-0000-0000-000000000005', 'PRY-HT-185-1X-SUB', 'Câble HT Sous-marin 33kV 1×185mm²',
 'HT', 'QC_HOLD', 33.0, 185.0, 'COPPER', 1, 'XLPE', 'IEC 60840, CIGRE TB 490',
 580.00, 800.0, 300.0, 'Usine Bizerte',
 'Câble sous-marin 33kV pour liaisons offshore. Armurage double couche, gaine PE renforcée.'),

-- === CÂBLES MOYENNE TENSION (MT) ===
('c0000006-0000-0000-0000-000000000006', 'PRY-MT-150-3X', 'Câble MT 20kV 3×150mm² Alu XLPE',
 'MT', 'IN_STOCK', 20.0, 150.0, 'ALUMINUM', 3, 'XLPE', 'IEC 60502-2, NF C 33-220',
 62.50, 15000.0, 3000.0, 'Usine Bizerte',
 'Câble triphasé 20kV pour réseaux de distribution MT. Référence standard Tunisia.'),

('c0000007-0000-0000-0000-000000000007', 'PRY-MT-95-3X', 'Câble MT 20kV 3×95mm² Alu XLPE',
 'MT', 'IN_STOCK', 20.0, 95.0, 'ALUMINUM', 3, 'XLPE', 'IEC 60502-2, NF C 33-220',
 42.00, 22000.0, 5000.0, 'Usine Bizerte',
 'Câble MT 20kV section 95mm² pour distribution secondaire.'),

('c0000008-0000-0000-0000-000000000008', 'PRY-MT-240-3X', 'Câble MT 20kV 3×240mm² Alu XLPE',
 'MT', 'IN_STOCK', 20.0, 240.0, 'ALUMINUM', 3, 'XLPE', 'IEC 60502-2',
 88.00, 9800.0, 2500.0, 'Usine Bizerte',
 'Câble MT forte section pour alimentation industrielle.'),

('c0000009-0000-0000-0000-000000000009', 'PRY-MT-70-3X-CU', 'Câble MT 10kV 3×70mm² Cu EPR',
 'MT', 'IN_STOCK', 10.0, 70.0, 'COPPER', 3, 'EPR', 'IEC 60502-2, BS 6622',
 75.00, 6500.0, 2000.0, 'Usine Sfax',
 'Câble MT cuivre isolation EPR pour environnements difficiles (humidité, vibrations).'),

('c0000010-0000-0000-0000-000000000010', 'PRY-MT-50-1X', 'Câble MT 6kV 1×50mm² Cu XLPE',
 'MT', 'IN_STOCK', 6.0, 50.0, 'COPPER', 1, 'XLPE', 'IEC 60502-2',
 28.50, 18000.0, 4000.0, 'Usine Sfax',
 'Câble MT unipolaire 6kV pour dérivations et câblage industriel.'),

('c0000011-0000-0000-0000-000000000011', 'PRY-MT-185-3X-ARM', 'Câble MT 30kV 3×185mm² Alu XLPE Armé',
 'MT', 'IN_PRODUCTION', 30.0, 185.0, 'ALUMINUM', 3, 'XLPE', 'IEC 60502-2, NF C 33-222',
 115.00, 4200.0, 1000.0, 'Usine Bizerte',
 'Câble MT armé 30kV avec protection mécanique renforcée pour pose en plein air ou terre.'),

('c0000012-0000-0000-0000-000000000012', 'PRY-MT-120-3X', 'Câble MT 15kV 3×120mm² Alu XLPE',
 'MT', 'IN_STOCK', 15.0, 120.0, 'ALUMINUM', 3, 'XLPE', 'IEC 60502-2',
 51.00, 13000.0, 3000.0, 'Usine Bizerte',
 'Câble MT 15kV pour distribution régionale.'),

-- === CÂBLES BASSE TENSION (BT) ===
('c0000013-0000-0000-0000-000000000013', 'PRY-BT-240-4X', 'Câble BT 1kV 4×240mm² Alu PVC',
 'BT', 'IN_STOCK', 1.0, 240.0, 'ALUMINUM', 4, 'PVC', 'IEC 60502-1, NF C 33-210',
 22.80, 45000.0, 8000.0, 'Usine Bizerte',
 'Câble tétrapolaire BT 1kV pour distribution résidentielle et tertiaire.'),

('c0000014-0000-0000-0000-000000000014', 'PRY-BT-150-4X', 'Câble BT 1kV 4×150mm² Alu PVC',
 'BT', 'IN_STOCK', 1.0, 150.0, 'ALUMINUM', 4, 'PVC', 'IEC 60502-1, NF C 33-210',
 15.40, 62000.0, 10000.0, 'Usine Bizerte',
 'Câble BT 4×150mm² pour alimentation triphasée immeubles et commerces.'),

('c0000015-0000-0000-0000-000000000015', 'PRY-BT-70-4X', 'Câble BT 1kV 4×70mm² Alu PVC',
 'BT', 'IN_STOCK', 1.0, 70.0, 'ALUMINUM', 4, 'PVC', 'IEC 60502-1',
 8.90, 85000.0, 15000.0, 'Usine Bizerte',
 'Câble BT 4×70mm² très utilisé pour branchements résidentiels.'),

('c0000016-0000-0000-0000-000000000016', 'PRY-BT-35-4X', 'Câble BT 1kV 4×35mm² Alu PVC',
 'BT', 'IN_STOCK', 1.0, 35.0, 'ALUMINUM', 4, 'PVC', 'IEC 60502-1',
 5.20, 120000.0, 20000.0, 'Usine Sfax',
 'Câble BT 4×35mm² pour réseaux basse tension enterrés.'),

('c0000017-0000-0000-0000-000000000017', 'PRY-BT-16-5X-CU', 'Câble BT 1kV 5×16mm² Cu PVC',
 'BT', 'IN_STOCK', 1.0, 16.0, 'COPPER', 5, 'PVC', 'IEC 60502-1, NF C 32-321',
 7.60, 38000.0, 8000.0, 'Usine Sfax',
 'Câble pentapolaire cuivre pour tableaux électriques industriels.'),

('c0000018-0000-0000-0000-000000000018', 'PRY-BT-10-5X-CU', 'Câble BT 1kV 5×10mm² Cu XLPE',
 'BT', 'IN_STOCK', 1.0, 10.0, 'COPPER', 5, 'XLPE', 'IEC 60502-1',
 5.80, 55000.0, 10000.0, 'Usine Sfax',
 'Câble BT cuivre 5×10mm² pour câblage industriel général.'),

('c0000019-0000-0000-0000-000000000019', 'PRY-BT-25-4X', 'Câble BT 1kV 4×25mm² Alu PVC',
 'BT', 'OUT_OF_STOCK', 1.0, 25.0, 'ALUMINUM', 4, 'PVC', 'IEC 60502-1',
 4.10, 0.0, 5000.0, 'Usine Bizerte',
 'Câble BT 4×25mm² - RUPTURE DE STOCK. Commande en cours.'),

('c0000020-0000-0000-0000-000000000020', 'PRY-BT-95-4X-ARM', 'Câble BT 1kV 4×95mm² Alu Armé PVC',
 'BT', 'IN_STOCK', 1.0, 95.0, 'ALUMINUM', 4, 'PVC', 'IEC 60502-1, NF C 33-210',
 12.50, 28000.0, 6000.0, 'Usine Bizerte',
 'Câble BT armé 4×95mm² pour pose directe en terre ou conduit.'),

-- === CÂBLES FIBRE OPTIQUE ===
('c0000021-0000-0000-0000-000000000021', 'PRY-FO-12G-ADSS', 'Câble Fibre Optique 12FO Monomode ADSS',
 'FIBER', 'IN_STOCK', NULL, NULL, NULL, 12, NULL, 'IEC 60794-1, ITU-T G.652.D',
 3.80, 95000.0, 15000.0, 'Usine Bizerte',
 'Câble fibre optique autoporté ADSS 12 fibres G.652.D pour déploiement aérien.'),

('c0000022-0000-0000-0000-000000000022', 'PRY-FO-48G-ADSS', 'Câble Fibre Optique 48FO Monomode ADSS',
 'FIBER', 'IN_STOCK', NULL, NULL, NULL, 48, NULL, 'IEC 60794-1, ITU-T G.652.D',
 8.50, 42000.0, 8000.0, 'Usine Bizerte',
 'Câble ADSS 48 fibres pour réseaux backhaul opérateurs télécoms.'),

('c0000023-0000-0000-0000-000000000023', 'PRY-FO-96G-TUBE', 'Câble Fibre Optique 96FO Tube Central',
 'FIBER', 'IN_STOCK', NULL, NULL, NULL, 96, NULL, 'IEC 60794-2',
 14.20, 18000.0, 4000.0, 'Usine Bizerte',
 'Câble FO 96 fibres tube central pour liaisons longue distance enterrées.'),

('c0000024-0000-0000-0000-000000000024', 'PRY-FO-24G-MIC', 'Câble Fibre Optique 24FO Microgaine',
 'FIBER', 'IN_PRODUCTION', NULL, NULL, NULL, 24, NULL, 'IEC 60794-3',
 2.40, 32000.0, 6000.0, 'Usine Bizerte',
 'Câble microgaine 24FO pour déploiement rapide FTTH résidentiel.'),

('c0000025-0000-0000-0000-000000000025', 'PRY-FO-288G-MEGA', 'Câble Fibre Optique 288FO Mégatube',
 'FIBER', 'IN_STOCK', NULL, NULL, NULL, 288, NULL, 'IEC 60794-2, TIA-598',
 38.00, 8500.0, 2000.0, 'Usine Bizerte',
 'Câble très haute capacité 288FO pour artères principales opérateurs.'),

-- === CÂBLES SOUS-MARINS ===
('c0000026-0000-0000-0000-000000000026', 'PRY-SM-400-3X', 'Câble Sous-marin 33kV 3×400mm² Cu XLPE',
 'SUBMARINE', 'IN_STOCK', 33.0, 400.0, 'COPPER', 3, 'XLPE', 'IEC 60840, CIGRE TB 490',
 850.00, 2200.0, 500.0, 'Usine Bizerte',
 'Câble sous-marin triphasé 33kV pour interconnexions îles/offshore. Double armurage galvanisé.'),

('c0000027-0000-0000-0000-000000000027', 'PRY-SM-630-1X', 'Câble Sous-marin 66kV 1×630mm² Cu XLPE',
 'SUBMARINE', 'IN_PRODUCTION', 66.0, 630.0, 'COPPER', 1, 'XLPE', 'IEC 60840',
 1250.00, 800.0, 200.0, 'Usine Bizerte',
 'Câble sous-marin export éolien offshore 66kV. Câble d''export pour parc éolien.'),

('c0000028-0000-0000-0000-000000000028', 'PRY-SM-FO-12-HT', 'Câble Sous-marin HT + 12FO Hybride',
 'SUBMARINE', 'IN_STOCK', 132.0, 240.0, 'COPPER', 1, 'XLPE', 'IEC 60840, IEC 60794-1',
 1680.00, 600.0, 150.0, 'Usine Bizerte',
 'Câble hybride sous-marin: puissance HT 132kV + 12 fibres optiques intégrées.'),

-- === CÂBLES SPÉCIAUX ===
('c0000029-0000-0000-0000-000000000029', 'PRY-SP-RFOU-4X', 'Câble Résistant au Feu 4×25mm² RFOU',
 'SPECIAL', 'IN_STOCK', 0.6, 25.0, 'COPPER', 4, 'XLPE', 'IEC 60331, EN 50200',
 18.50, 12000.0, 3000.0, 'Usine Sfax',
 'Câble résistant au feu 2h à 850°C pour évacuation de sécurité. Exigences SFPE.'),

('c0000030-0000-0000-0000-000000000030', 'PRY-SP-OLFLEX-7X', 'Câble Souple Blindé 7×1.5mm² OLFLEX',
 'SPECIAL', 'IN_STOCK', 0.5, 1.5, 'COPPER', 7, 'PVC', 'EN 50525, VDE 0285',
 4.20, 25000.0, 5000.0, 'Usine Sfax',
 'Câble souple blindé 7 conducteurs pour automates et robots industriels.'),

('c0000031-0000-0000-0000-000000000031', 'PRY-SP-MV-LSZH-3X', 'Câble MT LSZH 20kV 3×150mm²',
 'SPECIAL', 'IN_STOCK', 20.0, 150.0, 'ALUMINUM', 3, 'XLPE', 'IEC 60502-2, IEC 60754',
 98.00, 6800.0, 1500.0, 'Usine Bizerte',
 'Câble MT sans halogène zéro fumée (LSZH) pour tunnels et espaces confinés.'),

('c0000032-0000-0000-0000-000000000032', 'PRY-SP-TREX-6X', 'Câble Torsadé BT 6×10mm² T-Rex',
 'SPECIAL', 'IN_STOCK', 0.6, 10.0, 'COPPER', 6, 'EPR', 'IEC 60245, HD 22',
 9.80, 18000.0, 4000.0, 'Usine Sfax',
 'Câble souple haute température EPR pour fours industriels et applications chauffantes.'),

('c0000033-0000-0000-0000-000000000033', 'PRY-SP-STEEL-4X', 'Câble Acier Inoxydable 4×35mm²',
 'SPECIAL', 'IN_STOCK', 1.0, 35.0, 'COPPER', 4, 'XLPE', 'IEC 60502-1, ATEX',
 35.00, 8500.0, 2000.0, 'Usine Sfax',
 'Câble armé acier inox pour zones ATEX (explosibles). Pétrochimie et offshore.'),

('c0000034-0000-0000-0000-000000000034', 'PRY-SP-CCV-150-1X', 'Câble CCV 10kV 1×150mm² Cu',
 'SPECIAL', 'DISCONTINUED', 10.0, 150.0, 'COPPER', 1, 'PVC', 'NF C 33-250 (ancien)',
 55.00, 200.0, 500.0, 'Usine Bizerte',
 'RÉFÉRENCE DISCONTINUÉE. Remplacée par PRY-MT-150-3X. Stock résiduel.'),

('c0000035-0000-0000-0000-000000000035', 'PRY-SP-MINING-6X', 'Câble Minier Souple 6×25mm²',
 'SPECIAL', 'IN_STOCK', 3.6, 25.0, 'COPPER', 6, 'EPR', 'IEC 60245-4, EN 50265',
 42.00, 5500.0, 1200.0, 'Usine Sfax',
 'Câble minier souple pour engins mobiles. Résistant aux huiles, aux chocs mécaniques.'),

-- === CÂBLES HT SUPPLÉMENTAIRES ===
('c0000036-0000-0000-0000-000000000036', 'PRY-HT-300-1X', 'Câble HT 90kV 1×300mm² Alu XLPE',
 'HT', 'IN_STOCK', 90.0, 300.0, 'ALUMINUM', 1, 'XLPE', 'IEC 60840',
 238.00, 4800.0, 1200.0, 'Usine Bizerte',
 'Câble HT 90kV section 300mm² pour liaisons sous-teraines.'),

('c0000037-0000-0000-0000-000000000037', 'PRY-MT-50-3X', 'Câble MT 6kV 3×50mm² Cu XLPE',
 'MT', 'IN_STOCK', 6.0, 50.0, 'COPPER', 3, 'XLPE', 'IEC 60502-2',
 38.00, 11000.0, 2500.0, 'Usine Sfax',
 'Câble MT triphasé 6kV pour alimentation moteurs et transformateurs.'),

('c0000038-0000-0000-0000-000000000038', 'PRY-BT-400-4X', 'Câble BT 1kV 4×400mm² Alu PVC',
 'BT', 'IN_STOCK', 1.0, 400.0, 'ALUMINUM', 4, 'PVC', 'IEC 60502-1, NF C 33-210',
 38.00, 15000.0, 4000.0, 'Usine Bizerte',
 'Câble BT très forte section pour postes sources et tableaux généraux.'),

('c0000039-0000-0000-0000-000000000039', 'PRY-FO-6G-FTTH', 'Câble Fibre Optique 6FO Drop FTTH',
 'FIBER', 'IN_STOCK', NULL, NULL, NULL, 6, NULL, 'IEC 60794-3-10, ITU-T G.657.A2',
 0.95, 250000.0, 40000.0, 'Usine Bizerte',
 'Câble drop FTTH 6 fibres G.657.A2 pour raccordement abonnés. Très flexible.'),

('c0000040-0000-0000-0000-000000000040', 'PRY-SP-WIND-3X', 'Câble Éolien Flexible 3×95mm² Torsadé',
 'SPECIAL', 'IN_PRODUCTION', 1.8, 95.0, 'COPPER', 3, 'EPR', 'IEC 60245-4, GL 2010',
 88.00, 3200.0, 800.0, 'Usine Bizerte',
 'Câble de nacelle éolienne torsadé, résistant aux torsions infinies. Certifié GL/DNV.');

-- ============================================================
-- DONNÉES : Ordres de Production (maintenance_logs)
-- ============================================================
INSERT INTO maintenance_logs (id, cable_id, technician_id, maintenance_type, order_number, target_length_meters, produced_length_meters, description, start_date, end_date, cost, result_status) VALUES
('m0000001-0000-0000-0000-000000000001', 'c0000001-0000-0000-0000-000000000001', 'u1000000-0000-0000-0000-000000000003', 'EXTRUSION', 'OF-2024-0001', 5000.0, 4980.0, 'Production lot PRY-HT-240-1X pour STEG commande #C2024-0891', '2024-01-10 08:00:00', '2024-01-18 17:00:00', 185000.0, 'DONE'),
('m0000002-0000-0000-0000-000000000002', 'c0000006-0000-0000-0000-000000000006', 'u1000000-0000-0000-0000-000000000003', 'STRANDING', 'OF-2024-0002', 10000.0, 10000.0, 'Fabrication câble MT 20kV 3×150mm² Alu pour SONEDE', '2024-01-15 08:00:00', '2024-01-25 17:00:00', 62500.0, 'DONE'),
('m0000003-0000-0000-0000-000000000003', 'c0000013-0000-0000-0000-000000000013', 'u1000000-0000-0000-0000-000000000004', 'EXTRUSION', 'OF-2024-0003', 20000.0, 20000.0, 'Grande série BT 4×240mm² pour marché immobilier Sfax', '2024-02-01 08:00:00', '2024-02-20 17:00:00', 45600.0, 'DONE'),
('m0000004-0000-0000-0000-000000000004', 'c0000021-0000-0000-0000-000000000021', 'u1000000-0000-0000-0000-000000000003', 'PACKAGING', 'OF-2024-0004', 50000.0, 49500.0, 'Bobinage ADSS 12FO pour opérateur Tunisie Télécom', '2024-02-05 08:00:00', '2024-02-28 17:00:00', 19000.0, 'DONE'),
('m0000005-0000-0000-0000-000000000005', 'c0000026-0000-0000-0000-000000000026', 'u1000000-0000-0000-0000-000000000003', 'ARMORING', 'OF-2024-0005', 1000.0, 980.0, 'Câble sous-marin 33kV pour liaison Djerba-continent', '2024-03-01 08:00:00', '2024-04-15 17:00:00', 850000.0, 'DONE'),
('m0000006-0000-0000-0000-000000000006', 'c0000003-0000-0000-0000-000000000003', 'u1000000-0000-0000-0000-000000000003', 'EXTRUSION', 'OF-2024-0006', 2000.0, NULL, 'Production HT 66kV triphasé pour interconnexion nord Tunisie', '2024-09-01 08:00:00', NULL, 98000.0, 'IN_PROGRESS'),
('m0000007-0000-0000-0000-000000000007', 'c0000011-0000-0000-0000-000000000007', 'u1000000-0000-0000-0000-000000000004', 'ARMORING', 'OF-2024-0007', 3000.0, NULL, 'Câble MT 30kV armé pour réseau enterré Grand Tunis', '2024-09-02 08:00:00', NULL, 68500.0, 'IN_PROGRESS'),
('m0000008-0000-0000-0000-000000000008', 'c0000024-0000-0000-0000-000000000024', 'u1000000-0000-0000-0000-000000000003', 'PACKAGING', 'OF-2024-0008', 15000.0, NULL, 'Microgaines FTTH 24FO pour OOREDOO', '2024-09-03 08:00:00', NULL, 36000.0, 'IN_PROGRESS'),
('m0000009-0000-0000-0000-000000000009', 'c0000027-0000-0000-0000-000000000027', 'u1000000-0000-0000-0000-000000000003', 'ARMORING', 'OF-2024-0009', 500.0, NULL, 'Export offshore éolien 66kV 1×630mm²', '2024-09-01 08:00:00', NULL, 625000.0, 'QC_CHECK'),
('m0000010-0000-0000-0000-000000000010', 'c0000029-0000-0000-0000-000000000029', 'u1000000-0000-0000-0000-000000000004', 'EXTRUSION', 'OF-2024-0010', 5000.0, NULL, 'Câbles résistants feu RFOU pour aéroport Tunis-Carthage', '2024-08-20 08:00:00', NULL, 18500.0, 'PLANNED'),
('m0000011-0000-0000-0000-000000000011', 'c0000014-0000-0000-0000-000000000014', 'u1000000-0000-0000-0000-000000000004', 'STRANDING', 'OF-2024-0011', 8000.0, 7500.0, 'Production BT 4×150mm² lot rejeté (défaut extrusion)', '2024-07-01 08:00:00', '2024-07-10 17:00:00', 12320.0, 'REJECTED'),
('m0000012-0000-0000-0000-000000000012', 'c0000033-0000-0000-0000-000000000033', 'u1000000-0000-0000-0000-000000000004', 'TESTING', 'OF-2024-0012', 3000.0, 2900.0, 'Tests ATEX câble acier inox zone pétrolière', '2024-08-01 08:00:00', '2024-08-15 17:00:00', 10500.0, 'DONE'),
('m0000013-0000-0000-0000-000000000013', 'c0000022-0000-0000-0000-000000000022', 'u1000000-0000-0000-0000-000000000003', 'PACKAGING', 'OF-2024-0013', 20000.0, 20000.0, 'ADSS 48FO pour backbone 4G Tunisie', '2024-06-10 08:00:00', '2024-07-05 17:00:00', 17000.0, 'DONE'),
('m0000014-0000-0000-0000-000000000014', 'c0000040-0000-0000-0000-000000000040', 'u1000000-0000-0000-0000-000000000004', 'EXTRUSION', 'OF-2024-0014', 2000.0, NULL, 'Câbles éoliens flexibles nacelle pour WindTech SA', '2024-09-02 08:00:00', NULL, 17600.0, 'IN_PROGRESS');

-- ============================================================
-- DONNÉES : Alertes Usine
-- ============================================================
INSERT INTO alerts (id, cable_id, alert_type, severity, message, status) VALUES
('a0000001-0000-0000-0000-000000000001', 'c0000019-0000-0000-0000-000000000019', 'LOW_STOCK', 'CRITICAL', 'Rupture de stock totale — PRY-BT-25-4X : 0m disponibles. Commande urgente requise.', 'OPEN'),
('a0000002-0000-0000-0000-000000000002', 'c0000005-0000-0000-0000-000000000005', 'QC_FAILURE', 'HIGH', 'Câble PRY-HT-185-1X-SUB : test de tension claquage non conforme (58kV vs 60kV requis). Lot en QC_HOLD.', 'OPEN'),
('a0000003-0000-0000-0000-000000000003', 'c0000011-0000-0000-0000-000000000007', 'PRODUCTION_DELAY', 'MEDIUM', 'OF-2024-0007 en retard de 3 jours sur planning. Panne toronneuse L2 ce matin.', 'OPEN'),
('a0000004-0000-0000-0000-000000000004', 'c0000027-0000-0000-0000-000000000027', 'QC_FAILURE', 'HIGH', 'OF-2024-0009 : test d''étanchéité sous-marin non concluant. Armurage à reprendre.', 'ACKNOWLEDGED'),
('a0000005-0000-0000-0000-000000000005', 'c0000034-0000-0000-0000-000000000034', 'LOW_STOCK', 'LOW', 'Référence discontinuée PRY-SP-CCV-150-1X : stock résiduel 200m. Aucun réapprovisionnement prévu.', 'OPEN'),
('a0000006-0000-0000-0000-000000000006', 'c0000014-0000-0000-0000-000000000014', 'QC_FAILURE', 'CRITICAL', 'Lot OF-2024-0011 rejeté : défaut extrusion 7500m détecté. Rapport non-conformité émis.', 'OPEN'),
('a0000007-0000-0000-0000-000000000007', 'c0000003-0000-0000-0000-000000000003', 'EQUIPMENT_FAULT', 'HIGH', 'Presse d''extrusion EXT-03 en panne : joint d''étanchéité à remplacer. OF-2024-0006 suspendu 6h.', 'OPEN'),
('a0000008-0000-0000-0000-000000000008', 'c0000040-0000-0000-0000-000000000040', 'URGENT_ORDER', 'MEDIUM', 'Commande urgente WindTech SA : 2000m câble éolien PRY-SP-WIND-3X avant le 15/09/2024.', 'ACKNOWLEDGED');

-- ============================================================
-- DONNÉES : Prédictions Qualité ML
-- ============================================================
INSERT INTO ml_predictions (id, cable_id, prediction_type, predicted_date, confidence_score, maintenance_urgency, reason, model_version) VALUES
('p0000001-0000-0000-0000-000000000001', 'c0000001-0000-0000-0000-000000000001', 'MAINTENANCE_NEEDED', DATE_ADD(NOW(), INTERVAL 45 DAY), 87.5, 72.0, 'Usure conducteur XLPE - révision annuelle recommandée', '2.4'),
('p0000002-0000-0000-0000-000000000002', 'c0000005-0000-0000-0000-000000000005', 'MAINTENANCE_NEEDED', DATE_ADD(NOW(), INTERVAL 7 DAY), 95.0, 91.0, 'Lot QC_HOLD : test tension claquage échoué', '2.4'),
('p0000003-0000-0000-0000-000000000003', 'c0000027-0000-0000-0000-000000000027', 'MAINTENANCE_NEEDED', DATE_ADD(NOW(), INTERVAL 14 DAY), 92.0, 85.0, 'Reprise armurage sous-marin - contrôle étanchéité', '2.4'),
('p0000004-0000-0000-0000-000000000004', 'c0000014-0000-0000-0000-000000000014', 'MAINTENANCE_NEEDED', DATE_ADD(NOW(), INTERVAL 3 DAY), 98.0, 97.0, 'Lot rejeté - reprise production obligatoire', '2.4'),
('p0000005-0000-0000-0000-000000000005', 'c0000006-0000-0000-0000-000000000006', 'MAINTENANCE_NEEDED', DATE_ADD(NOW(), INTERVAL 90 DAY), 75.0, 58.0, 'Contrôle routine annuel isolant XLPE', '2.4');
