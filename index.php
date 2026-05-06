<?php

require 'config.php';

$totalParticipants = $pdo->query("
    SELECT COUNT(*) FROM participants
")->fetchColumn();

$totalPaying = $pdo->query("
    SELECT COUNT(*) FROM participants
    WHERE pays = 1
")->fetchColumn();

$fridayToSunday = $pdo->query("
    SELECT COUNT(*) FROM participants
    WHERE arrival = 'Od piątku'
    AND departure = 'Niedziela standardowo'
")->fetchColumn();

$fridayToSundayMorning = $pdo->query("
    SELECT COUNT(*) FROM participants
    WHERE arrival = 'Od piątku'
    AND departure = 'Niedziela rano'
")->fetchColumn();

$saturdayToSunday = $pdo->query("
    SELECT COUNT(*) FROM participants
    WHERE arrival = 'Od soboty'
")->fetchColumn();

?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Kawalerski</title>

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
            border-radius: 14px;
            padding: 20px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }

        .nav a {
            color: #2563eb;
            margin-right: 15px;
            text-decoration: none;
            font-weight: bold;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        .kpi {
            font-size: 42px;
            font-weight: bold;
            margin-top: 10px;
        }

        .muted {
            color: #64748b;
            margin-top: 10px;
        }

        h1 {
            margin-top: 0;
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
        <h1>Dashboard kawalerskiego</h1>

        <div class="grid">

            <div class="card">
                <div>Łącznie uczestników</div>
                <div class="kpi"><?= $totalParticipants ?></div>
            </div>

            <div class="card">
                <div>Płacących składkę</div>
                <div class="kpi"><?= $totalPaying ?></div>
            </div>

            <div class="card">
                <div>Od piątku do niedzieli</div>
                <div class="kpi"><?= $fridayToSunday ?></div>
            </div>

            <div class="card">
                <div>Od piątku do niedzieli rano</div>
                <div class="kpi"><?= $fridayToSundayMorning ?></div>
            </div>

            <div class="card">
                <div>Od soboty do niedzieli</div>
                <div class="kpi"><?= $saturdayToSunday ?></div>
            </div>

        </div>

        <div class="muted">
            Dashboard będzie później rozszerzony o:
            koszty, składki, transport, TODO i rozliczenia.
        </div>

    </div>

</div>

</body>
</html>