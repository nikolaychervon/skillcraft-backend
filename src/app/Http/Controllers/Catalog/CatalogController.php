<?php

declare(strict_types=1);

namespace App\Http\Controllers\Catalog;

use App\Domain\Catalog\Actions\GetSpecializationLanguagesAction;
use App\Domain\Catalog\Actions\GetSpecializationsAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Catalog\ProgrammingLanguageResource;
use App\Http\Resources\Catalog\SpecializationResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

class CatalogController extends Controller
{
    public function specializations(GetSpecializationsAction $getSpecializationsAction): JsonResponse
    {
        $collection = $getSpecializationsAction->run();
        return ApiResponse::success(data: SpecializationResource::collection($collection));
    }

    public function specializationLanguages(int $id, GetSpecializationLanguagesAction $getSpecializationLanguagesAction): JsonResponse
    {
        $collection = $getSpecializationLanguagesAction->run($id);
        return ApiResponse::success(data: ProgrammingLanguageResource::collection($collection));
    }
}
