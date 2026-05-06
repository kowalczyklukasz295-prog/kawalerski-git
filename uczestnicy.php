<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $arrival = $_POST['arrival'] ?? '';
    $departure = $_POST['departure'] ?? '';
    $pays = isset($_POST['pays']) ? 1 : 0;
    $notes = $_POST['notes'] ?? '';

    if ($name !== '') {
        $stmt = $pdo->prepare("
            INSERT INTO participants (name, arrival, departure, pays, notes)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$name, $arrival, $departure, $pays, $notes]);
    }

    header("Location: uczestnicy.php");
    exit;
}

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM participants WHERE id = ?");
    $stmt->execute([$_GET['delete']]);

    header("Location: uczestnicy.php");
    exit;
}

$participants = $pdo->query("SELECT * FROM participants ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Uczestnicy - Kawalerski</title>
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
    </style>
</head>
<body>

<div class="card nav">
    <a href="index.php">Dashboard</a>
    <a href="uczestnicy.php">Uczestnicy</a>
    <a href="koszty.php">Koszty</a>
<a href="skladki.php">Składki</a>
<a href="todo.php">TODO</a>
</div>

    <div class="card">
        <h1>Uczestnicy</h1>

        <form method="POST">
            <div class="grid">
                <div>
                    <label>Imię / ksywka</label>
                    <input type="text" name="name" required>
                </div>

                <div>
                    <label>Od kiedy jedzie?</label>
                    <select name="arrival">
                        <option>Od piątku</option>
                        <option>Od soboty</option>
                        <option>Do ustalenia</option>
                    </select>
                </div>
            </div>

            <div class="grid">
                <div>
                    <label>Kiedy wraca?</label>
                    <select name="departure">
                        <option>Niedziela standardowo</option>
                        <option>Niedziela rano</option>
                        <option>Niedziela po południu</option>
                        <option>Do ustalenia</option>
                    </select>
                </div>

                <div>
                    <label>
                        <input type="checkbox" name="pays" checked style="width:auto;">
                        Płaci składkę
                    </label>
                </div>
            </div>

            <label>Uwagi</label>
            <textarea name="notes"></textarea>

            <button type="submit">Dodaj uczestnika</button>
        </form>
    </div>

    <div class="card">
        <h2>Lista uczestników</h2>

        <table>
            <thead>
                <tr>
                    <th>Imię</th>
                    <th>Przyjazd</th>
                    <th>Powrót</th>
                    <th>Płaci</th>
                    <th>Uwagi</th>
                    <th>Akcja</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($participants as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['name']) ?></td>
                        <td><?= htmlspecialchars($p['arrival']) ?></td>
                        <td><?= htmlspecialchars($p['departure']) ?></td>
                        <td><?= $p['pays'] ? 'Tak' : 'Nie' ?></td>
                        <td><?= htmlspecialchars($p['notes']) ?></td>
                        <td>
                            <a href="uczestnicy.php?delete=<?= $p['id'] ?>" onclick="return confirm('Usunąć uczestnika?')">Usuń</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>