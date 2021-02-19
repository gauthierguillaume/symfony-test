<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\FilmsRepository;
use App\Entity\Films;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;


class FilmsController extends AbstractController
{
    /**
     * @Route("/films", name="films", methods={"GET"})
     */
    public function index(): Response
    {
        $films = $this->getDoctrine()
            ->getRepository(Films::class)
            ->findAll();
        return $this->render('films/index.html.twig', [
            'controller_name' => 'FilmsController',
            'films' => $films
        ]);
    }

    protected function serializeJson($objet)
    {
        $defaultContext = [
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object, $format, $context) {
                return $object->getNom();
            },
        ];
        $normalizer = new ObjectNormalizer(null, null, null, null, null, null, $defaultContext);
        $serializer = new Serializer([$normalizer], [new JsonEncoder()]);
        return $serializer->serialize($objet, 'json');
    }

    /**
     * @Route("/json/films", name="films_json", methods={"GET"})
     * @param filmsRepository $filmsRepository
     * @param Request $request
     * @return Response
     */
    public function filmsJson(FilmsRepository $filmsRepository, Request $request)
    {
        $filter = [];
        $em = $this->getDoctrine()->getManager();
        $metadata = $em->getClassMetadata(Films::class)->getFieldNames();
        foreach ($metadata as $value) {
            if ($request->query->get($value)) {
                $filter[$value] = $request->query->get($value);
            }
        }
        return JsonResponse::fromJsonString($this->serializeJson($filmsRepository->findBy($filter)));
    }

    /**
     * @Route("/api/create/film", name="create_film", methods={"POST"})
     * @param Request $request
     * @return Response
     */

    public function filmsCreate(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $newfilm = new Films();

        $newfilm->setNom($request->request->get("nom"))
            ->setSynopsis($request->request->get("synopsis"))
            ->setType("film");
        $entityManager->persist($newfilm);
        $entityManager->flush();
        $response = new Response();
        $response->setContent('Saved new film with id ' . $newfilm->getId());
        return $response;
    }

    /**
     * @Route("/api/create/serie", name="create_serie", methods={"POST"})
     * @param Request $request
     * @return Response
     */
    public function seriesCreate(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $newfilm = new Films();
        $newfilm->setNom($request->request->get("nom"))
            ->setSynopsis($request->request->get("synopsis"))
            ->setType("serie");
        $entityManager->persist($newfilm);
        $entityManager->flush();
        $response = new Response();
        $response->setContent('Saved new serie with id ' . $newfilm->getId());
        return $response;
    }

    /**
     * @Route("/film/get/{id}", name="getFilmbyId", methods={"GET"})
     * @param FilmsRepository $filmsRepository
     * @return Response
     */
    public function getFilmById($id, FilmsRepository $filmsRepository): Response
    {
        $films = $filmsRepository->findBy(['id' => $id]);
        return $this->render('films/index.html.twig', [
            'films' => $films,
        ]);
    }

    /**
     * @Route("/json/film/get/{id}", name="getFilmById_json", methods={"GET"})
     * @param $id
     * @param FilmsRepository $filmsRepository
     * @return Response
     */
    public function getfilmbyIdJson($id, FilmsRepository $filmsRepository): Response
    {
        $error = [];
        $response = new Response();
        $film = $filmsRepository->findBy(
            [
                'id' => $id
            ]
        );
        if (empty($film)) {
            array_push($error, "Ce film n'est pas disponible");
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
        } else {
            $response->setStatusCode(Response::HTTP_OK);
            $response->setContent($this->serializeJson($film));
        }
        return $response;
    }
}
