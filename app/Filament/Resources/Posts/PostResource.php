<?php

namespace App\Filament\Resources\Posts;

use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Filament\Resources\Posts\Schemas\PostForm;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Post;
use BackedEnum;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section; // container v4
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;
    public static function canViewAny(): bool
    {
        return auth()->check(); // atau cek role jika mau
    }
    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return 'Post';
    }
    public static function getNavigationSort(): int
    {
        return '1';
    }

    public static function getNavigationIcon(): string|BackedEnum|null
    {
        return 'heroicon-o-newspaper';
    }
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->user()?->hasRole('penulis')) {
            $query->where('user_id', auth()->id());
        }

        // editor & admin: tanpa pembatasan -> bisa lihat/edit semua
        return $query;
    }
    protected static ?string $navigationLabel = 'Posts';
    protected static ?string $slug = 'posts';
    protected static ?string $recordTitleAttribute = 'title';

    // tampil untuk admin/editor, sesuaikan role kamu
    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();
        return $user?->hasAnyRole(['admin', 'editor', 'penulis']) ?? false;
    }

    /** FORM */
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Konten')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn($state, callable $set) => $set('slug', \Str::slug($state))),

                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->disabled()
                            ->dehydrated(),

                        Forms\Components\Textarea::make('excerpt')
                            ->label('Ringkasan')
                            ->rows(3),

                        Forms\Components\RichEditor::make('content')
                            ->label('Isi')
                            ->columnSpanFull()
                            ->fileAttachmentsDisk('public')                     // sesuaikan dengan disk kamu
                            ->fileAttachmentsDirectory('editor/uploads'),


                    ])
                    ->columnSpan(2),


                Section::make('Detail')
                    ->schema([
                        Forms\Components\Select::make('category_id')
                            ->label('Kategori')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload(),

                        Forms\Components\FileUpload::make('thumbnail')
                            ->label('Thumbnail')
                            ->image()
                            ->disk('public')
                            ->directory('posts')
                            ->maxSize(2048)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->visibility('public'),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draft',
                                'scheduled' => 'Scheduled',
                                'published' => 'Published',
                            ])
                            ->default('draft')
                            ->required(),

                        Forms\Components\Select::make('tags')
                            ->label('Tags')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->relationship('tags', 'name')
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama Tag')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn($state, callable $set) => $set('slug', \Str::slug($state))),
                                Forms\Components\TextInput::make('slug')
                                    ->disabled()
                                    ->dehydrated(),
                            ]),

                        Forms\Components\DateTimePicker::make('published_at')
                            ->label('Tanggal Publish')
                            ->native(false)                // ⇐ gunakan picker JS, bukan HTML5 native
                            ->seconds(false)               // ⇐ hilangkan detik (opsional)
                            ->displayFormat('d/m/Y H:i')   // ⇐ format yang kamu lihat di UI
                            ->format('Y-m-d H:i:s')        // ⇐ format yang dikirim ke server
                            ->timezone(config('app.timezone')) // ⇐ konsisten dgn app
                            ->helperText('Kosongkan: otomatis diisi saat status Published.'),

                        Forms\Components\Toggle::make('is_featured')
                            ->label('Featured')
                            ->default(false),

                        Forms\Components\Toggle::make('is_pinned')
                            ->label('Pinned')
                            ->default(false),
                    ])
                    ->columnSpan(1),
            ]);
    }

    /** TABLE */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TagsColumn::make('tags.name')
                    ->label('Tags')
                    ->limit(3),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'secondary' => 'draft',
                        'warning' => 'scheduled',
                        'success' => 'published',
                    ])
                    ->label('Status'),

                Tables\Columns\TextColumn::make('published_at')
                    ->label('Publish')
                    ->since()
                    ->dateTime(),

                Tables\Columns\IconColumn::make('is_featured')->label('Feat')->boolean(),
                Tables\Columns\IconColumn::make('is_pinned')->label('Pin')->boolean(),
            ])
            ->defaultSort('created_at', 'desc')
            // klik baris untuk edit (tanpa Actions bawaan supaya aman)
            ->recordUrl(fn(Post $record) => static::getUrl('edit', ['record' => $record]))

            ->actions([
                Action::make('delete')
                    ->visible(fn() => auth()->user()?->hasAnyRole(['admin', 'editor']))
                    ->label('Hapus')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Post?')
                    ->modalDescription('Tindakan ini tidak dapat dibatalkan.')
                    ->action(fn(Post $record) => $record->delete()),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    // === Bulk Publish ===
                    BulkAction::make('publish')
                        ->visible(fn() => auth()->user()?->hasAnyRole(['admin', 'editor']))
                        ->label('Publish')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records) {
                            $now = Carbon::now();
                            $records->each(function ($post) use ($now) {
                                $post->update([
                                    'status' => 'published',
                                    'published_at' => $post->published_at ?: $now,
                                ]);
                            });
                        }),

                    // === Bulk Unpublish (Draft) ===
                    BulkAction::make('unpublish')
                        ->visible(fn() => auth()->user()?->hasAnyRole(['admin', 'editor']))
                        ->label('Unpublish (Draft)')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->color('secondary')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records) {
                            $records->each(function ($post) {
                                $post->update([
                                    'status' => 'draft',
                                    'published_at' => null,
                                ]);
                            });
                        }),

                    // (opsi sebelumnya) Bulk: Jadikan Draft — bisa dihapus jika sudah pakai unpublish
                    // BulkAction::make('set_draft') ...

                    // === Bulk Delete ===
                    DeleteBulkAction::make()
                        ->label('Hapus terpilih')
                        ->visible(fn() => auth()->user()?->hasAnyRole(['admin', 'editor']))
                        ->requiresConfirmation(),

                ]),

            ]);

    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPosts::route('/'),
            'create' => CreatePost::route('/create'),
            'edit' => EditPost::route('/{record}/edit'),
        ];
    }
}
