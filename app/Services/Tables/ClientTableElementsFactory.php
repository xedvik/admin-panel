<?php

namespace App\Services\Tables;

use Filament\Tables;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use App\Contracts\Repositories\ClientRepositoryInterface;
use App\Models\Client;

class ClientTableElementsFactory
{
    protected ClientRepositoryInterface $clientRepository;

    public function __construct(ClientRepositoryInterface $clientRepository)
    {
        $this->clientRepository = $clientRepository;
    }

    /**
     * Создать колонки таблицы клиентов
     */
    public function createTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('full_name')
                ->label('Имя Фамилия')
                ->searchable(['first_name', 'last_name'])
                ->sortable()
                ->getStateUsing(function (Client $record): string {
                    return $this->clientRepository->getClientFullName($record->id);
                })
                ->description(fn (Client $record): string => $record->email),

            Tables\Columns\TextColumn::make('phone')
                ->label('Телефон')
                ->searchable()
                ->copyable(),

            Tables\Columns\TextColumn::make('addresses_count')
                ->label('Адресов')
                ->badge()
                ->color('info')
                ->getStateUsing(function (Client $record): int {
                    return $record->clientAddresses()->count();
                })
                ->description(function (Client $record): string {
                    $shippingCount = $record->clientAddresses()->where('type', 'shipping')->count();
                    $billingCount = $record->clientAddresses()->where('type', 'billing')->count();
                    return "Доставка: {$shippingCount}, Оплата: {$billingCount}";
                }),

            Tables\Columns\TextColumn::make('customer_status')
                ->label('Статус')
                ->badge()
                ->getStateUsing(function (Client $record): string {
                    return $this->clientRepository->getClientStatus($record->id);
                })
                ->color(fn (string $state): string => match ($state) {
                    'VIP' => 'success',
                    'Постоянный' => 'warning',
                    'Обычный' => 'gray',
                    'Новый' => 'info',
                    default => 'gray',
                }),

            Tables\Columns\TextColumn::make('total_orders')
                ->label('Заказов')
                ->badge()
                ->color('info')
                ->getStateUsing(function (Client $record): int {
                    return $this->clientRepository->getClientOrdersCount($record->id);
                }),

            Tables\Columns\TextColumn::make('total_spent')
                ->label('Потрачено')
                ->money('RUB')
                ->sortable()
                ->getStateUsing(function (Client $record): int {
                    return $this->clientRepository->getClientTotalSpent($record->id);
                }),

            Tables\Columns\IconColumn::make('email_verified_at')
                ->label('Email подтвержден')
                ->boolean()
                ->getStateUsing(fn (Client $record) => !is_null($record->email_verified_at))
                ->trueIcon('heroicon-o-check-badge')
                ->falseIcon('heroicon-o-x-mark'),

            Tables\Columns\IconColumn::make('accepts_marketing')
                ->label('Маркетинг')
                ->boolean()
                ->trueIcon('heroicon-o-megaphone')
                ->falseIcon('heroicon-o-no-symbol'),

            Tables\Columns\IconColumn::make('is_active')
                ->label('Активен')
                ->boolean()
                ->trueIcon('heroicon-o-check-badge')
                ->falseIcon('heroicon-o-x-mark'),

            Tables\Columns\TextColumn::make('created_at')
                ->label('Регистрация')
                ->dateTime('d.m.Y H:i')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    /**
     * Создать фильтры таблицы клиентов
     */
    public function createTableFilters(): array
    {
        return [
            $this->createActiveFilter(),
            $this->createMarketingFilter(),
            $this->createEmailVerifiedFilter(),
            $this->createCustomerStatusFilter(),
            $this->createHighValueFilter(),
            $this->createFrequentBuyersFilter(),
        ];
    }

    /**
     * Создать фильтр активности клиентов
     */
    public function createActiveFilter(): Tables\Filters\TernaryFilter
    {
        return Tables\Filters\TernaryFilter::make('is_active')
            ->label('Активные')
            ->boolean()
            ->trueLabel('Только активные')
            ->falseLabel('Только неактивные')
            ->query(function (Builder $query, array $data): Builder {
                if ($data['value'] === true) {
                    $clientIds = $this->clientRepository->getActive()->pluck('id');
                    return $query->whereIn('id', $clientIds);
                } elseif ($data['value'] === false) {
                    $activeIds = $this->clientRepository->getActive()->pluck('id');
                    return $query->whereNotIn('id', $activeIds);
                }
                return $query;
            })
            ->native(false);
    }

    /**
     * Создать фильтр согласия на маркетинг
     */
    public function createMarketingFilter(): Tables\Filters\TernaryFilter
    {
        return Tables\Filters\TernaryFilter::make('accepts_marketing')
            ->label('Согласие на маркетинг')
            ->boolean()
            ->trueLabel('Согласны')
            ->falseLabel('Не согласны')
            ->query(function (Builder $query, array $data): Builder {
                if ($data['value'] === true) {
                    $clientIds = $this->clientRepository->getAcceptsMarketing()->pluck('id');
                    return $query->whereIn('id', $clientIds);
                } elseif ($data['value'] === false) {
                    $marketingIds = $this->clientRepository->getAcceptsMarketing()->pluck('id');
                    return $query->whereNotIn('id', $marketingIds);
                }
                return $query;
            })
            ->native(false);
    }

    /**
     * Создать фильтр подтверждения email
     */
    public function createEmailVerifiedFilter(): Tables\Filters\TernaryFilter
    {
        return Tables\Filters\TernaryFilter::make('email_verified_at')
            ->label('Email подтвержден')
            ->boolean()
            ->trueLabel('Подтвержден')
            ->falseLabel('Не подтвержден')
            ->query(function (Builder $query, array $data): Builder {
                if ($data['value'] === true) {
                    $verifiedIds = $this->clientRepository->getVerified()->pluck('id');
                    return $query->whereIn('id', $verifiedIds);
                } elseif ($data['value'] === false) {
                    $verifiedIds = $this->clientRepository->getVerified()->pluck('id');
                    return $query->whereNotIn('id', $verifiedIds);
                }
                return $query;
            })
            ->native(false);
    }

    /**
     * Создать фильтр статуса клиента
     */
    public function createCustomerStatusFilter(): Tables\Filters\SelectFilter
    {
        return Tables\Filters\SelectFilter::make('customer_status')
            ->label('Статус клиента')
            ->options([
                'Новый' => 'Новый',
                'Обычный' => 'Обычный',
                'Постоянный' => 'Постоянный',
                'VIP' => 'VIP',
            ])
            ->query(function (Builder $query, array $data): Builder {
                if (filled($data['value'])) {
                    $clientIds = [];

                    switch ($data['value']) {
                        case 'Новый':
                            $clientIds = $this->clientRepository->getNewClients()->pluck('id');
                            break;
                        case 'Постоянный':
                            $clientIds = $this->clientRepository->getRegularClients()->pluck('id');
                            break;
                        case 'VIP':
                            $clientIds = $this->clientRepository->getVipClients()->pluck('id');
                            break;
                        default:
                            return $query;
                    }

                    return $query->whereIn('id', $clientIds);
                }
                return $query;
            })
            ->native(false);
    }

    /**
     * Создать фильтр клиентов с высокими тратами
     */
    public function createHighValueFilter(): Tables\Filters\Filter
    {
        return Tables\Filters\Filter::make('high_value')
            ->label('Клиенты с высокими тратами')
            ->query(function (Builder $query): Builder {
                $highValueIds = $this->clientRepository->getClientsByTotalSpent(10000)->pluck('id');
                return $query->whereIn('id', $highValueIds);
            });
    }

    /**
     * Создать фильтр частых покупателей
     */
    public function createFrequentBuyersFilter(): Tables\Filters\Filter
    {
        return Tables\Filters\Filter::make('frequent_buyers')
            ->label('Частые покупатели')
            ->query(function (Builder $query): Builder {
                $frequentBuyerIds = $this->clientRepository->getClientsByOrderCount(5)->pluck('id');
                return $query->whereIn('id', $frequentBuyerIds);
            });
    }

    /**
     * Создать стандартные действия для клиентов
     */
    public function createStandardActions(): array
    {
        return [
            Tables\Actions\ViewAction::make()
                ->label('Просмотр'),

            Tables\Actions\EditAction::make()
                ->label('Редактировать'),

            $this->createVerifyEmailAction(),
        ];
    }

    /**
     * Создать действие подтверждения email
     */
    public function createVerifyEmailAction(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('verifyEmail')
            ->label('Подтвердить Email')
            ->icon('heroicon-o-check-badge')
            ->color('success')
            ->requiresConfirmation()
            ->visible(fn (Client $record): bool => is_null($record->email_verified_at))
            ->action(function (Client $record): void {
                $this->clientRepository->markEmailAsVerified($record->id);

                Notification::make()
                    ->title('Email клиента подтвержден')
                    ->success()
                    ->send();
            });
    }

    /**
     * Создать массовые действия для клиентов
     */
    public function createBulkActions(): Tables\Actions\BulkActionGroup
    {
        return Tables\Actions\BulkActionGroup::make([
            Tables\Actions\DeleteBulkAction::make()
                ->label('Удалить'),

            $this->createActivateBulkAction(),
            $this->createDeactivateBulkAction(),
            $this->createVerifyEmailsBulkAction(),
        ]);
    }

    /**
     * Создать массовое действие активации клиентов
     */
    public function createActivateBulkAction(): Tables\Actions\BulkAction
    {
        return Tables\Actions\BulkAction::make('activateClients')
            ->label('Активировать клиентов')
            ->icon('heroicon-o-check')
            ->color('success')
            ->requiresConfirmation()
            ->action(function ($records): void {
                $count = 0;

                foreach ($records as $record) {
                    $this->clientRepository->update($record->id, ['is_active' => true]);
                    $count++;
                }

                Notification::make()
                    ->title("Активировано клиентов: {$count}")
                    ->success()
                    ->send();
            });
    }

    /**
     * Создать массовое действие деактивации клиентов
     */
    public function createDeactivateBulkAction(): Tables\Actions\BulkAction
    {
        return Tables\Actions\BulkAction::make('deactivateClients')
            ->label('Деактивировать клиентов')
            ->icon('heroicon-o-x-mark')
            ->color('danger')
            ->requiresConfirmation()
            ->action(function ($records): void {
                $count = 0;

                foreach ($records as $record) {
                    $this->clientRepository->update($record->id, ['is_active' => false]);
                    $count++;
                }

                Notification::make()
                    ->title("Деактивировано клиентов: {$count}")
                    ->success()
                    ->send();
            });
    }

    /**
     * Создать массовое действие подтверждения email
     */
    public function createVerifyEmailsBulkAction(): Tables\Actions\BulkAction
    {
        return Tables\Actions\BulkAction::make('verifyEmails')
            ->label('Подтвердить Email адреса')
            ->icon('heroicon-o-check-badge')
            ->color('info')
            ->requiresConfirmation()
            ->action(function ($records): void {
                $count = 0;

                foreach ($records as $record) {
                    if (is_null($record->email_verified_at)) {
                        $this->clientRepository->markEmailAsVerified($record->id);
                        $count++;
                    }
                }

                Notification::make()
                    ->title("Подтверждено Email адресов: {$count}")
                    ->success()
                    ->send();
            });
    }
}
