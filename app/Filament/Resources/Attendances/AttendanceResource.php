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
        return 'Attendance log';
    }

    public static function getModelLabel(): string
    {
        return 'Attendance';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Attendance';
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
                    ->label('Member')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('member.code')
                    ->label('Code')
                    ->searchable(),
                TextColumn::make('checked_in_at')
                    ->label('Check-in')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('checked_out_at')
                    ->label('Check-out')
                    ->dateTime('d M Y H:i')
                    ->placeholder('— still in —'),
                TextColumn::make('method')
                    ->badge(),
                TextColumn::make('recorder.name')
                    ->label('By')
                    ->placeholder('—')
                    ->toggleable(),
            ])
            ->filters([
                Filter::make('today')
                    ->label('Today only')
                    ->query(fn (Builder $query): Builder => $query->whereDate('checked_in_at', today())),
                Filter::make('in_gym')
                    ->label('Currently in')
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
