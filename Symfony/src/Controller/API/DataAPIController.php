<?php

namespace App\Controller\API;

use App\Formatter\SpeciesFormatter;
use App\Repository\SpeciesRepository;
use App\Service\APIService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DataAPIController extends AbstractAPIController
{
    private $apiService;

    public function __construct(APIService $apiService)
    {
        $this->apiService = $apiService;
    }


    #[Route('/api/data/list_species', name: 'api_data_list_species')]
    public function listSpecies(Request $request, SpeciesRepository $speciesRepository, SpeciesFormatter $speciesFormatter): JsonResponse
    {
        $post = json_decode($request->getContent());
        $isValid = $this->verifyTokenAndData($post, [], $this->apiService);

        $species = $speciesRepository->findAll();
        return new JsonResponse($speciesFormatter->listSpeciesFormat($species));
    }
}
