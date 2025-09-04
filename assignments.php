<?php
// Website_Application_security/assignments.php
require __DIR__.'/_db.php';

$action = $_GET['action'] ?? 'list';

/* ==== CREATE ==== */
if ($action === 'create' && $_SERVER['REQUEST_METHOD']==='POST') {
  $st = $pdo->prepare("INSERT INTO EmpProj (idEmp, nomProj, heures, evalEmp) VALUES (?,?,?,?)");
  $eval = ($_POST['evalEmp'] === '' ? null : (int)$_POST['evalEmp']);
  try {
    $st->execute([(int)$_POST['idEmp'], $_POST['nomProj'], (float)$_POST['heures'], $eval]);
    header("Location: assignments.php?msg=Affectation créée"); exit;
  } catch (Throwable $e) {
    header("Location: assignments.php?err=".urlencode($e->getMessage())); exit;
  }
}

/* ==== UPDATE ==== */
if ($action === 'edit' && $_SERVER['REQUEST_METHOD']==='POST') {
  // PK composite => on ne modifie pas les clés, seulement heures & éval
  $st = $pdo->prepare("UPDATE EmpProj SET heures=?, evalEmp=? WHERE idEmp=? AND nomProj=?");
  $eval = ($_POST['evalEmp'] === '' ? null : (int)$_POST['evalEmp']);
  try {
    $st->execute([(float)$_POST['heures'], $eval, (int)$_POST['idEmp'], $_POST['nomProj']]);
    header("Location: assignments.php?msg=Affectation mise à jour"); exit;
  } catch (Throwable $e) {
    header("Location: assignments.php?err=".urlencode($e->getMessage())); exit;
  }
}

/* ==== DELETE ==== */
if ($action === 'delete' && $_SERVER['REQUEST_METHOD']==='POST') {
  $st = $pdo->prepare("DELETE FROM EmpProj WHERE idEmp=? AND nomProj=?");
  try {
    $st->execute([(int)$_POST['idEmp'], $_POST['nomProj']]);
    header("Location: assignments.php?msg=Affectation supprimée"); exit;
  } catch (Throwable $e) {
    header("Location: assignments.php?err=".urlencode($e->getMessage())); exit;
  }
}

/* ==== DATA ==== */
// pour formulaires
$employees = $pdo->query("SELECT idEmp, nomEmp FROM Emp ORDER BY nomEmp")->fetchAll();
$projects  = $pdo->query("SELECT nomProj FROM Proj ORDER BY nomProj")->fetchAll();

// pour edit line
$editRow = null;
if ($action === 'edit' && isset($_GET['idEmp'], $_GET['nomProj'])) {
  $st = $pdo->prepare("
    SELECT ep.idEmp, e.nomEmp, ep.nomProj, ep.heures, ep.evalEmp
    FROM EmpProj ep
    JOIN Emp e ON e.idEmp = ep.idEmp
    WHERE ep.idEmp=? AND ep.nomProj=?
  ");
  $st->execute([(int)$_GET['idEmp'], $_GET['nomProj']]);
  $editRow = $st->fetch();
  if (!$editRow) { header("Location: assignments.php?err=Introuvable"); exit; }
}

// liste
$q = trim($_GET['q'] ?? '');
if ($q !== '') {
  $st = $pdo->prepare("
    SELECT ep.idEmp, e.nomEmp, ep.nomProj, ep.heures, ep.evalEmp
    FROM EmpProj ep
    JOIN Emp e  ON e.idEmp = ep.idEmp
    JOIN Proj p ON p.nomProj = ep.nomProj
    WHERE e.nomEmp LIKE :q OR ep.nomProj LIKE :q
    ORDER BY ep.nomProj, e.nomEmp
    LIMIT 500
  ");
  $st->execute([':q' => '%'.str_replace(['%','_'],['\\%','\\_'],$q).'%']);
  $rows = $st->fetchAll();
} else {
  $rows = $pdo->query("
    SELECT ep.idEmp, e.nomEmp, ep.nomProj, ep.heures, ep.evalEmp
    FROM EmpProj ep
    JOIN Emp e  ON e.idEmp = ep.idEmp
    JOIN Proj p ON p.nomProj = ep.nomProj
    ORDER BY ep.nomProj, e.nomEmp
    LIMIT 500
  ")->fetchAll();
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Affectations (EmpProj) — CRUD</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>body{background:#f6f7fb}</style>
</head>
<body>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 m-0">Affectations (Emp ↔ Proj)</h1>
    <div class="d-flex gap-2">
      <a href="index.php" class="btn btn-outline-secondary">Accueil</a>
      <a href="employees.php" class="btn btn-outline-secondary">Employés</a>
      <a href="projects.php" class="btn btn-outline-secondary">Projets</a>
    </div>
  </div>

  <?php if (!empty($_GET['msg'])): ?>
    <div class="alert alert-success"><?=h($_GET['msg'])?></div>
  <?php endif; ?>
  <?php if (!empty($_GET['err'])): ?>
    <div class="alert alert-danger"><strong>Erreur :</strong> <?=h($_GET['err'])?></div>
  <?php endif; ?>

  <!-- Form Create / Edit -->
  <div class="card mb-4">
    <div class="card-body">
      <h2 class="h6 mb-3"><?= $editRow ? 'Modifier une affectation' : 'Ajouter une affectation' ?></h2>
      <form method="post" action="assignments.php?action=<?= $editRow ? 'edit' : 'create' ?>">
        <div class="row g-3">
          <div class="col-md-3">
            <label class="form-label">Employé</label>
            <?php if ($editRow): ?>
              <input type="hidden" name="idEmp" value="<?=h($editRow['idEmp'])?>">
              <input class="form-control" value="<?=h($editRow['nomEmp'].' (#'.$editRow['idEmp'].')')?>" readonly>
            <?php else: ?>
              <select name="idEmp" class="form-select" required>
                <option value="">— choisir —</option>
                <?php foreach ($employees as $e): ?>
                  <option value="<?=h($e['idEmp'])?>"><?=h($e['nomEmp'])?> (#<?=h($e['idEmp'])?>)</option>
                <?php endforeach; ?>
              </select>
            <?php endif; ?>
          </div>

          <div class="col-md-3">
            <label class="form-label">Projet</label>
            <?php if ($editRow): ?>
              <input type="hidden" name="nomProj" value="<?=h($editRow['nomProj'])?>">
              <input class="form-control" value="<?=h($editRow['nomProj'])?>" readonly>
            <?php else: ?>
              <select name="nomProj" class="form-select" required>
                <option value="">— choisir —</option>
                <?php foreach ($projects as $p): ?>
                  <option value="<?=h($p['nomProj'])?>"><?=h($p['nomProj'])?></option>
                <?php endforeach; ?>
              </select>
            <?php endif; ?>
          </div>

          <div class="col-md-2">
            <label class="form-label">Heures</label>
            <input type="number" step="0.01" min="0" name="heures" required class="form-control"
                   value="<?=h($editRow['heures'] ?? '')?>">
          </div>

          <div class="col-md-2">
            <label class="form-label">Évaluation (0–100)</label>
            <input type="number" min="0" max="100" name="evalEmp" class="form-control"
                   value="<?=h($editRow['evalEmp'] ?? '')?>">
            <div class="form-text">Vide = non évalué</div>
          </div>
        </div>

        <div class="mt-3">
          <button class="btn btn-dark" type="submit"><?= $editRow ? 'Mettre à jour' : 'Ajouter' ?></button>
          <?php if ($editRow): ?>
            <a class="btn btn-outline-secondary" href="assignments.php">Annuler</a>
          <?php endif; ?>
        </div>
      </form>
    </div>
  </div>

  <!-- Liste -->
  <div class="card">
    <div class="card-body">
      <form class="row g-2 mb-3" method="get" action="">
        <div class="col-auto">
          <input class="form-control" name="q" placeholder="Filtrer par projet ou employé" value="<?=h($q)?>">
        </div>
        <div class="col-auto">
          <button class="btn btn-outline-dark">Rechercher</button>
        </div>
      </form>

      <div class="table-responsive">
        <table class="table mb-0 align-middle">
          <thead class="table-light">
            <tr><th>Projet</th><th>Employé</th><th>Heures</th><th>Éval</th><th></th></tr>
          </thead>
          <tbody>
          <?php foreach ($rows as $r): ?>
            <tr>
              <td class="fw-semibold"><?=h($r['nomProj'])?></td>
              <td><?=h($r['nomEmp'])?> <span class="text-muted">(#<?=h($r['idEmp'])?>)</span></td>
              <td><?=h($r['heures'])?></td>
              <td><?=($r['evalEmp']===null||$r['evalEmp']==='') ? '—' : h($r['evalEmp'])?></td>
              <td class="text-end">
                <a class="btn btn-sm btn-outline-primary"
                   href="assignments.php?action=edit&idEmp=<?=h($r['idEmp'])?>&nomProj=<?=urlencode($r['nomProj'])?>">Éditer</a>
                <form class="d-inline" method="post" action="assignments.php?action=delete"
                      onsubmit="return confirm('Supprimer cette affectation ?');">
                  <input type="hidden" name="idEmp" value="<?=h($r['idEmp'])?>">
                  <input type="hidden" name="nomProj" value="<?=h($r['nomProj'])?>">
                  <button class="btn btn-sm btn-outline-danger">Supprimer</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$rows): ?>
            <tr><td colspan="5" class="p-3 text-muted">Aucune affectation.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>
</body>
</html>
