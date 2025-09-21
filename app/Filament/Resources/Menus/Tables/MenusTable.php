<?php

namespace App\Filament\Resources\Menus\Tables;

use App\Models\Menu;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MenusTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $query->with('parent')
                    ->orderBy('location')
                    ->orderBy('sort_order');
            })
            ->columns([
                TextColumn::make('label')
                    ->searchable()
                    ->formatStateUsing(fn (string $state, Menu $record) => str_repeat('— ', $record->depth) . $state),
                TextColumn::make('location')
                    ->searchable(),
                TextColumn::make('item_type')
                    ->searchable(),
                TextColumn::make('url')
                    ->searchable(),
                IconColumn::make('open_in_new_tab')
                    ->boolean(),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->reorderable('sort_order')
            ->groups([
                Group::make('location')
                    ->label('Lokasi'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
