<?php

namespace App\Controller;

use App\Entity\Place;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class PlaceController extends AbstractController
{
    /**
     * @Route("/place", name="place", methods={"GET"})
     */
    public function getPlaces()
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/PlaceController.php',
        ]);
    }

    /**
     * @param int $id
     * @Route("/place/{id}", name="get_place", methods={"GET"})
     */
    public function getPlace(int $id)
    {

    }

    /**
     * @param Place $place
     * @Route("/place", name="add_place", methods={"POST"})
     */
    public function addPlace(Place $place)
    {

    }

    /**
     * @param int $id
     * @Route("/place/{id}", name="update_place", methods={"PUT"})
     */
    public function alterPlace(int $id)
    {

    }

    /**
     * @param int $id
     * @Route("/place{id}", name="delete_place", methods={"DELETE"})
     */
    public function deletePlace(int $id)
    {

    }
}
