<?php

namespace App\Controller;

use App\Form\SearchFormType;
use App\SearchEngine\SearchEngine;
use App\SearchEngine\SearchEngineParams;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

}
