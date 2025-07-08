<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'mydatabase'; // <-- غيّريها لو قاعدة بياناتك اسمها مختلف

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $age = (int)$_POST['age'];
    $conn->query("INSERT INTO people (name, age) VALUES ('$name', $age)");
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

$result = $conn->query("SELECT * FROM people ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>نموذج تسجيل</title>
    <style>
        form { display: flex; gap: 10px; margin-bottom: 20px; }
        input, button { padding: 8px; font-size: 1em; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: center; }
    </style>
</head>
<body>

<form method="POST">
    <input type="text" name="name" placeholder="الاسم" required>
    <input type="number" name="age" placeholder="العمر" required>
    <button type="submit">إرسال</button>
</form>

<table>
    <thead>
        <tr>
            <th>ID</th><th>الاسم</th><th>العمر</th><th>الحالة</th><th>الإجراء</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr id="row-<?= $row['id'] ?>">
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= $row['age'] ?></td>
                <td class="status" data-id="<?= $row['id'] ?>">
                    <?= $row['status'] == 1 ? 'مفعل' : 'غير مفعل' ?>
                </td>
                <td><button type="button" class="toggle-btn" data-id="<?= $row['id'] ?>">تبديل الحالة</button></td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<script>
document.querySelectorAll('.toggle-btn').forEach(button => {
    button.addEventListener('click', () => {
        const id = button.getAttribute('data-id');
        fetch(`?toggle_id=${id}`)
            .then(res => res.text())
            .then(newStatus => {
                if (newStatus === 'error') {
                    alert('خطأ في تغيير الحالة');
                    return;
                }
                const statusCell = document.querySelector(`#row-${id} .status`);
                statusCell.textContent = newStatus === '1' ? 'مفعل' : 'غير مفعل';
            })
            .catch(() => alert('خطأ في الاتصال'));
    });
});
</script>

</body>
</html>

<?php
if (isset($_GET['toggle_id'])) {
    $id = (int)$_GET['toggle_id'];
    $res = $conn->query("SELECT status FROM people WHERE id = $id");
    if ($res && $row = $res->fetch_assoc()) {
        $newStatus = $row['status'] == 1 ? 0 : 1;
        $conn->query("UPDATE people SET status = $newStatus WHERE id = $id");
        echo $newStatus;
    } else {
        echo "error";
    }
    $conn->close();
    exit;
}
?>
