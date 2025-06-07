<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventResource\Pages;
use App\Models\Event;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Event Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Event Information')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('short_description')
                            ->label('Short Description')
                            ->rows(2)
                            ->columnSpanFull(),
                        Forms\Components\RichEditor::make('description')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('location')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('price')
                            ->numeric()
                            ->default(0.00)
                            ->prefix('$'),
                        Forms\Components\FileUpload::make('image_url')
                            ->label('Event Image')
                            ->image()
                            ->directory('events')
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Event Schedule')
                    ->schema([
                        Forms\Components\DateTimePicker::make('start_date')
                            ->required()
                            ->native(false),
                        Forms\Components\DateTimePicker::make('end_date')
                            ->required()
                            ->native(false)
                            ->after('start_date'),
                        Forms\Components\DateTimePicker::make('registration_start')
                            ->label('Registration Opens')
                            ->native(false),
                        Forms\Components\DateTimePicker::make('registration_end')
                            ->label('Registration Closes')
                            ->native(false)
                            ->after('registration_start'),
                    ])->columns(2),

                Forms\Components\Section::make('Registration Settings')
                    ->schema([
                        Forms\Components\TextInput::make('max_spots')
                            ->label('Maximum Participants')
                            ->numeric()
                            ->minValue(1),
                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'published' => 'Published',
                                'cancelled' => 'Cancelled',
                                'completed' => 'Completed',
                            ])
                            ->default('draft')
                            ->required(),
                        Forms\Components\Toggle::make('requires_approval')
                            ->label('Require Manual Approval')
                            ->default(true),
                        Forms\Components\Hidden::make('created_by')
                            ->default(fn () => auth()->id()),
                        Forms\Components\Hidden::make('current_registrations')
                            ->default(0),
                    ])->columns(2),

                Forms\Components\Section::make('Registration Form Fields')
                    ->schema([
                        Forms\Components\Repeater::make('form_fields')
                            ->label('Custom Form Fields')
                            ->schema([
                                Forms\Components\TextInput::make('label')
                                    ->required(),
                                Forms\Components\Select::make('type')
                                    ->options([
                                        'text' => 'Text Input',
                                        'textarea' => 'Textarea',
                                        'email' => 'Email',
                                        'number' => 'Number',
                                        'select' => 'Select Dropdown',
                                        'checkbox' => 'Checkbox',
                                        'file' => 'File Upload',
                                    ])
                                    ->required(),
                                Forms\Components\Toggle::make('required')
                                    ->default(false),
                                Forms\Components\Textarea::make('options')
                                    ->label('Options (for select/checkbox, one per line)')
                                    ->visible(fn ($get) => in_array($get('type'), ['select', 'checkbox']))
                                    ->rows(3),
                                Forms\Components\TextInput::make('placeholder')
                                    ->visible(fn ($get) => in_array($get('type'), ['text', 'textarea', 'email', 'number'])),
                            ])
                            ->columns(2)
                            ->defaultItems(0)
                            ->addActionLabel('Add Form Field')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_url')
                    ->label('Image')
                    ->circular()
                    ->size(40),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('location')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Start Date')
                    ->dateTime('M j, Y g:i A')
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('End Date')
                    ->dateTime('M j, Y g:i A')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('current_registrations')
                    ->label('Registrations')
                    ->formatStateUsing(fn ($record) => $record->current_registrations.($record->max_spots ? '/'.$record->max_spots : ''))
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'secondary' => 'draft',
                        'success' => 'published',
                        'danger' => 'cancelled',
                        'warning' => 'completed',
                    ]),
                Tables\Columns\IconColumn::make('requires_approval')
                    ->label('Approval Required')
                    ->boolean()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Created By')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                        'cancelled' => 'Cancelled',
                        'completed' => 'Completed',
                    ]),
                Tables\Filters\Filter::make('upcoming')
                    ->label('Upcoming Events')
                    ->query(fn (Builder $query) => $query->where('start_date', '>', now())),
                Tables\Filters\Filter::make('registration_open')
                    ->label('Registration Open')
                    ->query(fn (Builder $query) => $query->where('status', 'published')
                        ->where(function ($q) {
                            $q->whereNull('registration_end')
                                ->orWhere('registration_end', '>', now());
                        })),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('start_date', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'edit' => Pages\EditEvent::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // If user is not admin, only show their own events
        if (! auth()->user()->hasRole('admin')) {
            $query->where('created_by', auth()->id());
        }

        return $query;
    }
}
