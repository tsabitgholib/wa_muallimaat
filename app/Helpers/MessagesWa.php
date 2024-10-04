<?php

namespace App\Helpers;


final class MessagesWa
{

    public function wa($message)
    {

        $pesan = [
            "Assalamu'alaikum Warahmatullahi wabarakatuh
            \nKami dari keuangan Ibnu Abbas 
            \nMemberitahukan kepada wali santri atas nama {nama_anak} 
            \nMasih ada kekurangan pembayaran sebesar {jumlah_tagihan}, dengan rincian :
            {rincian}
            \nMohon untuk segera di tunaikan, atas perhatiannya kami sampaikan
            \nJazaakumullah khairan katsiran.
            \n\n*_*pesan otomatis dari " . config('app.nama_instansi') . "_*\n*_*silahkan hubingi admin sekolah jika ada kesalahan tagihan atau nama siswa_*",

            "-السلام عليكم ورحمة الله وبركاته-
            \nKami dari Ibnu Abbas, memberitahukan kepada wali santri atas nama {nama_anak} 
            \nAnanda masih memiliki kekurangan pembayaran sebesar {jumlah_tagihan}, dengan rincian :
            {rincian}
            \nMohon untuk segera di tunaikan, atas perhatiannya kami sampaikan
            \nJazaakumullah khairan katsiran.
            \n\n*_*pesan otomatis dari " . config('app.nama_instansi') . "_*\n*_*silahkan hubingi admin sekolah jika ada kesalahan tagihan atau nama siswa_*",

            "Dengan hormat, kami sampaikan kepada Bapak/Ibu {nama_orang_tua}, bahwa tagihan untuk ananda {nama_anak} sebesar {jumlah_tagihan} sudah harus dibayarkan.
            \nRincian :{rincian}\nTerima kasih atas perhatian dan kerjasamanya. Wassalam 🙏.
            \n\n*_*pesan otomatis dari " . config('app.nama_instansi') . "_*\n*_*silahkan hubingi admin sekolah jika ada kesalahan tagihan atau nama siswa_*",

            "Assalamualaikum Wr Wb,
            \nBa'da tahmid, tahlil dan shalawat bagi kita semua. Kami ingin menginformasikan kepada Anda, orang tua ananda {nama_anak}, untuk tagihan anak Anda sebesar {jumlah_tagihan}.
            \nDengan Rincian :{rincian}\nDemikian pesan dari kami. Wassalam 🙏.\n\n*_*pesan otomatis dari " . config('app.nama_instansi') . "_*\n*_*silahkan hubingi admin sekolah jika ada kesalahan tagihan atau nama siswa_*",
        ];
        return $pesan;
    }
}
