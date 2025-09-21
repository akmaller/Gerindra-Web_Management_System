<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Menu;
use App\Models\Page;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NewsPostSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@gerindrawebmanagement.com')->first() ?? User::first();

        if (! $admin) {
            $this->command?->warn('Tidak ada user ditemukan untuk penulis post. Seeder NewsPostSeeder dilewati.');
            return;
        }

        $categoryDefinitions = [
            [
                'slug' => 'politik-nasional',
                'name' => 'Politik Nasional',
                'description' => 'Berita dan pernyataan resmi Partai Gerindra seputar dinamika politik nasional.',
            ],
            [
                'slug' => 'infrastruktur-bencana',
                'name' => 'Infrastruktur dan Bencana',
                'description' => 'Respons Gerindra terhadap isu infrastruktur dan penanganan bencana.',
            ],
            [
                'slug' => 'internasional-diplomasi',
                'name' => 'Internasional & Diplomasi',
                'description' => 'Aktivitas Presiden Prabowo dan Partai Gerindra di kancah internasional.',
            ],
            [
                'slug' => 'pendidikan-sekolah-rakyat',
                'name' => 'Pendidikan & Sekolah Rakyat',
                'description' => 'Program Sekolah Rakyat dan pemerataan pendidikan.',
            ],
        ];

        $categories = collect($categoryDefinitions)->mapWithKeys(fn ($data) => [
            $data['slug'] => Category::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'is_active' => true,
                ]
            ),
        ]);

        $tagMap = collect([
            'demokrasi',
            'perang kognitif',
            'blora',
            'banjir',
            'bali',
            'infrastruktur',
            'kesehatan masyarakat',
            'sulteng',
            'jepang',
            'expo osaka',
            'pemerintahan',
            'kabinet',
            'diplomasi',
            'abu dhabi',
            'sekolah rakyat',
            'teknologi pendidikan',
            'brics',
        ])->mapWithKeys(fn ($name) => [Str::slug($name) => Tag::updateOrCreate(
            ['slug' => Str::slug($name)],
            ['name' => Str::title($name)]
        )->id]);

        $posts = [
            [
                'title' => 'Perang Kognitif di Blora, Aziz Subekti Ingatkan Demokrasi Bisa Direbut dari Akar Pikiran',
                'slug' => 'perang-kognitif-di-blora-azis-subekti-ingatkan-demokrasi-bisa-direbut-dari-akar-pikiran',
                'categories' => ['politik-nasional'],
                'published_at' => '2025-09-21 08:00:00',
                'excerpt' => 'Aziz Subekti menegaskan pentingnya kewaspadaan terhadap perang kognitif yang berupaya merebut demokrasi dari akar pikiran masyarakat.',
                'content' => <<<'HTML'
<p>Ketua DPC Partai Gerindra Blora, Aziz Subekti, menegaskan bahwa perang kognitif kini menjadi ancaman nyata bagi keberlangsungan demokrasi. Menurutnya, opini publik yang dikelola secara sistematis bisa menggerus kepercayaan masyarakat terhadap institusi negara.</p>
<p>Aziz mengajak seluruh kader Gerindra untuk membangun ketahanan informasi hingga ke tingkat akar rumput. Ia menekankan pentingnya literasi digital dan keaktifan warga dalam memverifikasi informasi sebelum menyebarkannya. “Demokrasi bisa dicuri tanpa suara jika pikiran publik diarahkan secara salah,” ujarnya.</p>
HTML,
                'tags' => ['demokrasi', 'perang kognitif', 'blora'],
            ],
            [
                'title' => 'Andi Iwan Aras Soroti Banjir di Bali, Minta Pemerintah Bangun Infrastruktur Pengendali',
                'slug' => 'andi-iwan-aras-soroti-banjir-di-bali-minta-pemerintah-bangun-infrastruktur-pengendali',
                'categories' => ['infrastruktur-bencana'],
                'published_at' => '2025-09-20 09:00:00',
                'excerpt' => 'Wakil Ketua Komisi V DPR RI Andi Iwan Aras meminta pemerintah memperkuat infrastruktur pengendali banjir di Bali setelah hujan ekstrem.',
                'content' => <<<'HTML'
<p>Wakil Ketua Komisi V DPR RI dari Partai Gerindra, Andi Iwan Aras, menyoroti banjir yang melanda sejumlah wilayah di Bali. Ia mendorong pemerintah pusat dan daerah mempercepat pembangunan infrastruktur pengendali banjir.</p>
<p>Menurut Andi, perencanaan tata ruang harus disesuaikan dengan karakteristik wilayah, sementara program normalisasi sungai wajib diiringi edukasi kepada masyarakat. “Penguatan infrastruktur menjadi kunci agar pariwisata dan aktivitas warga tetap berjalan,” tegasnya.</p>
HTML,
                'tags' => ['banjir', 'bali', 'infrastruktur'],
            ],
            [
                'title' => 'Kasus Keracunan MBG di Sulteng, Longki Djanggola Tegaskan Pengelolaan Dapur Harus Ketat',
                'slug' => 'kasus-keracunan-mbg-di-sulteng-longki-djanggola-tegaskan-pengelolaan-dapur-harus-ketat',
                'categories' => ['infrastruktur-bencana'],
                'published_at' => '2025-09-20 12:00:00',
                'excerpt' => 'Longki Djanggola meminta pengelola MBG memperketat standar dapur setelah insiden keracunan massal di Sulawesi Tengah.',
                'content' => <<<'HTML'
<p>Anggota DPR RI Fraksi Gerindra, Longki Djanggola, menyoroti kasus keracunan makanan yang melibatkan menu dari Multi Bina Guna (MBG) di Sulawesi Tengah. Ia mendesak pemerintah daerah memastikan semua penyedia konsumsi menerapkan standar higienitas yang ketat.</p>
<p>Longki menegaskan pentingnya inspeksi berkala dan sertifikasi pelaku usaha katering. “Kita tidak bisa kompromi terhadap keselamatan masyarakat. Pengawasan harus diperketat, terutama dalam acara-acara besar,” ujar mantan Gubernur Sulteng itu.</p>
HTML,
                'tags' => ['kesehatan masyarakat', 'sulteng'],
            ],
            [
                'title' => 'Tiba di Jepang, Presiden Prabowo Kunjungi Paviliun Indonesia di Expo 2025 Osaka',
                'slug' => 'tiba-di-jepang-presiden-prabowo-kunjungi-paviliun-indonesia-di-expo-2025-osaka',
                'categories' => ['internasional-diplomasi'],
                'published_at' => '2025-09-20 15:00:00',
                'excerpt' => 'Presiden Prabowo meninjau kesiapan Paviliun Indonesia di Expo 2025 Osaka sebagai upaya memperkuat promosi investasi dan budaya.',
                'content' => <<<'HTML'
<p>Presiden Prabowo Subianto mengawali kunjungan kerjanya di Jepang dengan meninjau Paviliun Indonesia di Expo 2025 Osaka. Didampingi sejumlah menteri, Presiden memastikan kesiapan partisipasi Indonesia dalam pameran internasional tersebut.</p>
<p>Prabowo menekankan pentingnya paviliun sebagai etalase kekuatan ekonomi dan budaya Indonesia kepada dunia. Ia berharap sinergi antara pemerintah dan pelaku usaha mampu menarik investasi baru dari Jepang serta negara-negara lain.</p>
HTML,
                'tags' => ['jepang', 'expo osaka', 'diplomasi'],
            ],
            [
                'title' => 'Presiden Prabowo Subianto Lantik Menteri hingga Kepala Badan Komunikasi Pemerintah',
                'slug' => 'presiden-prabowo-subianto-lantik-menteri-hingga-kepala-badan-komunikasi-pemerintah',
                'categories' => ['politik-nasional'],
                'published_at' => '2025-09-17 09:30:00',
                'excerpt' => 'Presiden Prabowo melantik sejumlah pejabat kabinet dan Kepala Badan Komunikasi Pemerintah untuk memperkuat tata kelola pemerintahan.',
                'content' => <<<'HTML'
<p>Presiden Prabowo Subianto secara resmi melantik beberapa anggota kabinet serta Kepala Badan Komunikasi Pemerintah di Istana Negara. Pelantikan ini dilakukan untuk memperkuat koordinasi pemerintahan dan pelayanan publik.</p>
<p>Presiden menekankan agar pejabat yang dilantik segera bekerja, menjaga integritas, dan memastikan program prioritas berjalan optimal. “Setiap jabatan adalah amanah rakyat. Laksanakan tugas dengan profesional dan transparan,” pesan Prabowo.</p>
HTML,
                'tags' => ['pemerintahan', 'kabinet'],
            ],
            [
                'title' => 'Presiden Prabowo Subianto Tinjau Lokasi Banjir di Bali, Tegaskan Penanganan Cepat dan Tepat',
                'slug' => 'presiden-prabowo-subianto-tinjau-lokasi-banjir-di-bali-tegaskan-penanganan-cepat-dan-tepat',
                'categories' => ['infrastruktur-bencana', 'politik-nasional'],
                'published_at' => '2025-09-13 11:00:00',
                'excerpt' => 'Presiden Prabowo meninjau lokasi banjir di Bali dan memerintahkan aparat mempercepat penanganan serta relokasi warga terdampak.',
                'content' => <<<'HTML'
<p>Usai menerima laporan banjir besar di Bali, Presiden Prabowo Subianto langsung meninjau lokasi terdampak. Ia berkoordinasi dengan pemerintah daerah dan aparat TNI/Polri untuk mempercepat evakuasi serta penyaluran bantuan.</p>
<p>Prabowo meminta agar proses rehabilitasi infrastruktur dilakukan segera setelah kondisi aman. “Kecepatan penanganan menentukan pemulihan kehidupan warga. Pemerintah pusat siap memberi dukungan penuh,” ujarnya.</p>
HTML,
                'tags' => ['banjir', 'bali', 'infrastruktur'],
            ],
            [
                'title' => 'Pertemuan Hangat Presiden Prabowo dan Presiden MBZ, Tegaskan Kedekatan Indonesia-PEA',
                'slug' => 'pertemuan-hangat-presiden-prabowo-dan-presiden-mbz-tegaskan-kedekatan-indonesia-pea',
                'categories' => ['internasional-diplomasi'],
                'published_at' => '2025-09-13 15:30:00',
                'excerpt' => 'Presiden Prabowo dan Presiden MBZ membahas peningkatan kerja sama ekonomi, energi, dan investasi strategis antara Indonesia dan PEA.',
                'content' => <<<'HTML'
<p>Dalam lawatan kenegaraan ke Abu Dhabi, Presiden Prabowo Subianto bertemu Presiden Persatuan Emirat Arab (PEA) Mohamed bin Zayed (MBZ). Keduanya membicarakan langkah konkret memperkuat kerja sama ekonomi dan energi.</p>
<p>Prabowo menyampaikan apresiasi atas hubungan baik kedua negara serta dukungan PEA terhadap pembangunan di Indonesia. Pertemuan berlangsung hangat dan menghasilkan komitmen untuk memperluas investasi serta proyek bersama.</p>
HTML,
                'tags' => ['diplomasi', 'abu dhabi'],
            ],
            [
                'title' => 'Presiden Prabowo dan Presiden MBZ Bahas Geopolitik dan Penguatan Kerja Sama Bilateral',
                'slug' => 'presiden-prabowo-dan-presiden-mbz-bahas-geopolitik-dan-penguatan-kerja-sama-bilateral',
                'categories' => ['internasional-diplomasi'],
                'published_at' => '2025-09-13 18:00:00',
                'excerpt' => 'Presiden Prabowo dan Presiden MBZ mendalami isu geopolitik serta peluang kerja sama baru di sektor pertahanan dan teknologi.',
                'content' => <<<'HTML'
<p>Pada pertemuan lanjutan di Abu Dhabi, Presiden Prabowo dan Presiden MBZ mengulas dinamika geopolitik kawasan. Mereka menilai pentingnya kerja sama antarnegara untuk menjaga stabilitas dan perdamaian.</p>
<p>Kedua pemimpin sepakat meningkatkan kolaborasi di bidang pertahanan, teknologi, dan energi baru. “Kerja sama yang saling menguntungkan akan memperkuat posisi strategis Indonesia dan PEA,” ujar Prabowo.</p>
HTML,
                'tags' => ['diplomasi', 'abu dhabi'],
            ],
            [
                'title' => 'Presiden Prabowo Tiba di Abu Dhabi, Bahas Kerja Sama dan Isu Strategis dengan Presiden MBZ',
                'slug' => 'presiden-prabowo-tiba-di-abu-dhabi-bahas-kerja-sama-dan-isu-strategis-dengan-presiden-mbz',
                'categories' => ['internasional-diplomasi'],
                'published_at' => '2025-09-13 20:00:00',
                'excerpt' => 'Presiden Prabowo memulai agenda kenegaraan di Abu Dhabi dengan menekankan pentingnya dukungan PEA terhadap pembangunan Indonesia.',
                'content' => <<<'HTML'
<p>Presiden Prabowo Subianto tiba di Abu Dhabi dan disambut hangat oleh Presiden MBZ. Dalam pertemuan awal, Prabowo membahas penguatan kerja sama ekonomi, investasi energi terbarukan, dan pendidikan.</p>
<p>Ia menyoroti pentingnya sinergi kedua negara dalam menghadapi tantangan global termasuk krisis energi dan pangan. Pertemuan tersebut menjadi pintu pembuka bagi penandatanganan kerja sama baru.</p>
HTML,
                'tags' => ['diplomasi', 'abu dhabi'],
            ],
            [
                'title' => 'Presiden Prabowo Dorong Pemanfaatan Teknologi untuk Perkuat Sekolah Rakyat',
                'slug' => 'presiden-prabowo-dorong-pemanfaatan-teknologi-untuk-perkuat-sekolah-rakyat',
                'categories' => ['pendidikan-sekolah-rakyat'],
                'published_at' => '2025-09-12 09:00:00',
                'excerpt' => 'Presiden Prabowo mendorong pemanfaatan teknologi digital untuk meningkatkan kualitas pembelajaran di Sekolah Rakyat.',
                'content' => <<<'HTML'
<p>Presiden Prabowo Subianto menilai teknologi merupakan kunci untuk mempercepat pemerataan pendidikan. Saat bertemu pengelola Sekolah Rakyat, ia meminta pemerintah daerah memfasilitasi konektivitas internet dan perangkat belajar yang memadai.</p>
<p>Prabowo juga mengajak dunia industri mendukung penyediaan perangkat belajar pintar. Menurutnya, kolaborasi multi pihak akan memperkuat program Sekolah Rakyat sebagai ujung tombak peningkatan kualitas SDM.</p>
HTML,
                'tags' => ['sekolah rakyat', 'teknologi pendidikan'],
            ],
            [
                'title' => 'Presiden Prabowo Tinjau Sekolah Rakyat Margaguna, Pastikan Fasilitas Layak untuk Siswa',
                'slug' => 'presiden-prabowo-tinjau-sekolah-rakyat-margaguna-jakarta-selatan-pastikan-fasilitas-layak-untuk-siswa',
                'categories' => ['pendidikan-sekolah-rakyat'],
                'published_at' => '2025-09-12 12:00:00',
                'excerpt' => 'Presiden Prabowo meninjau Sekolah Rakyat Margaguna di Jakarta Selatan dan memastikan fasilitas belajar memenuhi standar.',
                'content' => <<<'HTML'
<p>Didampingi Menteri Pendidikan, Presiden Prabowo meninjau langsung proses belajar di Sekolah Rakyat Margaguna, Jakarta Selatan. Ia mengecek fasilitas kelas, ruang praktik, hingga kualitas makanan bergizi bagi siswa.</p>
<p>Prabowo menegaskan bahwa kesejahteraan guru dan kelengkapan sarana menjadi prioritas. “Sekolah rakyat harus menjadi role model pendidikan yang ramah bagi seluruh anak Indonesia,” katanya.</p>
HTML,
                'tags' => ['sekolah rakyat'],
            ],
            [
                'title' => 'Presiden Prabowo Subianto Targetkan 500 Sekolah Rakyat Demi Pemerataan Pendidikan',
                'slug' => 'presiden-prabowo-subianto-targetkan-500-sekolah-rakyat-demi-pemerataan-pendidikan',
                'categories' => ['pendidikan-sekolah-rakyat', 'politik-nasional'],
                'published_at' => '2025-09-12 15:30:00',
                'excerpt' => 'Presiden Prabowo menargetkan pembangunan 500 Sekolah Rakyat sebagai langkah strategis pemerataan akses pendidikan.',
                'content' => <<<'HTML'
<p>Presiden Prabowo Subianto menginstruksikan jajarannya untuk membangun sedikitnya 500 Sekolah Rakyat di seluruh Indonesia. Program ini ditujukan agar anak-anak di daerah 3T mendapat layanan pendidikan bermutu.</p>
<p>Prabowo menekankan pentingnya dukungan pemerintah daerah dan partisipasi masyarakat. “Pendidikan adalah investasi bangsa. Sekolah Rakyat menjadi motor penggerak pemerataan kesempatan belajar,” jelasnya.</p>
HTML,
                'tags' => ['sekolah rakyat'],
            ],
            [
                'title' => 'Presiden Prabowo Subianto Tekankan Peran BRICS Jadi Pilar Penting Stabilitas Global',
                'slug' => 'presiden-prabowo-subianto-tekankan-peran-brics-jadi-pilar-penting-stabilitas-global',
                'categories' => ['politik-nasional', 'internasional-diplomasi'],
                'published_at' => '2025-09-09 10:00:00',
                'excerpt' => 'Presiden Prabowo menekankan bahwa BRICS harus menjadi pilar stabilitas global melalui kerja sama ekonomi dan keamanan yang setara.',
                'content' => <<<'HTML'
<p>Dalam pidato di forum internasional, Presiden Prabowo Subianto menegaskan bahwa BRICS berpotensi menjadi pilar penting stabilitas global. Ia mendorong negara-negara anggota untuk mengedepankan prinsip keadilan, kesetaraan, dan keberlanjutan.</p>
<p>Prabowo menilai kolaborasi BRICS harus memprioritaskan ketahanan ekonomi, teknologi, dan keamanan kolektif. Indonesia siap mengambil peran aktif untuk menjaga perdamaian dunia.</p>
HTML,
                'tags' => ['brics', 'diplomasi'],
            ],
        ];

        foreach ($posts as $data) {
            $categorySlugs = $data['categories'] ?? [];
            $categoryIds = collect($categorySlugs)
                ->map(fn ($slug) => $categories[$slug]->id ?? null)
                ->filter()
                ->values();

            $post = Post::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'user_id' => $admin->id,
                    'category_id' => $categoryIds->first(),
                    'title' => $data['title'],
                    'excerpt' => $data['excerpt'],
                    'content' => $data['content'],
                    'status' => 'published',
                    'published_at' => Carbon::parse($data['published_at']),
                    'is_featured' => false,
                    'is_pinned' => false,
                ]
            );

            $tagIds = collect($data['tags'] ?? [])
                ->map(fn ($name) => $tagMap[Str::slug($name)] ?? null)
                ->filter()
                ->all();

            $post->tags()->sync($tagIds);
            $post->categories()->sync($categoryIds->all());
        }

        $this->seedMenus();
    }

    protected function seedMenus(): void
    {
        $gerindraMenu = Menu::updateOrCreate(
            [
                'parent_id' => null,
                'location' => 'header',
                'label' => 'Gerindra',
            ],
            [
                'item_type' => 'page',
                'open_in_new_tab' => false,
                'is_active' => true,
                'sort_order' => 5,
            ]
        );

        $profileMenu = Menu::updateOrCreate(
            [
                'parent_id' => $gerindraMenu->id,
                'location' => 'header',
                'label' => 'Profil',
            ],
            [
                'item_type' => 'page',
                'open_in_new_tab' => false,
                'is_active' => true,
                'sort_order' => 0,
            ]
        );

        $slugs = [
            'profil-partai-gerindra',
            'deklarasi-partai-gerindra',
            'sejarah-partai-gerindra',
            'visi-misi',
            'tugas-fungsi',
            'makna-lambang',
            'anggaran-dasar-anggaran-rumah-tangga',
            'manifesto-perjuangan-partai-gerindra',
            'susunan-pengurus-dpp-gerindra',
            'struktur-organisasi-partai-gerindra',
        ];

        $pages = Page::whereIn('slug', $slugs)->get()->keyBy('slug');

        foreach ($slugs as $index => $slug) {
            $page = $pages->get($slug);

            if (! $page) {
                continue;
            }

            Menu::updateOrCreate(
                [
                    'parent_id' => $profileMenu->id,
                    'page_id' => $page->id,
                    'location' => 'header',
                ],
                [
                    'label' => $page->title,
                    'item_type' => 'page',
                    'open_in_new_tab' => false,
                    'is_active' => true,
                    'sort_order' => $index,
                ]
            );
        }
    }
}
