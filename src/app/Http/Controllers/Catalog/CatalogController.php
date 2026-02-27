<?php

declare(strict_types=1);

namespace App\Http\Controllers\Catalog;

use App\Application\Catalog\GetSpecializationLanguages;
use App\Application\Catalog\GetSpecializations;
use App\Http\Controllers\Controller;
use App\Http\Resources\Catalog\ProgrammingLanguageResource;
use App\Http\Resources\Catalog\SpecializationResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

class CatalogController extends Controller
{
    public function specializations(GetSpecializations $getSpecializations): JsonResponse
    {
        $collection = $getSpecializations->run();

        return ApiResponse::success(data: SpecializationResource::collection($collection));
    }

    public function specializationLanguages(int $specialization, GetSpecializationLanguages $getSpecializationLanguages): JsonResponse
    {
        $collection = $getSpecializationLanguages->run($specialization);

        return ApiResponse::success(data: ProgrammingLanguageResource::collection($collection));
    }
}
