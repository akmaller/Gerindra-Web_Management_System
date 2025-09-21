<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'title' => 'Tentang Kami',
                'slug' => 'tentang-kami',
                'content' => <<<'HTML'
<h2>Pengurus Partai Gerindra</h2>
<p>Partai Gerindra dibangun atas dasar semangat perjuangan untuk mewujudkan Indonesia yang adil, makmur, dan berdaulat. Kami percaya bahwa perubahan bangsa akan terwujud apabila rakyat mendapatkan kesempatan yang setara dalam memperoleh pendidikan, kesehatan, pekerjaan, serta rasa aman.</p>
<p>Pengurus pusat dan daerah Partai Gerindra terus bekerja mendampingi masyarakat. Kami mengajak semua elemen bangsa untuk bersama-sama mewujudkan cita-cita menuju Indonesia Raya.</p>
HTML,
            ],
            [
                'title' => 'Kebijakan dan Privasi',
                'slug' => 'kebijakan-dan-privasi',
                'content' => <<<'HTML'
<h2>Kebijakan Privasi Gerindra-Web_Management</h2>
<p>Gerindra-Web_Management berkomitmen untuk melindungi data pribadi pengunjung. Informasi yang kami kumpulkan hanya digunakan untuk keperluan internal dan tidak akan dibagikan kepada pihak ketiga tanpa persetujuan Anda.</p>
<ul>
    <li>Kami menggunakan data kunjungan untuk meningkatkan kualitas konten.</li>
    <li>Kami tidak menyimpan data sensitif seperti nomor identitas atau informasi keuangan.</li>
    <li>Pengunjung dapat menghubungi kami jika membutuhkan informasi lebih lanjut mengenai penggunaan data.</li>
</ul>
HTML,
            ],
            [
                'title' => 'Perjuangan Partai',
                'slug' => 'perjuangan-partai',
                'content' => <<<'HTML'
<h2>Perjuangan Partai Gerindra</h2>
<p>Gerindra hadir sebagai wadah perjuangan rakyat untuk mempertahankan kedaulatan negara serta memastikan kesejahteraan seluruh anak bangsa. Melalui kerja nyata di parlemen dan masyarakat, Gerindra mendorong kebijakan yang berpihak kepada rakyat kecil.</p>
<p>Kami terus mengawal isu-isu penting seperti kemandirian pangan, kedaulatan energi, pemerataan ekonomi, dan penegakan hukum yang berkeadilan. Perjuangan kami adalah perjuangan rakyat.</p>
HTML,
            ],
            [
                'title' => 'Profil Partai Gerindra',
                'slug' => 'profil-partai-gerindra',
                'content' => <<<'HTML'
<h2>Profil Partai Gerakan Indonesia Raya</h2>
<p>Partai Gerindra lahir sebagai wadah politik perjuangan rakyat untuk mewujudkan Indonesia Raya yang berdaulat, adil, dan makmur. Gerindra memposisikan diri sebagai partai nasionalis yang menjunjung tinggi Pancasila, UUD 1945, Negara Kesatuan Republik Indonesia, dan Bhinneka Tunggal Ika.</p>
<p>Organisasi ini dikembangkan dengan struktur yang kuat dari tingkat pusat hingga ranting. Gerindra mendorong kader untuk terlibat aktif dalam pelayanan sosial, pemberdayaan ekonomi, dan advokasi kebijakan publik.</p>
HTML,
            ],
            [
                'title' => 'Deklarasi Partai Gerindra',
                'slug' => 'deklarasi-partai-gerindra',
                'content' => <<<'HTML'
<h2>Deklarasi Partai Gerindra</h2>
<p>Deklarasi Partai Gerindra menandai komitmen seluruh pendiri, kader, dan simpatisan untuk memperjuangkan Indonesia yang berdaulat di bidang politik, berdikari dalam ekonomi, dan berkepribadian dalam kebudayaan.</p>
<p>Dalam deklarasi ditegaskan bahwa Gerindra siap berdiri di garda terdepan membela kepentingan rakyat, menegakkan hukum secara adil, serta menjaga persatuan dan kesatuan bangsa.</p>
HTML,
            ],
            [
                'title' => 'Sejarah Partai Gerindra',
                'slug' => 'sejarah-partai-gerindra',
                'content' => <<<'HTML'
<h2>Sejarah Singkat Partai Gerindra</h2>
<p>Gerindra didirikan oleh tokoh-tokoh nasional yang memiliki kepedulian mendalam terhadap masa depan bangsa. Sejak awal, partai ini fokus memperkuat kedaulatan rakyat melalui program ekonomi kerakyatan, pembangunan pertahanan nasional, dan pemerintahan yang bersih.</p>
<p>Dalam perjalanannya, Gerindra berkembang menjadi salah satu kekuatan utama dalam politik Indonesia. Keterlibatan aktif di parlemen dan kerja nyata di tengah masyarakat menjadi pijakan untuk terus memperluas basis dukungan.</p>
HTML,
            ],
            [
                'title' => 'Visi dan Misi Partai Gerindra',
                'slug' => 'visi-misi',
                'content' => <<<'HTML'
<h2>Visi Partai Gerindra</h2>
<p>Mewujudkan Indonesia yang berdaulat, adil, dan makmur, di mana seluruh rakyat mendapatkan kesempatan yang sama untuk berkembang dan sejahtera.</p>
<h2>Misi Partai Gerindra</h2>
<ul>
    <li>Meningkatkan kemakmuran rakyat melalui ekonomi kerakyatan yang berkeadilan.</li>
    <li>Memperkuat pertahanan dan keamanan nasional berbasis kekuatan rakyat.</li>
    <li>Menegakkan supremasi hukum yang berkeadilan dan bebas dari korupsi.</li>
    <li>Memperluas akses pendidikan dan kesehatan berkualitas bagi seluruh warga negara.</li>
</ul>
HTML,
            ],
            [
                'title' => 'Tugas dan Fungsi Partai Gerindra',
                'slug' => 'tugas-fungsi',
                'content' => <<<'HTML'
<h2>Tugas Partai Gerindra</h2>
<ul>
    <li>Mengorganisir potensi rakyat untuk berpartisipasi dalam pembangunan nasional.</li>
    <li>Menyalurkan aspirasi rakyat melalui proses politik yang demokratis.</li>
    <li>Menyiapkan kader-kader pemimpin yang berintegritas dan berkompetensi.</li>
</ul>
<h2>Fungsi Partai Gerindra</h2>
<ul>
    <li>Fungsi edukasi politik, memberikan pemahaman tentang hak dan kewajiban warga negara.</li>
    <li>Fungsi artikulasi, memperjuangkan kebijakan publik yang berpihak kepada rakyat.</li>
    <li>Fungsi rekrutmen, membuka ruang bagi putra-putri terbaik bangsa bergabung dalam perjuangan.</li>
</ul>
HTML,
            ],
            [
                'title' => 'Makna Lambang Partai Gerindra',
                'slug' => 'makna-lambang',
                'content' => <<<'HTML'
<h2>Makna Lambang Gerindra</h2>
<p>Lambang burung garuda melambangkan kekuatan, keberanian, dan perlindungan terhadap rakyat. Warna merah mencerminkan semangat juang, sementara warna kuning menggambarkan kesejahteraan dan kemakmuran.</p>
<p>Perisai lima sudut pada lambang menegaskan komitmen terhadap Pancasila. Seluruh unsur lambang disatukan dengan doa “Indonesia Raya” sebagai simbol tekad mewujudkan cita-cita kemerdekaan.</p>
HTML,
            ],
            [
                'title' => 'Anggaran Dasar dan Anggaran Rumah Tangga',
                'slug' => 'anggaran-dasar-anggaran-rumah-tangga',
                'content' => <<<'HTML'
<h2>AD/ART Partai Gerindra</h2>
<p>Anggaran Dasar dan Anggaran Rumah Tangga (AD/ART) Partai Gerindra memuat ketentuan mendasar mengenai struktur organisasi, mekanisme pengambilan keputusan, hak dan kewajiban anggota, serta disiplin partai.</p>
<p>Dokumen ini menjadi pedoman bagi seluruh kader dalam melaksanakan kegiatan organisasi. Di dalamnya tercantum tata cara musyawarah nasional, kongres daerah, pembentukan pengurus, hingga mekanisme evaluasi kinerja.</p>
HTML,
            ],
            [
                'title' => 'Manifesto Perjuangan Partai Gerindra',
                'slug' => 'manifesto-perjuangan-partai-gerindra',
                'content' => <<<'HTML'
<h2>Manifesto Perjuangan</h2>
<p>Manifesto Partai Gerindra menegaskan komitmen untuk memperjuangkan kedaulatan ekonomi, politik, dan budaya. Manifesto ini menjadi rujukan dalam merumuskan program kerja partai, baik jangka pendek maupun jangka panjang.</p>
<p>Isi manifesto mencakup agenda penguatan pangan, energi, pertahanan nasional, pemerataan pembangunan, hingga pelayanan publik yang berkeadilan.</p>
HTML,
            ],
            [
                'title' => 'Susunan Pengurus DPP Partai Gerindra',
                'slug' => 'susunan-pengurus-dpp-gerindra',
                'content' => <<<'HTML'
<h2>Susunan Pengurus Dewan Pimpinan Pusat</h2>
<p>Struktur pengurus DPP Partai Gerindra terdiri dari Ketua Umum, Dewan Pembina, Dewan Pakar, Sekretaris Jenderal, Bendahara, serta departemen-departemen strategis yang menangani isu ekonomi, politik, sosial, dan komunikasi.</p>
<p>Setiap pengurus memiliki mandat untuk memastikan program partai berjalan efektif, sekaligus menjaga hubungan dengan pengurus daerah dan simpatisan di seluruh Indonesia.</p>
HTML,
            ],
            [
                'title' => 'Struktur Organisasi Partai Gerindra',
                'slug' => 'struktur-organisasi-partai-gerindra',
                'content' => <<<'HTML'
<h2>Struktur Organisasi</h2>
<p>Struktur Partai Gerindra tersusun secara berjenjang mulai dari tingkat pusat, daerah, cabang, ranting, hingga anak ranting. Setiap tingkatan memiliki kewenangan dan tanggung jawab yang diatur secara jelas untuk memastikan koordinasi berjalan efektif.</p>
<p>Mekanisme rapat koordinasi, evaluasi kinerja, dan pendidikan politik diselenggarakan secara berkala agar visi perjuangan partai tetap sejalan dari pusat sampai akar rumput.</p>
HTML,
            ],
        ];

        foreach ($pages as $data) {
            Page::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'title' => $data['title'],
                    'content' => $data['content'],
                    'attachment_path' => null,
                    'is_active' => true,
                ]
            );
        }
    }
}
