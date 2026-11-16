<?php
session_start();
    if (!isset($_SESSION['visits'])) {
        $_SESSION['visits'] = 1;
    } else {
        $_SESSION['visits']++;
        }

require_once 'db.php';

$submission_error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $author = trim($_POST['author'] ?? '');
    $body = trim($_POST['message'] ?? '');
    $remember_name = isset($_POST['remember_name']);

    if(!empty($author) && !empty($body)) {
        $stmt = $pdo->prepare("INSERT INTO messages (author, body) VALUES (:author, :body)");
        $stmt->execute(['author' => $author, 'body' => $body]);

        if($remember_name) {
            setcookie('author', $author, time() + (86400 * 15), "/");
        } else {
            setcookie('author', '', time() - 3600, "/");
        }
        header("Location: index.php");
        exit();
    } else {
    $submission_error = "Please fill in both fields.";
    }
}

    $cookie_name = $_COOKIE['author'] ?? '';
    $search = $_GET['search'] ?? '';
    if (!empty($search)) {
        $stmt = $pdo->prepare("SELECT * FROM messages WHERE body ILIKE :search ORDER BY created_at DESC");
        $stmt->execute(['search' => "%$search%"]);
    } else {
        $stmt = $pdo->query("SELECT * FROM messages ORDER BY created_at DESC");
    }
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="description"
        content="Classwall website for messageboard discussions">
    <meta name="author" content="Crystal Sevilla">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Classwall: Cookies, Sessions, and PostgreSQL</title>
        <style>
            * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Helvetica, Arial, sans-serif;
            background-color: #f0f2f5;
            color: #1c1e21;
            line-height: 1.6;
            padding: 30px 15px;
        }

        header {
            background: linear-gradient(135deg, #4f46e5, #3b82f6);
            color: white;
            padding: 28px 25px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
            text-align: center;
        }

        header h1 {
            font-size: 1.85rem;
            margin-bottom: 6px;
        }

        header p {
            opacity: 0.9;
            font-size: 0.95rem;
        }

        .session-badge {
            background-color: #e0e7ff;
            color: #3730a3;
            padding: 10px 16px;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid #c7d2fe;
        }

        .card {
            background: #ffffff;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            margin-bottom: 20px;
        }

        h2, h3 {
            color: #111827;
            margin-bottom: 16px;
            font-size: 1.25rem;
        }

        label {
            display: inline-block;
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 6px;
            color: #374151;
        }

        input[type="text"],
        textarea {
            width: 100%;
            padding: 11px 14px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 0.95rem;
            font-family: inherit;
            transition: border-color 0.2s, box-shadow 0.2s;
            margin-bottom: 14px;
        }

        input[type="text"]:focus,
        textarea:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
        }

        textarea {
            min-height: 100px;
            resize: vertical;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 16px;
        }

        .checkbox-group input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: #4f46e5;
            cursor: pointer;
        }

        .checkbox-group label {
            margin-bottom: 0;
            font-weight: 400;
            cursor: pointer;
        }

        button {
            background-color: #4f46e5;
            color: white;
            border: none;
            padding: 11px 20px;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        button:hover {
            background-color: #4338ca;
        }

        #search form {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        #search input[type="text"] {
            margin-bottom: 0;
            flex: 1;
        }

        .reset-btn {
            text-decoration: none;
            color: #4b5563;
            background-color: #e5e7eb;
            padding: 11px 16px;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            transition: background-color 0.2s;
        }

        .reset-btn:hover {
            background-color: #d1d5db;
        }

        .error-msg {
            background-color: #fee2e2;
            color: #b91c1c;
            padding: 10px 14px;
            border-radius: 8px;
            margin-bottom: 14px;
            font-size: 0.9rem;
            border: 1px solid #fecaca;
        }

        #messages ul {
            list-style: none;
        }

        #messages li {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 16px;
            margin-bottom: 12px;
        }

        .msg-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        .msg-header strong {
            color: #4f46e5;
            font-size: 1rem;
        }

        .msg-header em {
            color: #6b7280;
            font-style: normal;
            font-size: 0.82rem;
        }

        .msg-body {
            color: #1f2937;
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        .empty-state {
            text-align: center;
            color: #6b7280;
            padding: 20px 0;
        }
        </style>
</head>
<body>
    <h1>Welcome to the Classwall!</h1>
    <p>Enter your discussions here!</p>

        <h2>Discussion Board</h2>
        <p>You have visited this page <?php echo $_SESSION['visits']; ?> times during this session.</p>
        <form action="index.php" method="POST">
            <section id = "name">
                <?php if (!empty($submission_error)): ?>
                    <p style="color: red;"><?php echo htmlspecialchars($submission_error); ?></p>
                <?php endif; ?>
                <label for="author">Username:</label>
                <input type="text" id="author" name="author" value="<?php echo htmlspecialchars($cookie_name); ?>" required><br><br>
                <input type="checkbox" id="remember_name" name="remember_name" value="1" <?php echo !empty($cookie_name) ? 'checked' : ''; ?>>
                <label for="remember_name">Remember my username</label>
            </section>
            <section id = "forms">
                <label for="message">Enter your Message:</label>
                <textarea id="message" name="message" required></textarea><br><br>
                <button type="submit"> Post Message </button>
            </section>
        </form>

        <section id = "search">
            <h3>Search Messages</h3>
            <form action="index.php" method="GET">
                <input type="text" name="search" placeholder="Search messages..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit">Search</button>
                <?php if (!empty($search)): ?>
                    <a href="index.php">Reset</a>
                <?php endif; ?>
            </form>
        </section>

        <section id = "messages">
            <h3>Messages</h3>
            <?php if (!empty($messages)): ?>
                <ul>
                    <?php foreach ($messages as $message): ?>
                        <li>
                            <strong><?php echo htmlspecialchars($message['author']); ?>:</strong>
                            <?php echo htmlspecialchars($message['body']); ?>
                            <em>(<?php echo htmlspecialchars($message['created_at']); ?>)</em>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No messages found.</p>
            <?php endif; ?>
        </section>
</body>
</html>
