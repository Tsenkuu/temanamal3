<?php
$folder = __DIR__ . "/assets/uploads/bukti/";

// 1. Cek apakah folder ada
if (!is_dir($folder)) {
    die("❌ Folder $folder tidak ditemukan");
}

// 2. Cek permission numerik
$perms = substr(sprintf('%o', fileperms($folder)), -4);

// 3. Cek owner (UID) & group (GID)
$owner = fileowner($folder);
$group = filegroup($folder);

// 4. Coba resolve ke nama user/group (kalau server izinkan)
$owner_name = function_exists('posix_getpwuid') ? posix_getpwuid($owner)['name'] : $owner;
$group_name = function_exists('posix_getgrgid') ? posix_getgrgid($group)['name'] : $group;

// 5. Cek writable oleh PHP
$writable = is_writable($folder) ? "✅ Bisa ditulis" : "❌ Tidak bisa ditulis";

// Output hasil cek
echo "<h3>Debug Folder Upload</h3>";
echo "Lokasi: $folder <br>";
echo "Permission: $perms <br>";
echo "Owner: $owner_name ($owner) <br>";
echo "Group: $group_name ($group) <br>";
echo "Status: $writable <br>";
?>
