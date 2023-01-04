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
        $page = $req->query->getInt(key: 'page', default: 1);

        $searchForm = $this->createForm(SearchFormType::class);
        $searchForm->handleRequest($req);

        $formData = null;
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $formData = $searchForm->getData();

            $params = new SearchEngineParams();
            $params->setSearchText($formData['searchText']);
            $params->setSearchType($formData['searchType']);
            $params->setCountryCode($formData['countryCode']);
            $params->setPage($page);
            $results = $this->searchEngine->search($params);
        } else {
            $results = null;
        }

        return $this->render('search.html.twig', [
            'searchResults' => $results,
            'searchForm' => $searchForm,
            'formData' => $formData,
        ]);
    }

}
