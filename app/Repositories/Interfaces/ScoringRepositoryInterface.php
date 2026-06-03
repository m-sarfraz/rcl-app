<?php

namespace App\Repositories\Interfaces;

use App\Models\BallByBallLog;
use App\Models\Innings;

interface ScoringRepositoryInterface
{
    public function getInnings(int $matchId): \Illuminate\Database\Eloquent\Collection;
    public function createInnings(array $data): Innings;
    public function updateInnings(int $inningsId, array $data): bool;
    public function recordBall(array $data): BallByBallLog;
    public function getLastBall(int $inningsId): ?BallByBallLog;
    public function getBallsByOver(int $inningsId, int $over): \Illuminate\Database\Eloquent\Collection;
    public function getLiveMatchState(int $matchId): array;
    public function undoLastBall(int $inningsId): bool;
}
