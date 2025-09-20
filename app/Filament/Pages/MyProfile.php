<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;      // layout (Schemas)
use Filament\Forms\Components\TextInput;      // fields (Forms)
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Hash;
use Filament\Actions;

class MyProfile extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected static ?string $navigationLabel = 'Profil Saya';
    protected static ?string $title = 'Profil Saya';
    protected static ?string $slug = 'profil-saya';
    protected static bool $shouldRegisterNavigation = true;

    // v4: non-static, wajib string
    protected string $view = 'filament.pages.my-profile';

    // Hindari type clash: gunakan getter
    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-user-circle';
    }
    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return 'Akun';
    }

    /** State schema */
    public ?array $data = [];

    /** ✅ Schemas API */
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('avatar_path')
                    ->label('Avatar')
                    ->image()                                // preview gambar
                    // ->imageEditor()                       // MATIKAN dulu sampai upload lancar
                    ->acceptedFileTypes(['image/jpeg', 'image/png'])
                    ->maxSize(2048)                          // 2 MB
                    ->disk('public')                         // simpan ke disk public
                    ->directory('avatars')                   // folder target
                    ->visibility('public')                   // pastikan publik
                    ->getUploadedFileNameForStorageUsing(    // beri nama aman (hindari masalah spasi/unik)
                        fn($file) => (string) \Illuminate\Support\Str::uuid() . '.' . $file->getClientOriginalExtension()
                    ),

                Section::make('Informasi Dasar')->schema([
                    TextInput::make('name')->label('Nama')->required()->maxLength(255),
                    TextInput::make('email')->label('Email')->email()->required()->maxLength(255),
                    TextInput::make('phone')->label('Telepon')->tel()->maxLength(30),
                    Textarea::make('bio')->label('Bio')->rows(4)->maxLength(1000),
                ]),

                Section::make('Ganti Password')
                    ->description('Opsional — isi jika ingin mengganti password.')
                    ->schema([
                        TextInput::make('current_password')->label('Password Saat Ini')
                            ->password()->revealable()
                            ->dehydrateStateUsing(fn($v) => $v ?: null),
                        TextInput::make('password')->label('Password Baru')
                            ->password()->revealable()->minLength(8)
                            ->dehydrateStateUsing(fn($v) => $v ?: null),
                        TextInput::make('password_confirmation')->label('Konfirmasi Password Baru')
                            ->password()->revealable()->same('password')
                            ->dehydrateStateUsing(fn($v) => $v ?: null),
                    ]),
            ])
            ->statePath('data'); // simpan state ke $this->data
    }

    public function mount(): void
    {
        $u = auth()->user();

        $this->form->fill([
            'avatar_path' => $u->avatar_path,
            'name' => $u->name,
            'email' => $u->email,
            'phone' => $u->phone,
            'bio' => $u->bio,
        ]);
    }

    public function save(): void
    {
        $u = auth()->user();
        $d = $this->form->getState();

        $u->fill([
            'avatar_path' => $d['avatar_path'] ?? $u->avatar_path,
            'name' => $d['name'] ?? $u->name,
            'email' => $d['email'] ?? $u->email,
            'phone' => $d['phone'] ?? $u->phone,
            'bio' => $d['bio'] ?? $u->bio,
        ]);

        if (!empty($d['password'])) {
            if (empty($d['current_password']) || !Hash::check($d['current_password'], $u->password)) {
                Notification::make()->danger()->title('Password saat ini tidak sesuai')->send();
                return;
            }
            $u->password = Hash::make($d['password']);
        }

        $u->save();

        Notification::make()->success()->title('Profil berhasil diperbarui')->send();
    }
    protected function getActions(): array
    {
        return [
            Actions\Action::make('save')
                ->label('Simpan')
                ->icon('heroicon-m-check')   // opsional
                ->color('primary')           // opsional
                ->keyBindings(['mod+s'])     // opsional: Cmd/Ctrl+S
                ->action('save'),            // ← panggil method save()
        ];
    }
}
