<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clinic Backend</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            background: #f8fafc;
            color: #0f172a;
        }

        main {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 12px 40px rgba(15, 23, 42, 0.08);
            text-align: center;
        }

        h1 {
            margin-top: 0;
        }

        .muted {
            color: #64748b;
        }
    </style>
</head>
<body>
    <main>
        <h1>Clinic Backend</h1>
        <p class="muted">Hello, <?php echo htmlspecialchars($name ?? 'Guest', ENT_QUOTES, 'UTF-8'); ?>.</p>
        <p>Role: <?php echo htmlspecialchars($role ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></p>
    </main>
</body>
</html>
