<?php

namespace Database\Seeders;

use App\Models\AgendaDisposisi;
use App\Models\AgendaKegiatan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class AgendaKegiatanSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::query()->limit(5)->get();
        if ($users->isEmpty()) {
            return;
        }

        $creatorId = $users->first()->id;
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $soonStart = Carbon::now()->addMinutes(25)->format('H:i:00');

        $agendas = [
            [
                'judul' => 'Rapat Koordinasi Badan Koordinasi Kehumasan',
                'tanggal' => $today->toDateString(),
                'jam_mulai' => $soonStart,
                'jam_selesai' => null,
                'tempat' => 'Balairung Lansek Manih',
                'asal_undangan' => 'Bupati Sijunjung',
                'keterangan' => 'Monitoring dan evaluasi pengelolaan media sosial OPD.',
                'instruksi' => 'Hadir, bawa 1 org admin',
            ],
            [
                'judul' => 'Evaluasi Kinerja & Penyerapan Anggaran Triwulan',
                'tanggal' => $today->toDateString(),
                'jam_mulai' => '14:00:00',
                'jam_selesai' => '16:00:00',
                'tempat' => 'Ruangan Operation Room Kantor Bupati',
                'asal_undangan' => 'Sekretariat Daerah',
                'keterangan' => 'Pembahasan progres kegiatan dan SPJ.',
                'instruksi' => 'Wajib hadir seluruh Kabid',
            ],
            [
                'judul' => 'Apel Gabungan & Pembinaan Disiplin ASN',
                'tanggal' => $yesterday->toDateString(),
                'jam_mulai' => '07:30:00',
                'jam_selesai' => '09:00:00',
                'tempat' => 'Lapangan M.Yamin',
                'asal_undangan' => 'BKPSDM Sijunjung',
                'keterangan' => 'Apel pagi mingguan.',
                'instruksi' => 'Seluruh ASN hadir tepat waktu',
            ],
        ];

        foreach ($agendas as $data) {
            $instruksi = $data['instruksi'];
            unset($data['instruksi']);

            $agenda = AgendaKegiatan::query()->firstOrCreate(
                [
                    'judul' => $data['judul'],
                    'tanggal' => $data['tanggal'],
                ],
                array_merge($data, ['created_by' => $creatorId]),
            );

            foreach ($users as $user) {
                AgendaDisposisi::query()->firstOrCreate(
                    [
                        'agenda_kegiatan_id' => $agenda->id,
                        'user_id' => $user->id,
                    ],
                    [
                        'instruksi' => $instruksi,
                    ],
                );
            }
        }
    }
}
