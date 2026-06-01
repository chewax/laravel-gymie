<?php

namespace App\Filament\Resources\Attendances;

use App\Filament\Resources\Attendances\Pages\ListAttendances;
use App\Models\Attendance;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static ?string $recordTitleAttribute = 'id';

    public static function getNavigationLabel(): string
    {
        return __('app.access.attendance.nav');
    }

    public static function getModelLabel(): string
    {
        return __('app.access.attendance.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('app.access.attendance.singular');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('checked_in_at', 'desc')
            ->columns([
                TextColumn::make('member.name')
                    ->label(__('app.access.attendance.member'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('member.code')
                    ->label(__('app.access.attendance.code'))
                    ->searchable(),
                TextColumn::make('checked_in_at')
                    ->label(__('app.access.attendance.check_in'))
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('checked_out_at')
                    ->label(__('app.access.attendance.check_out'))
                    ->dateTime('d M Y H:i')
                    ->placeholder(__('app.access.attendance.still_in')),
                TextColumn::make('method')
                    ->label(__('app.access.attendance.method'))
                    ->badge(),
                TextColumn::make('recorder.name')
                    ->label(__('app.access.attendance.by'))
                    ->placeholder('—')
                    ->toggleable(),
            ])
            ->filters([
                Filter::make('today')
                    ->label(__('app.access.attendance.today_only'))
                    ->query(fn (Builder $query): Builder => $query->whereDate('checked_in_at', today())),
                Filter::make('in_gym')
                    ->label(__('app.access.attendance.currently_in'))
                    ->query(fn (Builder $query): Builder => $query->whereNull('checked_out_at')),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAttendances::route('/'),
        ];
    }
}
