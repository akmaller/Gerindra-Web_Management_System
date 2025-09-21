
<div class="max-w-7xl mx-auto">
    <x-ad-slot location="footer" />
</div>
<footer class="bg-[color:var(--brand-primary)] text-[color:var(--brand-primary-contrast)] py-10 mt-12">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

            {{-- KIRI: Logo & Deskripsi --}}
            <div>
                <div class="flex items-center space-x-2 mb-4">
                    @if(!empty($settings?->logo_path))
                        <img src="{{ asset('storage/' . $settings->logo_path) }}" alt="{{ $settings->site_name }}" class="h-10">
                    @else
                        <span class="text-xl font-bold text-[color:var(--brand-primary-contrast)]">{{ $settings->site_name ?? 'Gerindra-Web_Management' }}</span>
                    @endif
                </div>
                <p class="text-sm leading-relaxed text-[color:var(--brand-primary-contrast)] opacity-90">
                    {{ $settings->site_description ?? 'Portal berita Gerindra-Web_Management.' }}
                </p>
            </div>

            {{-- TENGAH: Info Kontak --}}
            <div>
                <h4 class="text-[color:var(--brand-primary-contrast)] font-semibold mb-2">Kontak Kami</h4>
                <ul class="space-y-2 text-sm">
                    @if(!empty($profile?->company_name))
                        <li><span class="font-medium"></span> {{ $profile->company_name }}</li>
                    @endif
                    @if(!empty($profile?->address))
                        <li><span class="font-medium">Alamat:</span> {{ $profile->address }}</li>
                    @endif
                    @if(!empty($profile?->phone))
                        <li><span class="font-medium">Telepon:</span> {{ $profile->phone }}</li>
                    @endif
                    @if(!empty($profile?->email))
                        <li><span class="font-medium">Email:</span> <a href="mailto:{{ $profile->email }}" class="text-[color:var(--brand-primary-contrast)] hover:opacity-80">{{ $profile->email }}</a></li>
                    @endif
                </ul>
            </div>

            {{-- KANAN: Copyright --}}
            <div class="flex flex-col justify-between">
                <div class="text-sm text-right md:text-left">
                    <p class="mb-2">Ikuti Kami:</p>
                    <div class="flex space-x-3 justify-end md:justify-start">
                        @include('partials.social-icons', ['profile' => $settings, 'size' => 22])
                    </div>
                </div>
                <div class="text-xs text-[color:var(--brand-primary-contrast)] mt-6 md:mt-0 text-right md:text-left opacity-70">
                    &copy; {{ date('Y') }} {{ $settings->site_name ?? 'Gerindra-Web_Management' }}. Semua Hak Dilindungi.
                </div>
            </div>

        </div>
    </div>
</footer>
