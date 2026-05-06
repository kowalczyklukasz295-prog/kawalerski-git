<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $category = $_POST['category'] ?? '';
    $amount = $_POST['amount'] ?? 0;
    $paid_by = $_POST['paid_by'] ?? '';
    $split_type = $_POST['split_type'] ?? 'paying';
    $notes = $_POST['notes'] ?? '';

    if ($name !== '' && $amount > 0) {
        $stmt = $pdo->prepare("
            INSERT INTO costs (name, category, amount, paid_by, split_type, notes)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$name, $category, $amount, $paid_by, $split_type, $notes]);
    }

    header("Location: koszty.php");
    exit;
}

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM costs WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: koszty.php");
    exit;
}

$costs = $pdo->query("SELECT * FROM costs ORDER BY id DESC")->fetchAll();

$total = $pdo->query("SELECT SUM(amount) FROM costs")->fetchColumn();
$total = $total ?: 0;
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Koszty - Kawalerski</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f3f5f7;
            padding: 30px;
            color: #111827;
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
        }

        .card {
            background: white;
            padding: 20px;
            border-radius: 14px;
            margin-bottom: 20px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.08);
        }

        input, select, textarea, button {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            margin-bottom: 12px;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            font-size: 14px;
        }

        button {
            background: #111827;
            color: white;
            font-weight: bold;
            cursor: pointer;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        th, td {
            padding: 10px;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
        }

        th {
            background: #f8fafc;
        }

        a {
            color: #b91c1c;
            font-weight: bold;
            text-decoration: none;
        }

        .nav a {
            color: #2563eb;
            margin-right: 15px;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        .kpi {
            font-size: 36px;
            font-weight: bold;
        }
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
        <h1>Koszty</h1>

        <p>Suma kosztów:</p>
        <div class="kpi"><?= number_format($total, 2, ',', ' ') ?> zł</div>
    </div>

    <div class="card">
        <h2>Dodaj koszt</h2>

        <form method="POST">
            <div class="grid">
                <div>
                    <label>Nazwa kosztu</label>
                    <input type="text" name="name" required>
                </div>

                <div>
                    <label>Kategoria</label>
                    <select name="category">
                        <option>Nocleg</option>
                        <option>Transport</option>
                        <option>Alkohol</option>
                        <option>Jedzenie</option>
                        <option>Napoje</option>
                        <option>Grill</option>
                        <option>Prezent</option>
                        <option>Organizacja</option>
                        <option>Inne</option>
                    </select>
                </div>
            </div>

            <div class="grid">
                <div>
                    <label>Kwota</label>
                    <input type="number" step="0.01" name="amount" required>
                </div>

                <div>
                    <label>Kto zapłacił?</label>
                    <input type="text" name="paid_by">
                </div>
            </div>

            <label>Podział kosztu</label>
            <select name="split_type">
                <option value="paying">Płacący bez Pana Młodego</option>
                <option value="all">Wszyscy</option>
                <option value="custom">Nie licz do składki</option>
            </select>

            <label>Uwagi</label>
            <textarea name="notes"></textarea>

            <button type="submit">Dodaj koszt</button>
        </form>
    </div>

    <div class="card">
        <h2>Lista kosztów</h2>

        <table>
            <thead>
                <tr>
                    <th>Nazwa</th>
                    <th>Kategoria</th>
                    <th>Kwota</th>
                    <th>Zapłacił</th>
                    <th>Podział</th>
                    <th>Uwagi</th>
                    <th>Akcja</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($costs as $c): ?>
                    <tr>
                        <td><?= htmlspecialchars($c['name']) ?></td>
                        <td><?= htmlspecialchars($c['category']) ?></td>
                        <td><?= number_format($c['amount'], 2, ',', ' ') ?> zł</td>
                        <td><?= htmlspecialchars($c['paid_by']) ?></td>
                        <td><?= htmlspecialchars($c['split_type']) ?></td>
                        <td><?= htmlspecialchars($c['notes']) ?></td>
                        <td>
                            <a href="koszty.php?delete=<?= $c['id'] ?>" onclick="return confirm('Usunąć koszt?')">Usuń</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>