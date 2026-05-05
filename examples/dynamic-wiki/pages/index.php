<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dynamic Wiki</title>
    <style>
        body { font-family: sans-serif; max-width: 600px; margin: 40px auto; line-height: 1.6; }
        nav { margin-bottom: 20px; padding: 10px; background: #eee; }
        nav a { margin-right: 15px; }
    </style>
</head>
<body>
    <nav>
        <a href="/">Home</a>
        <a href="/about">About</a>
        <a href="/wiki/hello">Wiki Hello</a>
        <a href="/stuck">Admin</a>
    </nav>
    <h1>Welcome to Dynamic Wiki</h1>
    <p>Ini adalah demo <b>Mode Dynamic Routing</b>. File ini berada di <code>pages/index.php</code>.</p>
    <p>URL Anda saat ini: <code><?php echo fst_uri(); ?></code></p>
</body>
</html>
