<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $category = $_POST['category'] ?? '';
    $priority = $_POST['priority'] ?? '';
    $status = $_POST['status'] ?? '';
    $owner = $_POST['owner'] ?? '';
    $quantity = $_POST['quantity'] ?? '';
    $notes = $_POST['notes'] ?? '';

    if ($name !== '') {
        $stmt = $pdo->prepare("
            INSERT INTO todo (name, category, priority, status, owner, quantity, notes)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$name, $category, $priority, $status, $owner, $quantity, $notes]);
    }

    header("Location: todo.php");
    exit;
}

if (isset($_GET['done'])) {
    $stmt = $pdo->prepare("UPDATE todo SET status = 'Załatwione' WHERE id = ?");
    $stmt->execute([$_GET['done']]);
    header("Location: todo.php");
    exit;
}

if (isset($_GET['open'])) {
    $stmt = $pdo->prepare("UPDATE todo SET status = 'Do zrobienia' WHERE id = ?");
    $stmt->execute([$_GET['open']]);
    header("Location: todo.php");
    exit;
}

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM todo WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: todo.php");
    exit;
}

$filterCategory = $_GET['category'] ?? '';
$filterStatus = $_GET['status'] ?? '';

$sql = "SELECT * FROM todo WHERE 1=1";
$params = [];

if ($filterCategory !== '') {
    $sql .= " AND category = ?";
    $params[] = $filterCategory;
}

if ($filterStatus !== '') {
    $sql .= " AND status = ?";
    $params[] = $filterStatus;
}

$sql .= "
    ORDER BY
    CASE priority
        WHEN 'Pilne' THEN 1
        WHEN 'Normalne' THEN 2
        WHEN 'Opcjonalne' THEN 3
        ELSE 4
    END,
    id DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll();

$total = $pdo->query("SELECT COUNT(*) FROM todo")->fetchColumn();
$done = $pdo->query("SELECT COUNT(*) FROM todo WHERE status IN ('Załatwione', 'Kupione', 'Zabrane')")->fetchColumn();
$open = $total - $done;
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>TODO - Kawalerski</title>
    <style>
        body { font-family: Arial, sans-serif; background:#f3f5f7; padding:30px; color:#111827; }
        .container { max-width:1200px; margin:0 auto; }
        .card { background:white; padding:20px; border-radius:14px; margin-bottom:20px; box-shadow:0 8px 24px rgba(0,0,0,0.08); }
        .nav a { color:#2563eb; margin-right:15px; text-decoration:none; font-weight:bold; }
        input, select, textarea, button { width:100%; padding:10px; margin-top:6px; margin-bottom:12px; border-radius:8px; border:1px solid #cbd5e1; font-size:14px; }
        button { background:#111827; color:white; font-weight:bold; cursor:pointer; }
        .grid { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
        .grid3 { display:grid; grid-template-columns:1fr 1fr 1fr; gap:14px; }
        table { width:100%; border-collapse:collapse; background:white; }
        th, td { padding:10px; border-bottom:1px solid #e2e8f0; text-align:left; }
        th { background:#f8fafc; }
        a.delete { color:#b91c1c; font-weight:bold; text-decoration:none; }
        a.action { color:#2563eb; font-weight:bold; text-decoration:none; margin-right:8px; }
        .kpi { font-size:34px; font-weight:bold; }
        .done { color:#15803d; font-weight:bold; }
        .open { color:#b91c1c; font-weight:bold; }
        .muted { color:#64748b; }
    </style>
</head>
<body>

<div class="container">

    <div class="card nav">
        <a href="index.php">Dashboard</a>
        <a href="uczestnicy.php">Uczestnicy</a>
        <a href="koszty.php">Koszty</a>
        <a href="skladki.php">Składki</a>
        <a href="todo.php">TODO</a>
    </div>

    <div class="card">
        <h1>TODO / Organizacja</h1>

        <div class="grid3">
            <div class="card">
                <div>Wszystkie zadania</div>
                <div class="kpi"><?= $total ?></div>
            </div>

            <div class="card">
                <div>Otwarte</div>
                <div class="kpi"><?= $open ?></div>
            </div>

            <div class="card">
                <div>Zamknięte</div>
                <div class="kpi"><?= $done ?></div>
            </div>
        </div>
    </div>

    <div class="card">
        <h2>Dodaj zadanie / produkt</h2>

        <form method="POST">
            <div class="grid">
                <div>
                    <label>Nazwa</label>
                    <input type="text" name="name" required>
                </div>

                <div>
                    <label>Kategoria</label>
                    <select name="category">
                        <option>Zakupy</option>
                        <option>Transport</option>
                        <option>Alkohol</option>
                        <option>Grill</option>
                        <option>Jedzenie</option>
                        <option>Śniadanie</option>
                        <option>Napoje</option>
                        <option>Higiena</option>
                        <option>Apteczka</option>
                        <option>Prezent</option>
                        <option>Organizacja</option>
                        <option>Inne</option>
                    </select>
                </div>
            </div>

            <div class="grid3">
                <div>
                    <label>Priorytet</label>
                    <select name="priority">
                        <option>Pilne</option>
                        <option>Normalne</option>
                        <option>Opcjonalne</option>
                    </select>
                </div>

                <div>
                    <label>Status</label>
                    <select name="status">
                        <option>Do zrobienia</option>
                        <option>Do kupienia</option>
                        <option>Kupione</option>
                        <option>Zabrane</option>
                        <option>Załatwione</option>
                    </select>
                </div>

                <div>
                    <label>Ilość / zakres</label>
                    <input type="text" name="quantity" placeholder="np. 40 l, 2 kg, 100 szt.">
                </div>
            </div>

            <div class="grid">
                <div>
                    <label>Odpowiedzialny</label>
                    <input type="text" name="owner" placeholder="np. Łukasz">
                </div>

                <div>
                    <label>Uwagi</label>
                    <input type="text" name="notes">
                </div>
            </div>

            <button type="submit">Dodaj</button>
        </form>
    </div>

    <div class="card">
        <h2>Filtry</h2>

        <form method="GET">
            <div class="grid">
                <div>
                    <label>Kategoria</label>
                    <select name="category">
                        <option value="">Wszystkie</option>
                        <option <?= $filterCategory === 'Zakupy' ? 'selected' : '' ?>>Zakupy</option>
                        <option <?= $filterCategory === 'Transport' ? 'selected' : '' ?>>Transport</option>
                        <option <?= $filterCategory === 'Alkohol' ? 'selected' : '' ?>>Alkohol</option>
                        <option <?= $filterCategory === 'Jedzenie' ? 'selected' : '' ?>>Jedzenie</option>
                        <option <?= $filterCategory === 'Napoje' ? 'selected' : '' ?>>Napoje</option>
                        <option <?= $filterCategory === 'Organizacja' ? 'selected' : '' ?>>Organizacja</option>
                    </select>
                </div>

                <div>
                    <label>Status</label>
                    <select name="status">
                        <option value="">Wszystkie</option>
                        <option <?= $filterStatus === 'Do zrobienia' ? 'selected' : '' ?>>Do zrobienia</option>
                        <option <?= $filterStatus === 'Do kupienia' ? 'selected' : '' ?>>Do kupienia</option>
                        <option <?= $filterStatus === 'Kupione' ? 'selected' : '' ?>>Kupione</option>
                        <option <?= $filterStatus === 'Zabrane' ? 'selected' : '' ?>>Zabrane</option>
                        <option <?= $filterStatus === 'Załatwione' ? 'selected' : '' ?>>Załatwione</option>
                    </select>
                </div>
            </div>

            <button type="submit">Filtruj</button>
        </form>
    </div>

    <div class="card">
        <h2>Lista TODO</h2>

        <table>
            <thead>
                <tr>
                    <th>Nazwa</th>
                    <th>Kategoria</th>
                    <th>Priorytet</th>
                    <th>Status</th>
                    <th>Ilość</th>
                    <th>Odpowiedzialny</th>
                    <th>Uwagi</th>
                    <th>Akcje</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['name']) ?></td>
                        <td><?= htmlspecialchars($item['category']) ?></td>
                        <td><?= htmlspecialchars($item['priority']) ?></td>
                        <td>
                            <?php if (in_array($item['status'], ['Załatwione', 'Kupione', 'Zabrane'])): ?>
                                <span class="done"><?= htmlspecialchars($item['status']) ?></span>
                            <?php else: ?>
                                <span class="open"><?= htmlspecialchars($item['status']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($item['quantity']) ?></td>
                        <td><?= htmlspecialchars($item['owner']) ?></td>
                        <td><?= htmlspecialchars($item['notes']) ?></td>
                        <td>
                            <a class="action" href="todo.php?done=<?= $item['id'] ?>">Załatwione</a>
                            <a class="action" href="todo.php?open=<?= $item['id'] ?>">Otwórz</a>
                            <a class="delete" href="todo.php?delete=<?= $item['id'] ?>" onclick="return confirm('Usunąć pozycję?')">Usuń</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>