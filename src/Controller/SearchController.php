<?php

namespace App\Controller;

use App\Form\SearchFormType;
use App\Geonames\Geocoder;
use App\SearchEngine\SearchEngine;
use App\SearchEngine\SearchEngineParams;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    private SearchEngine $searchEngine;

    public function __construct(SearchEngine $searchEngine)
    {
        $this->searchEngine = $searchEngine;
    }

    #[Route('/search', name: 'app_search')]
    public function search(Request $req): Response
    {
        $searchForm = $this->createForm(SearchFormType::class);
        $searchForm->handleRequest($req);

        $pageFilters = null;
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $formData = $searchForm->getData();
            $pageFilters = $formData;

            $params = new SearchEngineParams();
            $params->setSearchText($formData['searchText']);
            $params->setSearchType($formData['searchType']);
            $params->setCountryCode($formData['countryCode']);
            $params->setPage((int) $formData['page']);

            if (!empty($formData['searchRadius'])) {
                $params->setSearchRadius((float)$formData['searchRadius']);
            }

            $results = $this->searchEngine->search($params);

            $pageFilters['searchRadius'] = $results->searchRadius;
        } else {
            $results = null;
        }

        return $this->render('search.html.twig', [
            'searchResults' => $results,
            'searchForm' => $searchForm,
            'pageFilters' => $pageFilters,
        ]);
    }

    #[Route('/search/cities', name: 'app_search_cities')]
    public function searchCities(Request $req, Geocoder $geocoder): JsonResponse
    {
        $searchText = $req->get('q', default: '');
        $countryCode = $req->get('countryCode', default: 'US');

        $geocoder->setCountry($countryCode);
        $results = $geocoder->searchCities($searchText);
        $results = array_map(function($result) {
            return [
                'value' => $result->title,
                'title' => $result->title,
            ];
        }, $results);
        return new JsonResponse($results);
    }

    #[Route('/search/postalCodes', name: 'app_search_postal_codes')]
    public function searchPostalCodes(Request $req, Geocoder $geocoder): JsonResponse
    {
        $searchText = $req->get('q', default: '');
        $countryCode = $req->get('countryCode', default: 'US');

        $geocoder->setCountry($countryCode);
        $results = $geocoder->searchPostalCodes($searchText);
        $results = array_map(function($result) {
            return [
                'value' => explode(' ', $result->title)[0],
                'title' => $result->title,
            ];
        }, $results);
        return new JsonResponse($results);
    }

}
