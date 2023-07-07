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
    public array $API_DATA_LIST_SPECIES_ARGS = [];

    #[Route('/api/data/list_species', name: 'api_data_list_species')]
    public function listSpecies(SpeciesRepository $speciesRepository, SpeciesFormatter $speciesFormatter): JsonResponse
    {
        if(!is_bool($this->isValid)){
            return $this->isValid;
        }

        $species = $speciesRepository->findAll();
        return new JsonResponse($speciesFormatter->listSpeciesFormat($species));
    }
}
