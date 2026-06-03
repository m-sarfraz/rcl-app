<?php

namespace App\Repositories\Interfaces;

use App\Models\Edition;
use Illuminate\Database\Eloquent\Collection;

interface EditionRepositoryInterface
{
    public function all(): Collection;
    public function find(int $id): ?Edition;
    public function findCurrent(): ?Edition;
    public function create(array $data): Edition;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function setAsCurrent(int $id): void;
    public function getPointsTable(int $editionId): array;
    public function getLeaderboards(int $editionId): array;
}
