<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Menu;
use App\Models\Page;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Rebuilds the public Sumsel navigation without duplicating rows.
 * Content is intentionally a short placeholder so editors can fill it in.
 */
class SumselNavigationSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'pendidikan-kebangsaan' => 'Pendidikan dan Kebangsaan',
            'pertanian-lingkungan' => 'Pertanian dan Lingkungan',
            'politik' => 'Politik',
            'berita-nasional' => 'Berita Nasional',
        ] as $slug => $name) {
            Category::updateOrCreate(['slug' => $slug], ['name' => $name]);
        }

        $page = static function (string $title): Page {
            return Page::firstOrCreate([
                'slug' => Str::slug($title),
            ], [
                'title' => $title,
                'content' => '<h1>'.e($title).'</h1>',
                'is_active' => true,
            ]);
        };

        $menu = static function (string $label, ?int $parentId = null, ?Page $page = null): Menu {
            return Menu::updateOrCreate([
                'location' => 'header',
                'label' => $label,
                'parent_id' => $parentId,
            ], [
                'item_type' => $page ? 'page' : 'url',
                'page_id' => $page?->id,
                'url' => $page ? null : '#',
                'is_active' => true,
                'sort_order' => 0,
                'open_in_new_tab' => false,
            ]);
        };

        $roots = [];
        foreach (['Gerindra', 'Aktivitas', 'Lapor Gerindra', 'Informasi Publik'] as $label) {
            $roots[$label] = $menu($label);
        }

        $groups = [
            'Gerindra' => [
                'Profil Partai', 'Profil Pimpinan', 'Calon Legislatif',
                'Calon Eksekutif', 'Anggota Legislatif', 'Anggota Eksekutif',
                'Ambulance Gerindra', 'Kantor Gerindra', 'Sayap Partai',
                'Program Partai Gerindra', 'Media Sosial',
            ],
            'Aktivitas' => ['Berita Gerindra', 'Timeline Gerindra', 'Suara Gerindra', 'Aksi Nyata'],
            'Lapor Gerindra' => ['Lapor Gerindra'],
            'Informasi Publik' => ['PPID', 'LHKPN Pimpinan Partai', 'Ketetapan dan Pedoman',
                'Daftar Informasi Publik', 'Donasi', 'E-PPID'],
        ];

        foreach ($groups as $root => $labels) {
            foreach ($labels as $label) {
                $child = $menu($label, $roots[$root]->id, $page($label));

                if ($label === 'Profil Pimpinan') {
                    foreach (['Ketua Umum', 'Ketua Harian', 'Sekretaris Jenderal', 'Bendahara Umum'] as $sub) {
                        $menu($sub, $child->id, $page($sub));
                    }
                }

                if ($label === 'PPID') {
                    foreach (['Profil PPID', 'Ketentuan dan SOP PPID', 'Rencana Kebijakan Layanan', 'Formulir Permintaan Informasi'] as $sub) {
                        $menu($sub, $child->id, $page($sub));
                    }
                }
            }
        }

        // Legacy duplicate roots are kept for backward compatibility but hidden.
        Menu::whereIn('label', ['PPID', 'Berita'])
            ->whereNull('parent_id')
            ->where('location', 'header')
            ->update(['is_active' => false]);
    }
}
