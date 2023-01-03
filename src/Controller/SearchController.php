<?php

namespace App\Controller;

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
        $searchText = $req->query->get('searchText') ?? '';
        $searchType = $req->query->get('searchType') ?? 'city';
        $countryCode = $req->query->get('countryCode') ?? 'US';

        $params = new SearchEngineParams();
        $params->setSearchText($searchText);
        $params->setSearchType($searchType);
        $params->setCountyCode($countryCode);

        $results = $this->searchEngine->search($params);

        return $this->render('search.html.twig', [
            'searchText' => '55125',
            'searchResults' => $results,
        ]);
    }

}
