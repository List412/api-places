<?php

namespace App\Controller;

use App\Entity\Place;
use App\Repository\CityRepository;
use App\Repository\PlaceRepository;
use App\Repository\PlaceTypeRepository;
use App\Service\LocationService;
use App\Service\YandexGeoCoderService;
use PHPUnit\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PlaceController extends BaseController
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

        $errors = [];
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            $errors[] = "Invalid parameter 'ip'";
        }

        if (!is_int($range) && $range <= 0) {
            $errors[] = "Parameter 'range' should be integer and greater than 0";
        }

        try {
            $response = $locationService->getCoordinatesByIp($ip);
        } catch (Exception $e) {
            $errors[] = "Failed to get location for ip: $ip";
        }

        if (count($errors)) {
            return $this->error($errors);
        }

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

        return $this->success(array_map(function ($item) { return $item->toArray(); }, $result));
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

        return $this->success(array_map(function ($item) { return $item->toArray(); }, $result));
    }

    /**
     * @Route("/place", name="place", methods={"GET"})
     * @param PlaceRepository $repository
     * @return JsonResponse
     */
    public function getPlaces(PlaceRepository $repository)
    {
        $products = $repository->findAll();
        return $this->success(array_map(function ($item) { return $item->toArray(); }, $products));
    }

    /**
     * @param int $id
     * @param PlaceRepository $repository
     * @return JsonResponse
     * @Route("/place/{id}", name="get_place", methods={"GET"})
     */
    public function getPlace(int $id, PlaceRepository $repository)
    {
        $place = $repository->find($id);

        $errors = [];

        if (!$place) {
            $errors[] =  "No place found for id $id";
        }

        if (count($errors)) {
            return $this->error($errors);
        }

        return $this->success($place->toArray());
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
        $errors = [];
        $entityManager = $this->getDoctrine()->getManager();

        $placeName = $request->get('name');
        $place = $placeRepository->findOneBy(['name' => $placeName]);

        if ($place) {
            $errors[] = "Place $placeName already exists";
            return $this->error($errors);
        } else {
            $place = new Place();
            $fields = ['lat', 'lng', 'name', 'description'];

            try {
                foreach ($fields as $field) {
                    $param = $request->get($field);
                    if ($param) {
                        $place->{"set$field"}($param);
                    }
                }
            } catch (\Exception $e) {
                $errors[] = "Invalid field '$field'";
                return $this->error($errors);
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

        return $this->success($place->toArray());
    }

    /**
     * @param int $id
     * @param Request $request
     * @param PlaceRepository $repository
     * @return JsonResponse
     * @Route("/place/{id}", name="update_place", methods={"PUT"})
     */
    public function updatePlace(int $id, Request $request, PlaceRepository $repository)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $place = $repository->find($id);

        $errors = [];

        if (!$place) {
            $errors[] =  "No place found for id $id";
        }

        if (count($errors)) {
            return $this->error($errors);
        }

        $fields = ['lat', 'lng', 'name', 'description'];

        try {
            foreach ($fields as $field) {
                $param = $request->get($field);
                if ($param) {
                    $place->{"set$field"}($param);
                }
            }
        } catch (\Exception $e) {
            $errors[] = "Invalid field '$field'";
            return $this->error($errors);
        }

        $entityManager->flush();

        return $this->success($place->toArray());
    }

    /**
     * @param int $id
     * @param PlaceRepository $repository
     * @Route("/place{id}", name="delete_place", methods={"DELETE"})
     * @return JsonResponse
     */
    public function deletePlace(int $id, PlaceRepository $repository)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $place = $repository->find($id);

        $errors = [];

        if (!$place) {
            $errors[] =  "No place found for id $id";
        }

        if (count($errors)) {
            return $this->error($errors);
        }

        $entityManager->remove($place);
        $entityManager->flush();
        return $this->success("Place $id was deleted successfully");
    }
}
