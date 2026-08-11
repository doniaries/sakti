<?php
$json_path = 'c:\laragon\www\absensilhk\database\seeders\pegawai_80.json';
$db_path = 'c:\Users\donia\Downloads\absen\backup.db';

$json_data = json_decode(file_get_contents($json_path), true);

$pdo = new PDO("sqlite:" . $db_path);
$stmt = $pdo->query("SELECT emp_pin, emp_firstname, emp_lastname FROM hr_employee");
$machine_users = $stmt->fetchAll(PDO::FETCH_ASSOC);

function cleanName($name) {
    // Remove titles after comma
    $name = preg_replace('/,.*$/', '', $name);
    // Remove common title patterns
    $name = str_replace(['S.IP', 'M.CIO', 'SH', 'SE', 'MT', 'SP', 'SS', 'ME', 'S.Pt', 'A.Md', 'S.A.P', 'S.ST'], '', $name);
    $name = preg_replace('/[^a-zA-Z\s]/', '', $name);
    return trim(strtoupper($name));
}

$mapped = 0;
foreach ($json_data as &$pegawai) {
    $clean_json_name = cleanName($pegawai['nama']);
    $best_match = null;
    $best_score = 0;

    foreach ($machine_users as $mu) {
        $clean_mu_name = cleanName($mu['emp_firstname'] . ' ' . $mu['emp_lastname']);
        
        if ($clean_json_name === $clean_mu_name) {
            $best_match = $mu['emp_pin'];
            $best_score = 100;
            break;
        }

        similar_text($clean_json_name, $clean_mu_name, $percent);
        if ($percent > $best_score) {
            $best_score = $percent;
            $best_match = $mu['emp_pin'];
        }
    }

    if ($best_score > 70) {
        $pegawai['nomor_absensi'] = (string)$best_match;
        $mapped++;
    } else {
        $pegawai['nomor_absensi'] = null;
    }
}

echo "Mapped $mapped out of " . count($json_data) . " employees.\n";
file_put_contents('database/seeders/pegawai_80.json', json_encode($json_data, JSON_PRETTY_PRINT));
