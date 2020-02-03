<?php


namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class BaseController extends AbstractController
{
    protected function success($data)
    {
        return $this->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    protected function error($errors, $responseCode = Response::HTTP_BAD_REQUEST)
    {
        return $this->json([
            'status' => 'error',
            'errors' => $errors
        ], $responseCode);
    }
}