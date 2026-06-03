<?php

namespace App\Repositories\Interfaces;

use App\Models\Player;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface PlayerRepositoryInterface
{
    public function all(array $filters = []): Collection;
    public function paginate(int $perPage = 20, array $filters = []): LengthAwarePaginator;
    public function find(int $id): ?Player;
    public function create(array $data): Player;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function getEligible(int $editionId, int $teamId): Collection;
    public function assignToTeam(int $playerId, int $teamId, int $editionId, array $extra = []): void;
    public function getByTeamAndEdition(int $teamId, int $editionId): Collection;
    public function updateEditionStats(int $playerId, int $editionId): void;
}
