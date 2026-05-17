<?php
require_once __DIR__ . '/../../includes/auth.php';
requireClient();
$pdo       = getDB();
$msg       = $type = '';
$voyage_id = (int)($_GET['voyage_id']??0);
$voyages   = $pdo->query("SELECT * FROM voyages WHERE disponible=1 ORDER BY titre")->fetchAll();
$selected  = null;
if ($voyage_id) { $s=$pdo->prepare("SELECT * FROM voyages WHERE id=? AND disponible=1"); $s->execute([$voyage_id]); $selected=$s->fetch(); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vid = (int)($_POST['voyage_id']??0);
    $nb  = max(1,(int)($_POST['nb_personnes']??1));
    $msg_text = trim($_POST['message']??'');
    $s = $pdo->prepare("SELECT * FROM voyages WHERE id=? AND disponible=1"); $s->execute([$vid]); $v=$s->fetch();
    if (!$v) { $msg='Voyage introuvable.'; $type='error'; }
    elseif ($nb > $v['places_restantes']) { $msg='Pas assez de places (reste '.$v['places_restantes'].').'; $type='error'; }
    else {
        $total = $v['prix'] * $nb;
        $pdo->prepare("INSERT INTO reservations (user_id,voyage_id,nb_personnes,prix_total,message) VALUES (?,?,?,?,?)")
            ->execute([$_SESSION['user_id'],$vid,$nb,$total,$msg_text]);
        $pdo->prepare("UPDATE voyages SET places_restantes=places_restantes-? WHERE id=?")->execute([$nb,$vid]);
        $msg = 'Réservation effectuée !'; $type = 'success';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réserver — <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="/projet-voyage3/public/css/style.css">
</head>
<body>
<nav class="navbar">
    <div class="container nav-inner">
        <a href="/projet-voyage3/index.php" class="logo">Tunisie<span>Voyages</span></a>
        <ul class="nav-links">
            <li><a href="/projet-voyage3/pages/client/mes-reservations.php">Mes réservations</a></li>
            <li><a href="/projet-voyage3/pages/client/logout.php" class="btn-nav">Déconnexion</a></li>
        </ul>
    </div>
</nav>
<div class="form-page">
    <div class="form-box" style="max-width:520px">
        <h2>✈ Réserver un voyage</h2>
        <p class="form-sub">Prix en Dinar Tunisien (DT)</p>
        <?php if ($msg): ?><div class="alert alert-<?= $type==='error'?'error':'success' ?>"><?= h($msg) ?></div><?php endif; ?>
        <form method="POST" id="reservForm">
            <div class="form-group">
                <label>Destination *</label>
                <select name="voyage_id" id="voyage_id" required onchange="updateInfo(this)">
                    <option value="">-- Choisir --</option>
                    <?php foreach ($voyages as $v): ?>
                        <option value="<?= $v['id'] ?>" data-prix="<?= $v['prix'] ?>" data-places="<?= $v['places_restantes'] ?>"
                            <?= ($selected&&$selected['id']==$v['id'])?'selected':'' ?>>
                            <?= h($v['titre']) ?> — <?= formatPrix($v['prix']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div id="info-box" style="display:none;background:var(--bg);border-radius:var(--radius);padding:.9rem;margin-bottom:1rem;font-size:.87rem;text-align:center">
                <strong id="info-prix" style="color:var(--accent);font-size:1.1rem"></strong>/pers · <span id="info-places"></span> places dispo.
            </div>
            <div class="form-group">
                <label>Nombre de personnes *</label>
                <input type="number" name="nb_personnes" id="nb" min="1" value="1" required oninput="updateTotal()">
            </div>
            <div id="total-box" style="display:none;background:var(--primary);color:var(--white);border-radius:var(--radius);padding:1rem;text-align:center;margin-bottom:1rem">
                <span style="font-size:.82rem;opacity:.85">Total</span><br>
                <strong id="total" style="font-size:1.8rem"></strong>
            </div>
            <div class="form-group">
                <label>Message (optionnel)</label>
                <textarea name="message" placeholder="Demandes particulières..."></textarea>
            </div>
            <button type="submit" class="btn-accent w-full" style="font-size:1rem;padding:.75rem">Confirmer la réservation</button>
        </form>
    </div>
</div>
<script>
let prix=0;
function updateInfo(s){
    const o=s.options[s.selectedIndex];
    const box=document.getElementById('info-box');
    const tot=document.getElementById('total-box');
    if(o.value){
        prix=parseFloat(o.dataset.prix);
        document.getElementById('info-prix').textContent=prix.toFixed(3)+' DT';
        document.getElementById('info-places').textContent=o.dataset.places;
        document.getElementById('nb').max=o.dataset.places;
        box.style.display=''; tot.style.display=''; updateTotal();
    } else { box.style.display='none'; tot.style.display='none'; prix=0; }
}
function updateTotal(){
    const nb=parseInt(document.getElementById('nb').value)||1;
    document.getElementById('total').textContent=(prix*nb).toFixed(3)+' DT';
}
window.addEventListener('DOMContentLoaded',()=>{ const s=document.getElementById('voyage_id'); if(s.value) updateInfo(s); });
</script>
</body>
</html>
