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
use Filament\Actions;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

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
                            ->hint('Masukkan password sekarang untuk verifikasi saat mengganti password.')
                            ->dehydrateStateUsing(fn($v) => $v ?: null),
                        TextInput::make('password')->label('Password Baru')
                            ->password()->revealable()->minLength(8)
                            ->dehydrateStateUsing(fn($v) => $v ?: null),
                        TextInput::make('password_confirmation')->label('Konfirmasi Password Baru')
                            ->password()->revealable()
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

        $guard = 'web';

        try {
            $validated = Validator::make($d, [
                'avatar_path' => ['nullable', 'string'],
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($u->id)],
                'phone' => ['nullable', 'string', 'max:30'],
                'bio' => ['nullable', 'string', 'max:1000'],
                'current_password' => ['nullable', 'required_with:password', 'current_password:' . $guard],
                'password' => ['nullable', 'string', Password::min(8), 'confirmed', 'different:current_password'],
                'password_confirmation' => ['nullable', 'string'],
            ])->validate();

            $u->fill(Arr::only($validated, ['avatar_path', 'name', 'email', 'phone', 'bio']));

            if (! empty($validated['password'])) {
                if (empty($validated['current_password']) || ! Hash::check($validated['current_password'], $u->getAuthPassword())) {
                    throw ValidationException::withMessages([
                        'current_password' => 'Password saat ini tidak sesuai.',
                    ]);
                }

                if (Hash::check($validated['password'], $u->getAuthPassword())) {
                    throw ValidationException::withMessages([
                        'password' => 'Password baru harus berbeda dari password sekarang.',
                    ]);
                }

                $u->password = $validated['password'];
            }

            $u->save();
        } catch (ValidationException $exception) {
            $this->setErrorBag($exception->validator->errors());

            Notification::make()
                ->danger()
                ->title('Gagal memperbarui profil')
                ->body($exception->validator->errors()->first() ?? 'Periksa kembali input Anda.')
                ->send();

            return;
        }

        $this->form->fill([
            'avatar_path' => $u->avatar_path,
            'name' => $u->name,
            'email' => $u->email,
            'phone' => $u->phone,
            'bio' => $u->bio,
            'current_password' => null,
            'password' => null,
            'password_confirmation' => null,
        ]);

        $this->resetErrorBag();

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
