<?php

namespace App\Filament\Pages;

use App\Models\Category;
use App\Models\HomepageSetting;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\Arr;

class HomepageSettings extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected static ?string $navigationLabel = 'Homepage Settings';
    protected static ?string $title = 'Homepage Settings';
    protected static ?string $slug = 'homepage-settings';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-home-modern';
    protected string $view = 'filament.pages.homepage-settings';

    public ?HomepageSetting $record = null;
    public ?array $data = [];

    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return 'Pengaturan';
    }

    public static function getNavigationSort(): int
    {
        return 2;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->hasRole('admin');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Hero Slider')
                    ->description('Atur gambar hero slider halaman depan.')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Repeater::make('hero_slides')
                            ->label('Daftar Slide')
                            ->minItems(1)
                            ->maxItems(6)
                            ->reorderable(true)
                            ->collapsible()
                            ->collapsed()
                            ->schema([
                                FileUpload::make('image')
                                    ->label('Gambar')
                                    ->image()
                                    ->disk('public')
                                    ->directory('homepage/hero')
                                    ->visibility('public')
                                    ->required()
                                    ->maxSize(4096),
                                TextInput::make('title')
                                    ->label('Judul')
                                    ->maxLength(150),
                                Textarea::make('subtitle')
                                    ->label('Deskripsi Singkat')
                                    ->rows(2)
                                    ->maxLength(300)
                                    ->columnSpanFull(),
                                TextInput::make('link_label')
                                    ->label('Teks Tombol')
                                    ->maxLength(60),
                                TextInput::make('link_url')
                                    ->label('URL Tombol')
                                    ->maxLength(255),
                            ])
                            ->columns(2)
                            ->itemLabel(fn(array $state): ?string => $state['title'] ?? null),
                    ]),

                Section::make('Custom Button')
                    ->description('Tombol cepat di bawah hero (contoh: Profil, Sejarah, Perjuangan).')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Repeater::make('custom_buttons')
                            ->label('Daftar Tombol')
                            ->maxItems(8)
                            ->collapsible()
                            ->collapsed()
                            ->schema([
                                TextInput::make('label')
                                    ->label('Nama Tombol')
                                    ->required()
                                    ->maxLength(50),
                                TextInput::make('url')
                                    ->label('URL')
                                    ->required()
                                    ->maxLength(255),
                            ])
                            ->columns(2),
                    ]),

                Section::make('Susunan Pengurus')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Repeater::make('management_team')
                            ->label('Pengurus')
                            ->maxItems(12)
                            ->collapsible()
                            ->collapsed()
                            ->schema([
                                FileUpload::make('photo')
                                    ->label('Foto')
                                    ->image()
                                    ->disk('public')
                                    ->directory('homepage/team')
                                    ->visibility('public')
                                    ->maxSize(4096),
                                TextInput::make('name')
                                    ->label('Nama')
                                    ->required()
                                    ->maxLength(100),
                                TextInput::make('position')
                                    ->label('Jabatan')
                                    ->required()
                                    ->maxLength(100),
                            ])
                            ->columns(3),
                    ]),

                Section::make('Kategori Berita')
                    ->description('Pilih hingga 3 kategori untuk ditampilkan di beranda.')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Repeater::make('category_blocks')
                            ->label('Kategori')
                            ->minItems(1)
                            ->maxItems(3)
                            ->collapsible()
                            ->collapsed()
                            ->schema([
                                Select::make('category_id')
                                    ->label('Kategori')
                                    ->required()
                                    ->searchable()
                                    ->options(fn() => Category::where('is_active', true)->orderBy('name')->pluck('name', 'id')),
                                TextInput::make('title')
                                    ->label('Judul Section')
                                    ->helperText('Kosongkan untuk menggunakan nama kategori.')
                                    ->maxLength(100),
                            ])
                            ->columns(2),
                    ]),

                Section::make('Konten Tabs')
                    ->description('Bagian tab informatif di halaman utama.')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Repeater::make('tab_sections')
                            ->label('Daftar Tab')
                            ->maxItems(6)
                            ->collapsible()
                            ->collapsed()
                            ->schema([
                                TextInput::make('title')
                                    ->label('Judul Tab')
                                    ->required()
                                    ->maxLength(100),

                                RichEditor::make('content')
                                    ->label('Konten')
                                    ->columnSpanFull()
                                    ->fileAttachmentsDisk('public')
                                    ->fileAttachmentsDirectory('homepage/tabs')
                                    ->fileAttachmentsVisibility('public')

                                    ->required(),
                            ])
                            ->itemLabel(fn(array $state): ?string => $state['title'] ?? null)
                            ->columns(1),
                    ]),
            ]);
    }

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasAnyRole(['admin', 'editor']), 403);

        $this->record = HomepageSetting::current();

        $this->form->fill(Arr::only($this->record->toArray(), [
            'hero_slides',
            'custom_buttons',
            'management_team',
            'category_blocks',
            'tab_sections',
        ]));
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

        $payload = Arr::only($this->form->getState(), [
            'hero_slides',
            'custom_buttons',
            'management_team',
            'category_blocks',
            'tab_sections',
        ]);

        $this->record->fill($payload)->save();

        $this->form->fill(Arr::only($this->record->fresh()->toArray(), array_keys($payload)));

        Notification::make()
            ->success()
            ->title('Homepage berhasil diperbarui.')
            ->send();
    }
}
