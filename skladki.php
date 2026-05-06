<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $person = $_POST['person'] ?? '';
    $amount = $_POST['amount'] ?? 0;
    $payment_type = $_POST['payment_type'] ?? '';
    $notes = $_POST['notes'] ?? '';

    if ($person !== '' && $amount > 0) {
        $stmt = $pdo->prepare("
            INSERT INTO payments (person, amount, payment_type, notes)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$person, $amount, $payment_type, $notes]);
    }

    header("Location: skladki.php");
    exit;
}

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM payments WHERE id = ?");
    $stmt->execute([$_GET['delete']]);

    header("Location: skladki.php");
    exit;
}

$participants = $pdo->query("
    SELECT * FROM participants
    WHERE pays = 1
    ORDER BY name ASC
")->fetchAll();

$payments = $pdo->query("
    SELECT * FROM payments
    ORDER BY id DESC
")->fetchAll();

$totalCosts = $pdo->query("
    SELECT SUM(amount) FROM costs
    WHERE split_type != 'custom'
")->fetchColumn();

$totalCosts = $totalCosts ?: 0;

$totalPayments = $pdo->query("
    SELECT SUM(amount) FROM payments
")->fetchColumn();

$totalPayments = $totalPayments ?: 0;

$payingCount = count($participants);
$targetPerPerson = $payingCount > 0 ? $totalCosts / $payingCount : 0;

$paymentsByPerson = [];

foreach ($payments as $payment) {
    $key = mb_strtolower(trim($payment['person']));

    if (!isset($paymentsByPerson[$key])) {
        $paymentsByPerson[$key] = 0;
    }

    $paymentsByPerson[$key] += $payment['amount'];
}

$missingTotal = max($totalCosts - $totalPayments, 0);
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Składki - Kawalerski</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f3f5f7;
            padding: 30px;
            color: #111827;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .card {
            background: white;
            padding: 20px;
            border-radius: 14px;
            margin-bottom: 20px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.08);
        }

        .nav a {
            color: #2563eb;
            margin-right: 15px;
            text-decoration: none;
            font-weight: bold;
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

        .grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        .kpi {
            font-size: 34px;
            font-weight: bold;
            margin-top: 8px;
        }

        .muted {
            color: #64748b;
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

        a.delete {
            color: #b91c1c;
            font-weight: bold;
            text-decoration: none;
        }

        .ok {
            color: #15803d;
            font-weight: bold;
        }

        .bad {
            color: #b91c1c;
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
        <h1>Składki i wpłaty</h1>

        <div class="grid">
            <div class="card">
                <div>Suma kosztów</div>
                <div class="kpi"><?= number_format($totalCosts, 2, ',', ' ') ?> zł</div>
            </div>

            <div class="card">
                <div>Suma wpłat</div>
                <div class="kpi"><?= number_format($totalPayments, 2, ',', ' ') ?> zł</div>
            </div>

            <div class="card">
                <div>Brakuje</div>
                <div class="kpi"><?= number_format($missingTotal, 2, ',', ' ') ?> zł</div>
            </div>

            <div class="card">
                <div>Składka / osoba</div>
                <div class="kpi"><?= number_format($targetPerPerson, 2, ',', ' ') ?> zł</div>
            </div>
        </div>

        <p class="muted">
            Składka liczona jest na podstawie kosztów z wyłączeniem pozycji oznaczonych jako „custom”.
        </p>
    </div>

    <div class="card">
        <h2>Dodaj wpłatę</h2>

        <form method="POST">
            <div class="form-grid">
                <div>
                    <label>Osoba</label>
                    <select name="person" required>
                        <option value="">-- wybierz --</option>
                        <?php foreach ($participants as $p): ?>
                            <option value="<?= htmlspecialchars($p['name']) ?>">
                                <?= htmlspecialchars($p['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label>Kwota</label>
                    <input type="number" step="0.01" name="amount" value="181" required>
                </div>
            </div>

            <div class="form-grid">
                <div>
                    <label>Typ wpłaty</label>
                    <select name="payment_type">
                        <option>Zaliczka nocleg</option>
                        <option>Składka ogólna</option>
                        <option>Dopłata</option>
                        <option>Zwrot</option>
                        <option>Inne</option>
                    </select>
                </div>

                <div>
                    <label>Uwagi</label>
                    <input type="text" name="notes" placeholder="np. BLIK, gotówka, przelew">
                </div>
            </div>

            <button type="submit">Dodaj wpłatę</button>
        </form>
    </div>

    <div class="card">
        <h2>Rozliczenie uczestników</h2>

        <table>
            <thead>
                <tr>
                    <th>Osoba</th>
                    <th>Powinna zapłacić</th>
                    <th>Wpłacono</th>
                    <th>Do dopłaty</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($participants as $p): ?>
                    <?php
                        $key = mb_strtolower(trim($p['name']));
                        $paid = $paymentsByPerson[$key] ?? 0;
                        $left = max($targetPerPerson - $paid, 0);
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($p['name']) ?></td>
                        <td><?= number_format($targetPerPerson, 2, ',', ' ') ?> zł</td>
                        <td><?= number_format($paid, 2, ',', ' ') ?> zł</td>
                        <td><?= number_format($left, 2, ',', ' ') ?> zł</td>
                        <td>
                            <?php if ($left <= 0): ?>
                                <span class="ok">Rozliczony</span>
                            <?php else: ?>
                                <span class="bad">Do dopłaty</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="card">
        <h2>Lista wpłat</h2>

        <table>
            <thead>
                <tr>
                    <th>Osoba</th>
                    <th>Kwota</th>
                    <th>Typ</th>
                    <th>Uwagi</th>
                    <th>Akcja</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($payments as $payment): ?>
                    <tr>
                        <td><?= htmlspecialchars($payment['person']) ?></td>
                        <td><?= number_format($payment['amount'], 2, ',', ' ') ?> zł</td>
                        <td><?= htmlspecialchars($payment['payment_type']) ?></td>
                        <td><?= htmlspecialchars($payment['notes']) ?></td>
                        <td>
                            <a class="delete" href="skladki.php?delete=<?= $payment['id'] ?>" onclick="return confirm('Usunąć wpłatę?')">Usuń</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>