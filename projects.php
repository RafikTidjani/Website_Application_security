<?php
// Website_Application_security/projects.php
require __DIR__.'/_db.php';

$action = $_GET['action'] ?? 'list';

// CREATE
if ($action === 'create' && $_SERVER['REQUEST_METHOD']==='POST') {
  $st = $pdo->prepare("INSERT INTO Proj (nomProj, mgrProj, budget, dateDebut) VALUES (?,?,?,?)");
  $st->execute([$_POST['nomProj'], (int)$_POST['mgrProj'], (float)$_POST['budget'], $_POST['dateDebut']]);
  header("Location: projects.php?msg=Projet créé"); exit;
}

// UPDATE
if ($action === 'edit' && $_SERVER['REQUEST_METHOD']==='POST') {
  $st = $pdo->prepare("UPDATE Proj SET mgrProj=?, budget=?, dateDebut=? WHERE nomProj=?");
  $st->execute([(int)$_POST['mgrProj'], (float)$_POST['budget'], $_POST['dateDebut'], $_POST['nomProj']]);
  header("Location: projects.php?msg=Projet mis à jour"); exit;
}

// DELETE
if ($action === 'delete' && $_SERVER['REQUEST_METHOD']==='POST') {
  $st = $pdo->prepare("DELETE FROM Proj WHERE nomProj=?");
  $st->execute([$_POST['nomProj']]);
  header("Location: projects.php?msg=Projet supprimé"); exit;
}

// DATA
$editRow = null;
if ($action === 'edit' && isset($_GET['nom'])) {
  $st = $pdo->prepare("SELECT * FROM Proj WHERE nomProj=?");
  $st->execute([$_GET['nom']]);
  $editRow = $st->fetch();
  if (!$editRow) { header("Location: projects.php?msg=Introuvable"); exit; }
}

$projects = $pdo->query("SELECT * FROM Proj ORDER BY dateDebut DESC")->fetchAll();
$employees = $pdo->query("SELECT idEmp, nomEmp FROM Emp ORDER BY nomEmp")->fetchAll();
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Projets — CRUD</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>body{background:#f6f7fb}</style>
</head>
<body>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 m-0">Projets</h1>
    <a href="index.php" class="btn btn-outline-secondary">Accueil</a>
  </div>
  <?php if (!empty($_GET['msg'])): ?>
    <div class="alert alert-success"><?=h($_GET['msg'])?></div>
  <?php endif; ?>

  <!-- Formulaire Create / Edit -->
  <div class="card mb-4">
    <div class="card-body">
      <h2 class="h6 mb-3"><?= $editRow ? 'Modifier un projet' : 'Ajouter un projet' ?></h2>
      <form method="post" action="projects.php?action=<?= $editRow ? 'edit' : 'create' ?>">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Nom du projet (clé)</label>
            <input required name="nomProj" class="form-control" value="<?=h($editRow['nomProj'] ?? '')?>" <?= $editRow?'readonly':'' ?>>
            <?php if ($editRow): ?><div class="form-text">La clé ne peut pas être modifiée.</div><?php endif; ?>
          </div>
          <div class="col-md-3">
            <label class="form-label">Manager (idEmp)</label>
            <input list="emps" required name="mgrProj" class="form-control" value="<?=h($editRow['mgrProj'] ?? '')?>">
            <datalist id="emps">
              <?php foreach ($employees as $e): ?>
                <option value="<?=h($e['idEmp'])?>"><?=h($e['nomEmp'])?></option>
              <?php endforeach; ?>
            </datalist>
          </div>
          <div class="col-md-2">
            <label class="form-label">Budget (€)</label>
            <input required type="number" step="0.01" name="budget" class="form-control" value="<?=h($editRow['budget'] ?? '')?>">
          </div>
          <div class="col-md-3">
            <label class="form-label">Date de début</label>
            <input required type="date" name="dateDebut" class="form-control" value="<?=h($editRow['dateDebut'] ?? '')?>">
          </div>
        </div>
        <div class="mt-3">
          <button class="btn btn-dark" type="submit"><?= $editRow ? 'Mettre à jour' : 'Ajouter' ?></button>
          <?php if ($editRow): ?>
            <a class="btn btn-outline-secondary" href="projects.php">Annuler</a>
          <?php endif; ?>
        </div>
      </form>
    </div>
  </div>

  <!-- Tableau -->
  <div class="card">
    <div class="table-responsive">
      <table class="table mb-0 align-middle">
        <thead class="table-light">
          <tr><th>Nom</th><th>Mgr (idEmp)</th><th>Budget</th><th>Début</th><th></th></tr>
        </thead>
        <tbody>
        <?php foreach ($projects as $p): ?>
          <tr>
            <td class="fw-semibold"><?=h($p['nomProj'])?></td>
            <td><?=h($p['mgrProj'])?></td>
            <td><?=number_format((float)$p['budget'], 2, ',', ' ')?> €</td>
            <td><?=h(date('d/m/Y', strtotime($p['dateDebut'])))?></td>
            <td class="text-end">
              <a class="btn btn-sm btn-outline-primary" href="projects.php?action=edit&nom=<?=urlencode($p['nomProj'])?>">Éditer</a>
              <form class="d-inline" method="post" action="projects.php?action=delete" onsubmit="return confirm('Supprimer ce projet ?');">
                <input type="hidden" name="nomProj" value="<?=h($p['nomProj'])?>">
                <button class="btn btn-sm btn-outline-danger">Supprimer</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$projects): ?>
          <tr><td colspan="5" class="p-3 text-muted">Aucun projet.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</body>
</html>
