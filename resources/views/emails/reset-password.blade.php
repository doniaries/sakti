<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atur Ulang Kata Sandi SAKTI</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; color: #334155;">

    <table width="100%" border="0" cellpadding="0" cellspacing="0" style="background-color: #f1f5f9; padding: 40px 15px;">
        <tr>
            <td align="center">
                <!-- Container Card Utama -->
                <table width="100%" border="0" cellpadding="0" cellspacing="0" style="max-width: 580px; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); border: 1px solid #e2e8f0;">
                    
                    <!-- Header Kedinasan -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); padding: 36px 32px 30px 32px; text-align: center; border-bottom: 4px solid #f59e0b;">
                            <div style="display: inline-block; padding: 6px 14px; background-color: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.3); border-radius: 20px; margin-bottom: 12px;">
                                <span style="font-size: 11px; font-weight: 700; color: #fbbf24; letter-spacing: 1.2px; text-transform: uppercase;">
                                    Portal Layanan Pegawai
                                </span>
                            </div>
                            <h1 style="margin: 0; font-size: 26px; font-weight: 800; color: #ffffff; letter-spacing: 0.5px;">
                                SAKTI
                            </h1>
                            <p style="margin: 4px 0 0 0; font-size: 13px; color: #94a3b8; font-weight: 400;">
                                Sistem Administrasi, Kinerja, dan Tata Informasi
                            </p>
                            <p style="margin: 6px 0 0 0; font-size: 11px; color: #cbd5e1; text-transform: uppercase; letter-spacing: 0.8px; font-weight: 600;">
                                Dinas Pariwisata, Pemuda dan Olah Raga Kab. Sijunjung
                            </p>
                        </td>
                    </tr>

                    <!-- Body Konten -->
                    <tr>
                        <td style="padding: 36px 36px 28px 36px;">
                            
                            <!-- Salam & Pengantar -->
                            <h2 style="margin: 0 0 14px 0; font-size: 19px; font-weight: 700; color: #0f172a;">
                                Halo, {{ $user->name }}
                            </h2>
                            <p style="margin: 0 0 22px 0; font-size: 14px; line-height: 1.65; color: #475569;">
                                Kami menerima permintaan untuk mengatur ulang kata sandi akun SAKTI Anda. Untuk menjaga keamanan akun kedinasan Anda, silakan buat kata sandi baru dengan menekan tombol verifikasi di bawah ini:
                            </p>

                            <!-- Tombol Aksi Utama (CTA) -->
                            <table width="100%" border="0" cellpadding="0" cellspacing="0" style="margin: 30px 0 34px 0;">
                                <tr>
                                    <td align="center">
                                        <!--[if mso]>
                                        <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ $resetUrl }}" style="height:48px;v-text-anchor:middle;width:280px;" arcsize="25%" stroke="f" fillcolor="#ea580c">
                                        <w:anchorlock/>
                                        <center style="color:#ffffff;font-family:sans-serif;font-size:14px;font-weight:bold;">Kunci Akun: Atur Ulang Kata Sandi</center>
                                        </v:roundrect>
                                        <![endif]-->
                                        <!--[if !mso]><!-->
                                        <a href="{{ $resetUrl }}" target="_blank" style="display: inline-block; background-color: #ea580c; background-image: linear-gradient(135deg, #f59e0b 0%, #ea580c 50%, #c2410c 100%); color: #ffffff; text-decoration: none; font-size: 14.5px; font-weight: 700; padding: 15px 38px; border-radius: 12px; box-shadow: 0 6px 18px rgba(234, 88, 12, 0.4); letter-spacing: 0.4px; border: 1px solid rgba(255, 255, 255, 0.25);">
                                            &#128274;&nbsp;&nbsp;Atur Ulang Kata Sandi Saya
                                        </a>
                                        <!--<![endif]-->
                                    </td>
                                </tr>
                            </table>

                            <!-- Kotak Informasi Keamanan -->
                            <table width="100%" border="0" cellpadding="0" cellspacing="0" style="background-color: #fffbeb; border: 1px solid #fef3c7; border-left: 4px solid #f59e0b; border-radius: 8px; margin-bottom: 24px;">
                                <tr>
                                    <td style="padding: 14px 16px;">
                                        <p style="margin: 0; font-size: 12.5px; line-height: 1.6; color: #92400e;">
                                            <strong>Catatan Keamanan Penting:</strong><br>
                                            • Tautan ini hanya berlaku selama <strong>{{ $expireMinutes }} menit</strong> sejak email ini dikirimkan.<br>
                                            • Jika Anda <strong>tidak pernah meminta</strong> atur ulang kata sandi, abaikan email ini. Akun Anda tetap aman dan kata sandi lama Anda tidak akan berubah.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Fallback Link Teks -->
                            <div style="border-top: 1px dashed #cbd5e1; padding-top: 20px;">
                                <p style="margin: 0 0 8px 0; font-size: 12px; color: #64748b; line-height: 1.5;">
                                    Jika tombol di atas tidak dapat diklik, salin dan buka tautan berikut langsung pada peramban (browser) Anda:
                                </p>
                                <p style="margin: 0; font-size: 11.5px; color: #d97706; word-break: break-all; line-height: 1.5; background-color: #f8fafc; padding: 10px 12px; border-radius: 6px; border: 1px solid #e2e8f0; font-family: monospace;">
                                    <a href="{{ $resetUrl }}" style="color: #b45309; text-decoration: underline;">{{ $resetUrl }}</a>
                                </p>
                            </div>

                        </td>
                    </tr>

                    <!-- Footer Email -->
                    <tr>
                        <td style="background-color: #f8fafc; padding: 22px 32px; text-align: center; border-top: 1px solid #e2e8f0;">
                            <p style="margin: 0; font-size: 12px; color: #64748b; font-weight: 500;">
                                Pemerintah Kabupaten Sijunjung &bull; Disparpora
                            </p>
                            <p style="margin: 4px 0 0 0; font-size: 11px; color: #94a3b8; line-height: 1.4;">
                                Email ini dibuat secara otomatis oleh sistem SAKTI. Mohon tidak membalas email ini secara langsung (*no-reply*).
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>
