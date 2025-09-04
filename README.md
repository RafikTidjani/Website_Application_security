# Website_Application_security

Mini-application web en **PHP + PDO + Bootstrap** pour gérer :

- Les **employés** (CRUD)
- Les **projets** (CRUD)
- Les **affectations** Employé ↔ Projet (CRUD)
- Un **dashboard d’accueil** avec statistiques et vues globales

---

## ⚙️ Prérequis

- Serveur web local : **XAMPP**, **WAMP** ou **MAMP** (Apache + PHP + MySQL/MariaDB)
- PHP 8+
- Navigateur web moderne

---

## 📥 Installation

1. **Cloner le projet**
   ```bash
   git clone https://github.com/votre-utilisateur/Website_Application_security.git
Déplacer le dossier dans le répertoire web local

Sous XAMPP → C:\xampp\htdocs\Website_Application_security

Sous WAMP → C:\wamp64\www\Website_Application_security

Créer la base de données et insérer les données

Ouvrir phpMyAdmin

Créer une base vide bdd_securisation

Dans l’onglet SQL, coller le script ci-dessous et exécuter :

sql
Copier le code
-- Création de la base
CREATE DATABASE IF NOT EXISTS bdd_securisation
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

USE bdd_securisation;

-- ========================
-- Table Employés
-- ========================
DROP TABLE IF EXISTS Emp;
CREATE TABLE Emp (
    idEmp INT AUTO_INCREMENT PRIMARY KEY,
    nomEmp VARCHAR(100) NOT NULL,
    salEmp DECIMAL(10,2) NOT NULL,
    mgrEmp INT NULL,
    deptEmp VARCHAR(100) NOT NULL,
    FOREIGN KEY (mgrEmp) REFERENCES Emp(idEmp) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ========================
-- Table Projets
-- ========================
DROP TABLE IF EXISTS Proj;
CREATE TABLE Proj (
    nomProj VARCHAR(100) PRIMARY KEY,
    mgrProj INT NOT NULL,
    budget DECIMAL(12,2) NOT NULL,
    dateDebut DATE NOT NULL,
    FOREIGN KEY (mgrProj) REFERENCES Emp(idEmp) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ========================
-- Table Affectations
-- ========================
DROP TABLE IF EXISTS EmpProj;
CREATE TABLE EmpProj (
    idEmp INT NOT NULL,
    nomProj VARCHAR(100) NOT NULL,
    heures DECIMAL(10,2) NOT NULL,
    evalEmp INT NULL,
    PRIMARY KEY (idEmp, nomProj),
    FOREIGN KEY (idEmp) REFERENCES Emp(idEmp) ON DELETE CASCADE,
    FOREIGN KEY (nomProj) REFERENCES Proj(nomProj) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ========================
-- Jeu de données : Employés
-- ========================
INSERT INTO Emp (nomEmp, salEmp, mgrEmp, deptEmp) VALUES
('Alice Martin', 3200.00, NULL, 'Informatique'),
('Bruno Dupont', 3500.00, 1, 'Informatique'),
('Claire Durand', 3000.00, 2, 'Informatique'),
('David Bernard', 2800.00, 2, 'Finance'),
('Emma Leroy', 4000.00, NULL, 'Finance'),
('François Petit', 2600.00, 5, 'RH'),
('Gérard Noël', 2900.00, 5, 'RH'),
('Hélène Caron', 2700.00, 1, 'Support'),
('Isabelle Fabre', 3100.00, 2, 'Support'),
('Jacques Moreau', 3300.00, NULL, 'Marketing'),
('Karim Lemaire', 2500.00, 10, 'Marketing'),
('Laura Roche', 2800.00, 10, 'Marketing'),
('Marc Garnier', 2600.00, 1, 'Informatique'),
('Nina Colin', 2400.00, 2, 'Support'),
('Olivier Henry', 2900.00, 5, 'Finance'),
('Pauline Lefevre', 3100.00, 2, 'Informatique'),
('Quentin Rousseau', 2700.00, 2, 'Informatique'),
('Raphaël Simon', 3300.00, NULL, 'RH'),
('Sophie Lambert', 2600.00, 18, 'RH'),
('Thomas Blanchard', 3500.00, 2, 'Informatique');

-- ========================
-- Jeu de données : Projets
-- ========================
INSERT INTO Proj (nomProj, mgrProj, budget, dateDebut) VALUES
('Migration ERP', 2, 50000.00, '2024-01-15'),
('Audit Financier', 5, 30000.00, '2024-03-01'),
('Campagne Marketing Q2', 10, 20000.00, '2024-04-10'),
('CRM Client', 2, 45000.00, '2024-05-05'),
('SIRH Update', 5, 25000.00, '2024-06-20'),
('Formation Sécurité', 1, 15000.00, '2024-02-01'),
('Application Mobile', 2, 40000.00, '2024-07-01'),
('Réseau Interne', 1, 18000.00, '2024-08-15'),
('Migration Cloud', 2, 60000.00, '2024-09-01'),
('Site E-commerce', 10, 55000.00, '2024-10-01');

-- ========================
-- Jeu de données : Affectations
-- ========================
INSERT INTO EmpProj (idEmp, nomProj, heures, evalEmp) VALUES
(2, 'Migration ERP', 120.50, 85),
(3, 'Migration ERP', 95.00, 90),
(4, 'Audit Financier', 140.00, 88),
(5, 'Audit Financier', 100.00, 80),
(10, 'Campagne Marketing Q2', 160.00, 75),
(11, 'Campagne Marketing Q2', 120.00, 78),
(14, 'CRM Client', 150.00, 92),
(15, 'CRM Client', 130.00, 85),
(16, 'CRM Client', 100.00, 79),
(6, 'SIRH Update', 180.00, 87),
(7, 'SIRH Update', 110.00, 82),
(8, 'Formation Sécurité', 90.00, 89),
(17, 'Formation Sécurité', 75.00, 84),
(18, 'CRM Client', 140.00, 91),
(19, 'Application Mobile', 100.00, 76),
(13, 'Application Mobile', 95.00, 83),
(20, 'Réseau Interne', 105.00, 77),
(2, 'Migration Cloud', 160.00, 90),
(3, 'Migration Cloud', 150.00, 88),
(17, 'Site E-commerce', 200.00, 95),
(11, 'Site E-commerce', 140.00, 87);
Configurer la connexion
Dans le fichier _db.php, ajuster si besoin :

php
Copier le code
$DB_HOST = 'localhost';
$DB_PORT = '3306';
$DB_NAME = 'bdd_securisation';
$DB_USER = 'root';
$DB_PASS = ''; // mot de passe (souvent vide sous XAMPP/WAMP)
🚀 Utilisation
Lancer Apache + MySQL depuis XAMPP/WAMP/MAMP

Ouvrir dans le navigateur :
👉 http://localhost/Website_Application_security/index.php

Tu arrives sur le dashboard avec :

Les statistiques clés (employés, projets, heures, évaluations…)

Des boutons pour accéder aux CRUD :

employees.php → gérer les employés

projects.php → gérer les projets

assignments.php → gérer les affectations

projects_view.php → consulter les projets en cours

📂 Structure
index.php → Dashboard + liens

employees.php → CRUD employés

projects.php → CRUD projets

assignments.php → CRUD affectations

projects_view.php → consultation projets en cours

_db.php → connexion PDO


Les relations sont sécurisées par clés étrangères (supprimer un employé supprime ses affectations, etc.).

Le jeu de données contient :

20 employés

10 projets

20+ affectations

Les formulaires utilisent des requêtes préparées (sécurité contre injection SQL).
