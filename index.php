<?php
// Website_Application_security/index.php
require __DIR__.'/_db.php';

// ===== KPIs rapides =====
$kpis = [
  'employees' => 0,
  'projects'  => 0,
  'active'    => 0,       // projets en cours (dateDebut <= aujourd'hui)
  'hours'     => 0.0,     // total heures pointées
  'avg_eval'  => null,    // moyenne des évaluations (sur lignes non nulles)
];

$kpis['employees'] = (int)$pdo->query("SELECT COUNT(*) FROM Emp")->fetchColumn();
$kpis['projects']  = (int)$pdo->query("SELECT COUNT(*) FROM Proj")->fetchColumn();
$kpis['active']    = (int)$pdo->query("SELECT COUNT(*) FROM Proj WHERE dateDebut <= CURDATE()")->fetchColumn();
$kpis['hours']     = (float)$pdo->query("SELECT IFNULL(SUM(heures),0) FROM EmpProj")->fetchColumn();
$kpis['avg_eval']  = $pdo->query("SELECT ROUND(AVG(evalEmp),1) FROM EmpProj WHERE evalEmp IS NOT NULL")->fetchColumn();

// ===== Top 5 projets par heures (avec nb employés) =====
$topProjects = $pdo->query("
  SELECT
    p.nomProj,
    DATE_FORMAT(p.dateDebut, '%d/%m/%Y') AS dateDebut,
    ROUND(IFNULL(SUM(ep.heures),0),2) AS totalHeures,
    COUNT(DISTINCT ep.idEmp) AS nbEmp
  FROM Proj p
  LEFT JOIN EmpProj ep ON ep.nomProj = p.nomProj
  GROUP BY p.nomProj, p.dateDebut
  ORDER BY totalHeures DESC, p.nomProj
  LIMIT 5
")->fetchAll();

// ===== Derniers projets créés (10) =====
$recentProjects = $pdo->query("
  SELECT nomProj, DATE_FORMAT(dateDebut, '%d/%m/%Y') AS dateDebut, budget, mgrProj
  FROM Proj
  ORDER BY dateDebut DESC
  LIMIT 10
")->fetchAll();

// ===== Répartition employées par département (Top 5) =====
$byDept = $pdo->query("
  SELECT deptEmp, COUNT(*) AS n
  FROM Emp
  GROUP BY deptEmp
  ORDER BY n DESC, deptEmp
  LIMIT 5
")->fetchAll();

?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Accueil — Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body{background:#f6f7fb}
    .card{border-radius:16px}
    .kpi .value{font-size:1.8rem;font-weight:700}
    .btn-tile{padding:1.25rem;border-radius:12px}
  </style>
</head>
<body>
<div class="container py-4">

  <!-- Header + boutons -->
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
    <div>
      <h1 class="h3 m-0">Website Application Security</h1>
      <div class="text-muted">Dashboard — vue d’ensemble</div>
    </div>
    <div class="d-flex flex-wrap gap-2">
      <a href="employees.php" class="btn btn-dark btn-tile">Gérer les Employés (CRUD)</a>
      <a href="projects.php" class="btn btn-dark btn-tile">Gérer les Projets (CRUD)</a>
      <a href="assignments.php" class="btn btn-dark btn-tile">Gérer les Affectations (CRUD)</a>
      <a href="projects_view.php" class="btn btn-outline-dark btn-tile">Projets en cours + liés</a>
    </div>
  </div>

  <!-- KPIs -->
  <div class="row g-3 kpi">
    <div class="col-12 col-md-3">
      <div class="card shadow-sm h-100">
        <div class="card-body">
          <div class="text-muted">Employés</div>
          <div class="value"><?=h($kpis['employees'])?></div>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-3">
      <div class="card shadow-sm h-100">
        <div class="card-body">
          <div class="text-muted">Projets (total)</div>
          <div class="value"><?=h($kpis['projects'])?></div>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-3">
      <div class="card shadow-sm h-100">
        <div class="card-body">
          <div class="text-muted">Projets en cours</div>
          <div class="value"><?=h($kpis['active'])?></div>
          <div class="progress mt-2" role="progressbar" aria-label="Actifs">
            <?php
              $pct = $kpis['projects'] ? (int)round($kpis['active']*100/$kpis['projects']) : 0;
            ?>
            <div class="progress-bar" style="width: <?=$pct?>%"><?=$pct?>%</div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-3">
      <div class="card shadow-sm h-100">
        <div class="card-body">
          <div class="text-muted">Heures pointées</div>
          <div class="value"><?=number_format($kpis['hours'], 2, ',', ' ')?> h</div>
          <div class="text-muted small mt-1">
            Éval moyenne : <?= $kpis['avg_eval'] !== null ? h($kpis['avg_eval']).'/100' : '—' ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Deux colonnes : Top projets + Répartition par département -->
  <div class="row g-3 mt-1">
    <div class="col-12 col-lg-7">
      <div class="card shadow-sm h-100">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h2 class="h6 m-0">Top 5 projets par heures</h2>
            <a class="btn btn-sm btn-outline-secondary" href="projects_view.php">Voir tous</a>
          </div>
          <div class="table-responsive">
            <table class="table align-middle mb-0">
              <thead class="table-light">
              <tr><th>Projet</th><th>Début</th><th>Heures</th><th>Employés</th></tr>
              </thead>
              <tbody>
              <?php foreach ($topProjects as $p): ?>
                <tr>
                  <td class="fw-semibold"><?=h($p['nomProj'])?></td>
                  <td><?=h($p['dateDebut'])?></td>
                  <td><?=number_format((float)$p['totalHeures'], 2, ',', ' ')?></td>
                  <td><?=h($p['nbEmp'])?></td>
                </tr>
              <?php endforeach; ?>
              <?php if (!$topProjects): ?>
                <tr><td colspan="4" class="text-muted p-3">Aucune donnée.</td></tr>
              <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-5">
      <div class="card shadow-sm h-100">
        <div class="card-body">
          <h2 class="h6 mb-2">Répartition employés par département (Top 5)</h2>
          <?php
            $totalEmp = max(1, $kpis['employees']); // éviter division par 0
          ?>
          <?php foreach ($byDept as $d): 
            $pct = (int)round($d['n']*100/$totalEmp);
          ?>
            <div class="d-flex justify-content-between">
              <div><strong><?=h($d['deptEmp'])?></strong> <span class="text-muted">(<?=h($d['n'])?>)</span></div>
              <div class="text-muted"><?=$pct?>%</div>
            </div>
            <div class="progress mb-2" role="progressbar">
              <div class="progress-bar" style="width: <?=$pct?>%"></div>
            </div>
          <?php endforeach; ?>
          <?php if (!$byDept): ?>
            <div class="text-muted">Aucune donnée.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Derniers projets -->
  <div class="card shadow-sm mt-3">
    <div class="card-body">
      <h2 class="h6 mb-2">Derniers projets</h2>
      <div class="table-responsive">
        <table class="table align-middle mb-0">
          <thead class="table-light">
            <tr><th>Projet</th><th>Début</th><th>Budget</th><th>Mgr (idEmp)</th></tr>
          </thead>
          <tbody>
          <?php foreach ($recentProjects as $p): ?>
            <tr>
              <td class="fw-semibold"><?=h($p['nomProj'])?></td>
              <td><?=h($p['dateDebut'])?></td>
              <td><?=number_format((float)$p['budget'], 2, ',', ' ')?> €</td>
              <td><?=h($p['mgrProj'])?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$recentProjects): ?>
            <tr><td colspan="4" class="text-muted p-3">Aucun projet récent.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <footer class="mt-4 text-center text-muted small">
    Dashboard lecture seule • KPIs calculés en SQL • Boutons d’accès rapide
  </footer>
</div>
</body>
</html>
