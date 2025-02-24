<?php
// تحديد اسم ملف النسخة الاحتياطية
$backup_file = 'backup_' . date('Y-m-d_H-i-s') . '.zip';

// إنشاء ملف ZIP
$zip = new ZipArchive();
if ($zip->open($backup_file, ZipArchive::CREATE) !== TRUE) {
    die("فشل في إنشاء ملف النسخة الاحتياطية!");
}

// دالة لإضافة الملفات إلى ملف ZIP
function addFilesToZip($folder, $zip, $folderInZip = '') {
    $files = scandir($folder);
    foreach ($files as $file) {
        if ($file == '.' || $file == '..') continue;
        $filePath = $folder . DIRECTORY_SEPARATOR . $file;
        $zipPath = $folderInZip . $file;

        if (is_dir($filePath)) {
            addFilesToZip($filePath, $zip, $zipPath . '/');
        } else {
            $zip->addFile($filePath, $zipPath);
        }
    }
}

// إضافة ملفات الموقع إلى ملف ZIP
addFilesToZip(__DIR__, $zip);
$zip->close();

// معلومات قاعدة البيانات
$db_host = 'localhost';
$db_user = 'u210490590_nsqli';  // عدل حسب إعداداتك
$db_pass = 'U210490590_nsqli';      // عدل حسب إعداداتك
$db_name = 'u210490590_nsqli'; // عدل حسب اسم قاعدة البيانات

// إنشاء نسخة احتياطية لقاعدة البيانات
$dump_file = 'db_backup_' . date('Y-m-d_H-i-s') . '.sql';
$dump_command = "mysqldump --host=$db_host --user=$db_user --password=$db_pass $db_name > $dump_file";
system($dump_command);

// إضافة قاعدة البيانات إلى ملف ZIP
$zip->open($backup_file);
$zip->addFile($dump_file);
$zip->close();

// حذف ملف قاعدة البيانات بعد الإضافة
unlink($dump_file);

// توفير رابط تحميل النسخة الاحتياطية
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . basename($backup_file) . '"');
header('Content-Length: ' . filesize($backup_file));
readfile($backup_file);

// حذف الملف بعد التنزيل
unlink($backup_file);
?>
