<?php
// تحديد اسم ملف النسخة الاحتياطية
$backup_file = 'backup_' . date('Y-m-d_H-i-s') . '.zip';
$db_backup_file = 'db_backup_' . date('Y-m-d_H-i-s') . '.sql';

// معلومات قاعدة البيانات
$db_host = 'localhost';
$db_user = 'u210490590_nsqli';  // عدل حسب إعداداتك
$db_pass = 'U210490590_nsqli';      // عدل حسب إعداداتك
$db_name = 'u210490590_nsqli'; // عدل حسب اسم قاعدة البيانات

// الاتصال بقاعدة البيانات
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}

// فتح ملف لحفظ نسخة قاعدة البيانات الاحتياطية
$backup_sql = fopen($db_backup_file, 'w');
if (!$backup_sql) {
    die("فشل في إنشاء ملف النسخة الاحتياطية لقاعدة البيانات.");
}

// جلب جميع الجداول من قاعدة البيانات
$tables_result = $conn->query("SHOW TABLES");
if (!$tables_result) {
    die("فشل في جلب الجداول: " . $conn->error);
}

while ($table = $tables_result->fetch_array()) {
    $table_name = $table[0];

    // استخراج هيكل الجدول
    $create_table_result = $conn->query("SHOW CREATE TABLE `$table_name`");
    $create_table_row = $create_table_result->fetch_array();
    fwrite($backup_sql, "DROP TABLE IF EXISTS `$table_name`;\n");
    fwrite($backup_sql, $create_table_row[1] . ";\n\n");

    // استخراج البيانات
    $rows_result = $conn->query("SELECT * FROM `$table_name`");
    while ($row = $rows_result->fetch_assoc()) {
        $values = [];
        foreach ($row as $value) {
            $values[] = isset($value) ? "'" . $conn->real_escape_string($value) . "'" : "NULL";
        }
        fwrite($backup_sql, "INSERT INTO `$table_name` VALUES (" . implode(", ", $values) . ");\n");
    }
    fwrite($backup_sql, "\n");
}
fclose($backup_sql);
$conn->close();

// إنشاء ملف ZIP
$zip = new ZipArchive();
if ($zip->open($backup_file, ZipArchive::CREATE) !== TRUE) {
    die("فشل في إنشاء ملف النسخة الاحتياطية!");
}

// إضافة ملف قاعدة البيانات إلى الأرشيف
$zip->addFile($db_backup_file, basename($db_backup_file));

// إضافة جميع ملفات الموقع
function addFolderToZip($folder, $zip, $folderInZip = '') {
    $files = scandir($folder);
    foreach ($files as $file) {
        if ($file == '.' || $file == '..' || $file == 'backup.php') continue;
        $filePath = $folder . DIRECTORY_SEPARATOR . $file;
        $zipPath = $folderInZip . $file;
        if (is_dir($filePath)) {
            addFolderToZip($filePath, $zip, $zipPath . '/');
        } else {
            $zip->addFile($filePath, $zipPath);
        }
    }
}
addFolderToZip(__DIR__, $zip);
$zip->close();

// حذف ملف قاعدة البيانات بعد إضافته إلى الأرشيف
unlink($db_backup_file);

// توفير رابط التحميل
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . basename($backup_file) . '"');
header('Content-Length: ' . filesize($backup_file));
readfile($backup_file);

// حذف ملف النسخة الاحتياطية بعد التنزيل
unlink($backup_file);
?>
