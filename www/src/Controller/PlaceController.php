<?php

namespace App\Controller;

use App\Entity\Place;
use App\Entity\PlaceType;
use App\Repository\PlaceRepository;
use App\Service\LocationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PlaceController extends AbstractController
{
    /**
     * @param Request $request
     * @param LocationService $locationService
     * @return JsonResponse
     * @Route("/nearbyPlace", name="get_nearby_place", methods={"GET"})
     */
    public function getNearbyPlaces(Request $request, LocationService $locationService)
    {
        $ip = $request->get('ip') ?? $request->getClientIp();
        $range = intval($request->get('range') ?? 1000);

        $response = $locationService->getCoordinatesByIp($ip);

        return $this->json([
            'ip' => $response,
            'range' => $range
        ]);
    }

    /**
     * @Route("/place", name="place", methods={"GET"})
     * @param PlaceRepository $repository
     * @return JsonResponse
     */
    public function getPlaces(PlaceRepository $repository)
    {
        $products = $repository->findAll();
        return $this->json(array_map(function ($item) { return $item->toArray(); }, $products));
    }

    /**
     * @param int $id
     * @param PlaceRepository $repository
     * @Route("/place/{id}", name="get_place", methods={"GET"})
     * @return JsonResponse
     */
    public function getPlace(int $id, PlaceRepository $repository)
    {
        $place = $repository->find($id);

        if (!$place) {
            throw $this->createNotFoundException(
                'No place found for id '.$id
            );
        }

        return $this->json($place->toArray());
    }

    /**
     * @Route("/place", name="add_place", methods={"POST"})
     * @param Request $request
     */
    public function addPlace(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $lat = $request->get('lat');
        $lng = $request->get('lng');
        $name = $request->get('name');
        $description = $request->get('description');

        $type = new PlaceType();
        $type->setName('test');
        $entityManager->persist($type);
        $place = new Place();
        $place->setType($type);
        $fields = ['lat', 'lng', 'name', 'description'];

        foreach ($fields as $field) {
            $param = $request->get($field);
            if ($param) {
                $place->{"set$field"}($param);
            }
        }

        $entityManager->persist($place);
        $entityManager->flush();
        return $this->json($place->toArray());
    }

    /**
     * @param int $id
     * @param PlaceRepository $repository
     * @return JsonResponse
     * @Route("/place/{id}", name="update_place", methods={"PUT"})
     */
    public function updatePlace(int $id, PlaceRepository $repository)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $place = $repository->find($id);

        if (!$place) {
            throw $this->createNotFoundException(
                'No place found for id '.$id
            );
        }

        $place->setName('New name!');
        $entityManager->flush();

        return $this->json($place->toArray());
    }

    /**
     * @param int $id
     * @param PlaceRepository $repository
     * @Route("/place{id}", name="delete_place", methods={"DELETE"})
     */
    public function deletePlace(int $id, PlaceRepository $repository)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $place = $repository->find($id);

        if (!$place) {
            throw $this->createNotFoundException(
                'No place found for id '.$id
            );
        }

        $entityManager->remove($place);
        $entityManager->flush();
    }
}
