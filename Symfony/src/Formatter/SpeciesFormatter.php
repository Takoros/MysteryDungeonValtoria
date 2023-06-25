<?php

namespace App\Formatter;

class SpeciesFormatter
{
    public function listSpeciesFormat($speciesList){
        $formattedSpeciesList = [];

        foreach ($speciesList as $species) {
            $typeList = [];

            foreach ($species->getType() as $type){
                $typeList[] = [
                    "id" => $type->getId(),
                    "name" => $type->getName()
                ];
            }

            $formattedSpeciesList[] = [
                "id" => $species->getId(),
                "name" => $species->getName(),
                "isPlayable" => $species->isIsPlayable(),
                "types" => $typeList,
                "description" => $species->getDescription()
            ];
        }

        return $formattedSpeciesList;
    }
}
