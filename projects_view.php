<?php
// Website_Application_security/projects_view.php
require __DIR__.'/_db.php';

// Liste projets en cours + collaborateurs (GROUP_CONCAT pour un affichage compact)
$rows = $pdo->query("
  SELECT
    p.nomProj,
    p.dateDebut,
    p.budget,
    COUNT(ep.idEmp) AS nbEmployes,
    GROUP_CONCAT(CONCAT(e.nomEmp, ' (', ep.heures, 'h', IFNULL(CONCAT(' • ', ep.evalEmp), ''), ')') ORDER BY e.nomEmp SEPARATOR ', ') AS collaborateurs
  FROM Proj p
  LEFT JOIN EmpProj ep ON ep.nomProj = p.nomProj
  LEFT JOIN Emp e ON e.idEmp = ep.idEmp
  WHERE p.dateDebut <= CURDATE()
  GROUP BY p.nomProj, p.dateDebut, p.budget
  ORDER BY p.dateDebut DESC, p.nomProj ASC
")->fetchAll();
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Projets en cours — Consultation</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>body{background:#f6f7fb}</style>
</head>
<body>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 m-0">Projets en cours</h1>
    <a href="index.php" class="btn btn-outline-secondary">Accueil</a>
  </div>

  <div class="card">
    <div class="table-responsive">
      <table class="table mb-0 align-middle">
        <thead class="table-light">
          <tr><th>Projet</th><th>Début</th><th>Budget</th><th>Employés liés</th></tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td class="fw-semibold"><?=h($r['nomProj'])?></td>
            <td><?=h(date('d/m/Y', strtotime($r['dateDebut'])))?></td>
            <td><?=number_format((float)$r['budget'], 2, ',', ' ')?> €</td>
            <td>
              <?php if ($r['nbEmployes'] > 0): ?>
                <?=h($r['collaborateurs'])?>
              <?php else: ?>
                <span class="text-muted">Aucun employé affecté</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?>
          <tr><td colspan="4" class="p-3 text-muted">Aucun projet en cours.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</body>
</html>
