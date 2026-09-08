<?php

namespace App\Filament\Pages;

use App\Models\ChatbotSetting;
use App\Models\Page as StaticPage;
use App\Models\Post;
use BackedEnum;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ChatbotEmbedSettings extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected static ?string $navigationLabel = 'Chatbot Embed';
    protected static ?string $title = 'Pengaturan Chatbot Embed';
    protected static ?string $slug = 'chatbot-embed';
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected string $view = 'filament.pages.chatbot-embed-settings';

    public ?ChatbotSetting $record = null;
    public ?array $data = [];

    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return 'Pengaturan';
    }

    public static function getNavigationSort(): int
    {
        return 5;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole(['admin', 'editor']);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Status & Integrasi N8N')
                    ->columns(2)
                    ->schema([
                        Toggle::make('module_enabled')
                            ->label('Aktifkan Modul Chatbot')
                            ->helperText('Kontrol utama untuk menampilkan tombol chatbot pada situs.'),

                        TextInput::make('endpoint')
                            ->label('Endpoint Webhook N8N')
                            ->url()
                            ->columnSpan(2)
                            ->required(fn (callable $get) => (bool) $get('module_enabled'))
                            ->helperText('URL webhook n8n yang menerima permintaan percakapan.'),

                        TextInput::make('request_timeout')
                            ->label('Batas Waktu Request (detik)')
                            ->numeric()
                            ->minValue(15)
                            ->maxValue(600)
                            ->default(180)
                            ->helperText('Pastikan selaras dengan timeout workflow n8n.'),
                    ]),

                Section::make('Autentikasi Webhook')
                    ->columns(2)
                    ->schema([
                        Select::make('auth_type')
                            ->label('Skema Autentikasi')
                            ->options([
                                'none' => 'Tanpa Autentikasi',
                                'custom_header' => 'Custom Header',
                            ])
                            ->default('custom_header'),

                        TextInput::make('auth_header_key')
                            ->label('Header Key')
                            ->maxLength(120)
                            ->placeholder('Mis. X-API-Key')
                            ->visible(fn (callable $get) => $get('auth_type') === 'custom_header'),

                        TextInput::make('auth_header_value')
                            ->label('Header Value')
                            ->password()
                            ->revealable()
                            ->maxLength(255)
                            ->columnSpan(2)
                            ->visible(fn (callable $get) => $get('auth_type') === 'custom_header')
                            ->helperText('Nilai akan disimpan terenkripsi di basis data.'),

                        Toggle::make('auth_header_as_bearer')
                            ->label('Gunakan juga sebagai Authorization: Bearer')
                            ->visible(fn (callable $get) => $get('auth_type') === 'custom_header'),
                    ]),

                Section::make('Tampilan Chatbot')
                    ->columns(2)
                    ->schema([
                        TextInput::make('default_title')
                            ->label('Judul Panel Chat')
                            ->maxLength(150),

                        TextInput::make('chat_button_text')
                            ->label('Teks Tombol Chat')
                            ->maxLength(60),

                        Select::make('chat_button_position')
                            ->label('Posisi Tombol')
                            ->options([
                                'bottom-right' => 'Kanan bawah',
                                'bottom-left' => 'Kiri bawah',
                            ])
                            ->default('bottom-right'),

                        FileUpload::make('default_avatar_path')
                            ->label('Avatar Asisten')
                            ->image()
                            ->disk('public')
                            ->directory('chatbot')
                            ->visibility('public')
                            ->helperText('Kosongkan untuk menggunakan avatar bawaan.')
                            ->getUploadedFileNameForStorageUsing(
                                fn ($file) => (string) Str::uuid() . '.' . $file->getClientOriginalExtension()
                            ),
                    ]),

                Section::make('Riwayat Percakapan')
                    ->columns(2)
                    ->schema([
                        Select::make('history_storage')
                            ->label('Metode Penyimpanan')
                            ->options([
                                'ttl' => 'Berdasarkan Waktu (TTL)',
                                'session' => 'Per Sesi Browser',
                                'none' => 'Tidak Menyimpan',
                            ])
                            ->default('ttl'),

                        TextInput::make('history_ttl_minutes')
                            ->label('TTL (menit)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(7200)
                            ->visible(fn (callable $get) => $get('history_storage') === 'ttl')
                            ->helperText('Menentukan berapa lama riwayat tersimpan di browser pengguna.'),
                    ]),

                Section::make('Auto Inject Shortcode')
                    ->columns(2)
                    ->schema([
                        Toggle::make('auto_inject_enabled')
                            ->label('Aktifkan Auto Inject'),

                        Toggle::make('auto_inject_sitewide')
                            ->label('Terapkan ke seluruh konten')
                            ->visible(fn (callable $get) => (bool) $get('auto_inject_enabled')),

                        Select::make('auto_inject_position')
                            ->label('Posisi Shortcode')
                            ->options([
                                'above_content' => 'Di atas konten',
                                'below_content' => 'Di bawah konten',
                                'footer' => 'Footer',
                            ])
                            ->default('below_content')
                            ->visible(fn (callable $get) => (bool) $get('auto_inject_enabled')),

                        Select::make('auto_inject_pages')
                            ->label('Halaman')
                            ->multiple()
                            ->options(fn () => StaticPage::orderBy('title')->pluck('title', 'id')->toArray())
                            ->visible(fn (callable $get) => (bool) $get('auto_inject_enabled') && ! $get('auto_inject_sitewide'))
                            ->placeholder('Cari halaman...')
                            ->searchable(),

                        Select::make('auto_inject_posts')
                            ->label('Posts')
                            ->multiple()
                            ->options(fn () => Post::orderBy('title')->limit(100)->pluck('title', 'id')->toArray())
                            ->visible(fn (callable $get) => (bool) $get('auto_inject_enabled') && ! $get('auto_inject_sitewide'))
                            ->placeholder('Cari post...')
                            ->searchable()
                            ->helperText('Daftar dibatasi 100 post terbaru untuk performa.'),
                    ]),
            ]);
    }

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasAnyRole(['admin', 'editor']), 403);

        $this->record = ChatbotSetting::current();

        $this->form->fill([
            'module_enabled' => $this->record->module_enabled,
            'endpoint' => $this->record->endpoint,
            'default_title' => $this->record->default_title,
            'chat_button_text' => $this->record->chat_button_text,
            'chat_button_position' => $this->record->chat_button_position,
            'request_timeout' => $this->record->request_timeout,
            'auth_type' => $this->record->auth_type,
            'auth_header_key' => $this->record->auth_header_key,
            'auth_header_value' => $this->record->auth_header_value,
            'auth_header_as_bearer' => $this->record->auth_header_as_bearer,
            'default_avatar_path' => $this->record->default_avatar_path,
            'history_storage' => $this->record->history_storage,
            'history_ttl_minutes' => $this->record->history_ttl_minutes,
            'auto_inject_enabled' => $this->record->auto_inject_enabled,
            'auto_inject_sitewide' => $this->record->auto_inject_sitewide,
            'auto_inject_position' => $this->record->auto_inject_position,
            'auto_inject_pages' => $this->record->auto_inject_pages,
            'auto_inject_posts' => $this->record->auto_inject_posts,
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('save')
                ->label('Simpan')
                ->icon('heroicon-m-check')
                ->color('primary')
                ->keyBindings(['mod+s'])
                ->action('save'),
        ];
    }

    public function save(): void
    {
        abort_unless(auth()->user()?->hasAnyRole(['admin', 'editor']), 403);

        $state = $this->form->getState();

        $this->record->fill([
            'module_enabled' => (bool) ($state['module_enabled'] ?? false),
            'endpoint' => $state['endpoint'] ?? null,
            'default_title' => $state['default_title'] ?? null,
            'chat_button_text' => $state['chat_button_text'] ?? null,
            'chat_button_position' => $state['chat_button_position'] ?? 'bottom-right',
            'request_timeout' => (int) ($state['request_timeout'] ?? 180),
            'auth_type' => $state['auth_type'] ?? 'custom_header',
            'auth_header_key' => $state['auth_header_key'] ?? null,
            'auth_header_value' => $state['auth_header_value'] ?? null,
            'auth_header_as_bearer' => (bool) ($state['auth_header_as_bearer'] ?? false),
            'default_avatar_path' => $state['default_avatar_path'] ?? null,
            'history_storage' => $state['history_storage'] ?? 'ttl',
            'history_ttl_minutes' => $state['history_ttl_minutes'] ?? null,
            'auto_inject_enabled' => (bool) ($state['auto_inject_enabled'] ?? false),
            'auto_inject_sitewide' => (bool) ($state['auto_inject_sitewide'] ?? false),
            'auto_inject_position' => $state['auto_inject_position'] ?? 'below_content',
            'auto_inject_pages' => array_values($state['auto_inject_pages'] ?? []),
            'auto_inject_posts' => array_values($state['auto_inject_posts'] ?? []),
        ])->save();

        $this->record->refresh();
        $this->mount();

        Notification::make()
            ->success()
            ->title('Pengaturan chatbot berhasil disimpan.')
            ->send();
    }
}
