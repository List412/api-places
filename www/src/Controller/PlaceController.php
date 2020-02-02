<?php

namespace App\Controller;

use App\Entity\Place;
use App\Repository\CityRepository;
use App\Repository\PlaceRepository;
use App\Repository\PlaceTypeRepository;
use App\Service\LocationService;
use App\Service\YandexGeoCoderService;
use Doctrine\Common\Collections\Criteria;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PlaceController extends AbstractController
{
    /**
     * @param Request $request
     * @param LocationService $locationService
     * @param PlaceRepository $placeRepository
     * @return JsonResponse
     * @throws \Exception
     * @Route("/nearbyPlace", name="get_nearby_place", methods={"GET"})
     */
    public function getNearbyPlaces(Request $request,
                                    LocationService $locationService,
                                    PlaceRepository $placeRepository)
    {
        $ip = $request->get('ip') ?? $request->getClientIp();
        $range = intval($request->get('range') ?? 1000);

        $response = $locationService->getCoordinatesByIp($ip);

        $coef = $range * 0.0000089;
        $latMax = $response['lat'] + $coef;
        $lngMax = $response['lng'] + $coef / cos($response['lat'] * 0.018);
        $latMin = $response['lat'] - $coef;
        $lngMin = $response['lng'] - $coef / cos($response['lat'] * 0.018);

        $result = $placeRepository->findNearPlaces($latMax, $lngMax, $latMin, $lngMin);

        foreach ($result as &$place) {
            $place->distance = $locationService->distance($response['lat'], $response['lng'], $place->getLat(), $place->getLng());
        }

        usort($result, function ($a, $b) {
            if ($a->distance == $b->distance) return 0;
            if ($a->distance < $b->distance) return -1;
            return 1;
        });

        return $this->json(array_map(function ($item) { return $item->toArray(); }, $result));
    }

    /**
     * @Route("/cityPlaces", name="cityPlaces", methods={"GET"})
     * @param Request $request
     * @param PlaceRepository $placeRepository
     * @param LocationService $locationService
     * @return JsonResponse
     * @throws \Exception
     */
    public function getCityPlaces(Request $request, PlaceRepository $placeRepository, LocationService $locationService)
    {
        $city = $request->get('city');
        if (!isset($city)) {
            $ip = $request->getClientIp();
            $city = $locationService->getCoordinatesByIp($ip)['city'];
        }

        $result = $placeRepository->findByCity($city);

        return $this->json(array_map(function ($item) { return $item->toArray(); }, $result));
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
     * @param YandexGeoCoderService $geoCoderService
     * @return JsonResponse
     * @Route("/place/{id}", name="get_place", methods={"GET"})
     */
    public function getPlace(int $id, PlaceRepository $repository, YandexGeoCoderService $geoCoderService)
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
     * @param PlaceTypeRepository $placeTypeRepository
     * @param PlaceRepository $placeRepository
     * @param CityRepository $cityRepository
     * @param YandexGeoCoderService $geoCoderService
     * @return JsonResponse
     */
    public function addPlace(Request $request, PlaceTypeRepository $placeTypeRepository, PlaceRepository $placeRepository,
                             CityRepository $cityRepository, YandexGeoCoderService $geoCoderService)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $placeName = $request->get('name');
        $place = $placeRepository->findOneBy(['name' => $placeName]);
        if (!isset($place)) {
            $place = new Place();
            $fields = ['lat', 'lng', 'name', 'description'];

            foreach ($fields as $field) {
                $param = $request->get($field);
                if ($param) {
                    $place->{"set$field"}($param);
                }
            }

            $typeStr = $request->get('type');
            $type = $placeTypeRepository->findOrCreate($typeStr);
            $place->setType($type);

            $cityName = $geoCoderService->getCity($place->getLat(), $place->getLng());
            $city = $cityRepository->findOrCreate($cityName);
            $place->setCity($city);

            $entityManager->persist($place);
            $entityManager->flush();
        }

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
