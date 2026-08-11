<?php
$file = 'database/seeders/DataPegawaiSeeder.php';
$content = file_get_contents($file);

$startStr = '$data = [';
$endStr = '];';

$startPos = strpos($content, $startStr);
$endPos = strpos($content, $endStr, $startPos) + 2;

$newArrayStr = <<<'PHP'
$jsonPath = database_path('seeders/pegawai_80.json');
        if (!file_exists($jsonPath)) {
            $this->command->warn('File pegawai_80.json tidak ditemukan!');
            return;
        }

        $dataRaw = json_decode(file_get_contents($jsonPath), true);
        
        $data = [];
        foreach ($dataRaw as $row) {
            $data[] = [
                'name' => $row['nama'] ?? null,
                'nip' => $row['nip'] ?? null,
                'jabatan' => $row['jabatan'] ?? null,
                'pendidikan' => $row['pendidikan'] ?? null,
                'jenis_kelamin' => $row['jk'] ?? null,
                'eselon' => $row['eselon'] ?? null,
                'tempat_lahir' => $row['tempat_lahir'] ?? null,
                'tanggal_lahir' => $row['tanggal_lahir'] ?? null,
                'nomor_absensi' => $row['nomor_absensi'] ?? null,
                'email' => strtolower(str_replace([' ', ',', '.'], '', $row['nama'] ?? '')) . '@example.com',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
            ];
        }
PHP;

$newContent = substr_replace($content, $newArrayStr, $startPos, $endPos - $startPos);
file_put_contents($file, $newContent);
echo "Updated DataPegawaiSeeder.php\n";
