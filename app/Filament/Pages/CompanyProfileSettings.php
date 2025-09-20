<?php

namespace App\Filament\Pages;

use App\Models\CompanyProfile;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;

class CompanyProfileSettings extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected static ?string $navigationLabel = 'Company Profile';
    protected static ?string $title = 'Company Profile';
    protected static ?string $slug = 'company-profile';
    protected string $view = 'filament.pages.company-profile-settings';

    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return 'Pengaturan';
    }

    public static function getNavigationSort(): int
    {
        return '3';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->hasRole('admin');
    }

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-building-office';
    }

    public ?CompanyProfile $record = null;
    public ?array $data = [];

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Identitas Perusahaan')->schema([
                    TextInput::make('company_name')->label('Nama Perusahaan')->required()->maxLength(255),
                    Textarea::make('address')->label('Alamat')->rows(3),
                    TextInput::make('email')->label('Email')->email(),
                    TextInput::make('phone')->label('Nomor Telepon')->numeric(),
                ]),

                Section::make('Legalitas')->schema([
                    TextInput::make('npwp')->label('NPWP'),
                    TextInput::make('nib')->label('NIB'),
                    TextInput::make('bank_account')->label('Nomor Rekening'),
                ]),

                Section::make('Visi & Misi')->schema([
                    Textarea::make('vision')->label('Visi')->rows(3),
                    Textarea::make('mission')->label('Misi')->rows(5),
                ]),

                Section::make('Lokasi')->schema([
                    Textarea::make('google_maps')
                        ->label('Embed Google Maps')
                        ->rows(3)
                        ->helperText('Masukkan iframe embed dari Google Maps'),
                ]),

                Section::make('Sosial Media')->schema([
                    TextInput::make('twitter')->label('Twitter'),
                    TextInput::make('facebook')->label('Facebook'),
                    TextInput::make('instagram')->label('Instagram'),
                    TextInput::make('youtube')->label('YouTube'),
                    TextInput::make('tiktok')->label('TikTok'),
                    TextInput::make('wikipedia')->label('Wikipedia'),
                ]),
            ]);
    }

    public function mount(): void
    {

        abort_unless(auth()->user()?->hasAnyRole(['admin', 'editor']), 403);

        $this->record = CompanyProfile::current();
        $this->form->fill($this->record->toArray());
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
        abort_unless(auth()->user()?->hasRole('admin', 'editor'), 403);

        $this->record->fill($this->form->getState())->save();

        $this->form->fill($this->record->fresh()->toArray());

        Notification::make()
            ->success()
            ->title('Profil perusahaan berhasil disimpan.')
            ->send();
    }
}
