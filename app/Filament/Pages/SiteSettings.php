<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Str;

class SiteSettings extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected static ?string $navigationLabel = 'Pengaturan Website';
    protected static ?string $title = 'Pengaturan Website';
    protected static ?string $slug = 'settings';
    protected string $view = 'filament.pages.site-settings';

    /** tampil di menu “Pengaturan” */
    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return 'Pengaturan';
    }

    public static function getNavigationSort(): int
    {
        return '4';
    }

    /** hanya admin yang bisa lihat menu ini */
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->hasRole('admin');
    }

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-cog-6-tooth';
    }

    /** record singleton & state */
    public ?SiteSetting $record = null;
    public ?array $data = [];

    /** definisi form schema */
    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Identitas Situs')->schema([
                    TextInput::make('site_name')
                        ->label('Nama Website')
                        ->required()
                        ->maxLength(255),

                    Textarea::make('site_description')
                        ->label('Deskripsi Website')
                        ->rows(4)
                        ->maxLength(1000),
                ]),

                Section::make('Branding')->schema([
                    FileUpload::make('logo_path')
                        ->label('Logo')
                        ->image()
                        ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp', 'image/svg+xml'])
                        ->maxSize(4096)
                        ->disk('public')
                        ->directory('branding')
                        ->visibility('public')
                        ->getUploadedFileNameForStorageUsing(
                            fn($file) => (string) Str::uuid() . '.' . $file->getClientOriginalExtension()
                        ),

                    FileUpload::make('favicon_path')
                        ->label('Favicon')
                        ->image()
                        ->acceptedFileTypes([
                            'image/png',
                            'image/jpeg',
                            'image/webp',
                            'image/x-icon',
                            'image/svg+xml'
                        ])
                        ->maxSize(1024)
                        ->disk('public')
                        ->directory('branding')
                        ->visibility('public')
                        ->getUploadedFileNameForStorageUsing(
                            fn($file) => (string) Str::uuid() . '.' . $file->getClientOriginalExtension()
                        )
                        ->helperText('Gunakan ukuran kecil (32×32 atau 48×48).'),
                ]),
            ]);
    }

    /** prefill data dari record singleton */
    public function mount(): void
    {
        abort_unless(auth()->user()?->hasAnyRole(['admin', 'editor']), 403);

        $this->record = SiteSetting::first() ?? SiteSetting::create([]);
        $this->form->fill([
            'site_name' => $this->record->site_name,
            'site_description' => $this->record->site_description,
            'logo_path' => $this->record->logo_path,
            'favicon_path' => $this->record->favicon_path,
        ]);
    }

    /** tombol Simpan di header */
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

    /** handler Simpan */
    public function save(): void
    {
        abort_unless(auth()->user()?->hasAnyRole(['admin', 'editor']), 403);

        $d = $this->form->getState();

        $this->record->fill([
            'site_name' => $d['site_name'] ?? $this->record->site_name,
            'site_description' => $d['site_description'] ?? $this->record->site_description,
            'logo_path' => $d['logo_path'] ?? $this->record->logo_path,
            'favicon_path' => $d['favicon_path'] ?? $this->record->favicon_path,
        ])->save();

        // refresh state agar field tidak kosong setelah simpan
        $this->record->refresh();
        $this->form->fill([
            'site_name' => $this->record->site_name,
            'site_description' => $this->record->site_description,
            'logo_path' => $this->record->logo_path,
            'favicon_path' => $this->record->favicon_path,
        ]);

        Notification::make()
            ->success()
            ->title('Pengaturan berhasil disimpan.')
            ->send();
    }
}
