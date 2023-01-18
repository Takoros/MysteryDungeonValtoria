<?php

namespace App\Controller\API;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class AbstractAPIController extends AbstractController
{
    public function verifyTokenAndData($post, $awaitedData, $apiService){
        if(empty($post->token) || !$apiService->isCorrectToken($post->token)){
            return new JsonResponse(['message' => 'Unauthorized'], 401);
        }

        if(empty($post->data) || !$apiService->hasCorrectData($awaitedData, $post->data)){
            return new JsonResponse(['message' => 'Bad Request'], 400);
        }

        return true;
    }


}
