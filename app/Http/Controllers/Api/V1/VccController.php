<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\SponsorResource;
use App\Http\Resources\VccMemberResource;
use App\Models\Sponsor;
use App\Models\VccCabinet;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class VccController extends Controller
{
    public function index(): JsonResponse
    {
        return ApiResponse::success(
            VccMemberResource::collection(VccCabinet::active()->orderBy('display_order')->get())
        );
    }

    public function sponsors(): JsonResponse
    {
        $sponsors = Sponsor::active()->orderBy('display_order')->get();

        return ApiResponse::success([
            'by_tier' => $sponsors->groupBy('tier')->map(
                fn ($group, $tier) => [
                    'tier'     => $tier,
                    'sponsors' => SponsorResource::collection($group->values()),
                ]
            )->values(),
            'all' => SponsorResource::collection($sponsors),
        ]);
    }
}
