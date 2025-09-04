<?php
// Website_Application_security/employees.php
require __DIR__.'/_db.php';

// Router simple
$action = $_GET['action'] ?? 'list';

// CREATE
if ($action === 'create' && $_SERVER['REQUEST_METHOD']==='POST') {
  $st = $pdo->prepare("INSERT INTO Emp (nomEmp, salEmp, mgrEmp, deptEmp) VALUES (?,?,?,?)");
  $mgr = ($_POST['mgrEmp'] === '' ? null : (int)$_POST['mgrEmp']);
  $st->execute([$_POST['nomEmp'], (float)$_POST['salEmp'], $mgr, $_POST['deptEmp']]);
  header("Location: employees.php?msg=Employé créé"); exit;
}

// UPDATE
if ($action === 'edit' && $_SERVER['REQUEST_METHOD']==='POST') {
  $st = $pdo->prepare("UPDATE Emp SET nomEmp=?, salEmp=?, mgrEmp=?, deptEmp=? WHERE idEmp=?");
  $mgr = ($_POST['mgrEmp'] === '' ? null : (int)$_POST['mgrEmp']);
  $st->execute([$_POST['nomEmp'], (float)$_POST['salEmp'], $mgr, $_POST['deptEmp'], (int)$_POST['idEmp']]);
  header("Location: employees.php?msg=Employé mis à jour"); exit;
}

// DELETE
if ($action === 'delete' && $_SERVER['REQUEST_METHOD']==='POST') {
  $st = $pdo->prepare("DELETE FROM Emp WHERE idEmp=?");
  $st->execute([(int)$_POST['idEmp']]);
  header("Location: employees.php?msg=Employé supprimé"); exit;
}

// DATA pour formulaires
$editRow = null;
if ($action === 'edit' && isset($_GET['id'])) {
  $st = $pdo->prepare("SELECT * FROM Emp WHERE idEmp=?");
  $st->execute([(int)$_GET['id']]);
  $editRow = $st->fetch();
  if (!$editRow) { header("Location: employees.php?msg=Introuvable"); exit; }
}

// Liste (par défaut)
$employees = $pdo->query("SELECT * FROM Emp ORDER BY nomEmp")->fetchAll();
$managers  = $pdo->query("SELECT idEmp, nomEmp FROM Emp ORDER BY nomEmp")->fetchAll();
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Employés — CRUD</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>body{background:#f6f7fb}</style>
</head>
<body>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 m-0">Employés</h1>
    <a href="index.php" class="btn btn-outline-secondary">Accueil</a>
  </div>
  <?php if (!empty($_GET['msg'])): ?>
    <div class="alert alert-success"><?=h($_GET['msg'])?></div>
  <?php endif; ?>

  <!-- Formulaire Create / Edit -->
  <div class="card mb-4">
    <div class="card-body">
      <h2 class="h6 mb-3"><?= $editRow ? 'Modifier un employé' : 'Ajouter un employé' ?></h2>
      <form method="post" action="employees.php?action=<?= $editRow ? 'edit' : 'create' ?>">
        <?php if ($editRow): ?>
          <input type="hidden" name="idEmp" value="<?=h($editRow['idEmp'])?>">
        <?php endif; ?>
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Nom</label>
            <input required name="nomEmp" class="form-control" value="<?=h($editRow['nomEmp'] ?? '')?>">
          </div>
          <div class="col-md-2">
            <label class="form-label">Salaire (€)</label>
            <input required type="number" step="0.01" name="salEmp" class="form-control" value="<?=h($editRow['salEmp'] ?? '')?>">
          </div>
          <div class="col-md-3">
            <label class="form-label">Manager (idEmp)</label>
            <input list="mgrs" name="mgrEmp" class="form-control" value="<?=h($editRow['mgrEmp'] ?? '')?>">
            <datalist id="mgrs">
              <?php foreach ($managers as $m): ?>
                <option value="<?=h($m['idEmp'])?>"><?=h($m['nomEmp'])?></option>
              <?php endforeach; ?>
            </datalist>
            <div class="form-text">Laisser vide si aucun</div>
          </div>
          <div class="col-md-3">
            <label class="form-label">Département</label>
            <input required name="deptEmp" class="form-control" value="<?=h($editRow['deptEmp'] ?? '')?>">
          </div>
        </div>
        <div class="mt-3">
          <button class="btn btn-dark" type="submit"><?= $editRow ? 'Mettre à jour' : 'Ajouter' ?></button>
          <?php if ($editRow): ?>
            <a class="btn btn-outline-secondary" href="employees.php">Annuler</a>
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
          <tr><th>ID</th><th>Nom</th><th>Dept</th><th>Salaire</th><th>Mgr</th><th></th></tr>
        </thead>
        <tbody>
        <?php foreach ($employees as $e): ?>
          <tr>
            <td><?=h($e['idEmp'])?></td>
            <td class="fw-semibold"><?=h($e['nomEmp'])?></td>
            <td><?=h($e['deptEmp'])?></td>
            <td><?=number_format((float)$e['salEmp'], 2, ',', ' ')?> €</td>
            <td><?=h($e['mgrEmp'])?></td>
            <td class="text-end">
              <a class="btn btn-sm btn-outline-primary" href="employees.php?action=edit&id=<?=h($e['idEmp'])?>">Éditer</a>
              <form class="d-inline" method="post" action="employees.php?action=delete" onsubmit="return confirm('Supprimer cet employé ?');">
                <input type="hidden" name="idEmp" value="<?=h($e['idEmp'])?>">
                <button class="btn btn-sm btn-outline-danger">Supprimer</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$employees): ?>
          <tr><td colspan="6" class="p-3 text-muted">Aucun employé.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</body>
</html>
