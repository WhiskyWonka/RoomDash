<?php

declare(strict_types=1);

namespace Infrastructure\Tenant\Adapters;

use DateTimeImmutable;
use Domain\Tenant\Entities\Tenant as TenantEntity;
use Domain\Tenant\Ports\TenantRepositoryInterface;
use Infrastructure\Tenant\Models\Tenant as TenantModel;

class EloquentTenantRepository implements TenantRepositoryInterface
{
    public function findById(string $id): ?TenantEntity
    {
        $model = TenantModel::find($id);

        return $model ? $this->toEntity($model) : null;
    }

    public function findByDomain(string $domain): ?TenantEntity
    {
        $model = TenantModel::whereHas('domains', fn ($q) => $q->where('domain', $domain))->first();

        return $model ? $this->toEntity($model) : null;
    }

    public function findAll(): array
    {
        return TenantModel::with('domains')->get()->map(fn ($m) => $this->toEntity($m))->all();
    }

    public function update(string $id, string $name, string $domain): TenantEntity
    {
        $model = TenantModel::findOrFail($id);
        $model->update(['name' => $name]);
        $model->domains()->delete();
        $model->createDomain(['domain' => $domain]);
        $model->load('domains');

        return $this->toEntity($model);
    }

    public function create(string $name, string $domain): TenantEntity
    {
        $model = TenantModel::create(['name' => $name]);
        $model->createDomain(['domain' => $domain]);

        return $this->toEntity($model);
    }

    public function deacttivate(string $id): void
    {
        $model = TenantModel::findOrFail($id);
        $model->update(['is_active' => false]);
    }

    public function activate(string $id): void
    {
        $model = TenantModel::findOrFail($id);
        $model->update(['is_active' => true]);
    }

    public function delete(string $id): void
    {
        $model = TenantModel::findOrFail($id);
        $model->delete();
    }

    private function toEntity(TenantModel $model): TenantEntity
    {
        return new TenantEntity(
            id: $model->id,
            name: $model->name ?? '',
            domain: $model->domains->first()?->domain ?? '',
            createdAt: new DateTimeImmutable($model->created_at->toDateTimeString()),
        );
    }
}
