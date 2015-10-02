<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Form\ImobiParserType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

class MainController extends Controller {

    public function indexAction(Request $request) {
        $form = $this->createForm(new ImobiParserType());
        $queryBuilder = $this->getDoctrine()->getRepository('AppBundle:Offer')->getAllOffersQuery();
        $page = $request->get('page', 1);
        $adapter = new DoctrineORMAdapter($queryBuilder);
        $pager = new Pagerfanta($adapter);
        $pager->setMaxPerPage($this->container->getParameter('max_results_per_page'));
        try {
            $pager->setCurrentPage($page);
        } catch (NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        return $this->render('app/index.html.twig', array(
                    'pager' => $pager,
                    'form' => $form->createView(),
        ));
    }

    public function searchAction(Request $request) {
        $form = $this->createForm(new ImobiParserType());
        $form->handleRequest($request);
        $data = array_filter($form->getData());
        $page = $request->get('page', 1);
        $queryBuilder = $this->getDoctrine()->getRepository('AppBundle:Offer')->getFilteredOffers($data, true);
        $offers = $this->getDoctrine()->getRepository('AppBundle:Offer')->getFilteredOffers($data);
        $adapter = new DoctrineORMAdapter($queryBuilder);
        $pager = new Pagerfanta($adapter);
        $pager->setMaxPerPage($this->container->getParameter('max_results_per_page'));
        try {
            $pager->setCurrentPage($page);
        } catch (NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }
        $template = $this->renderView('app/partials/results.html.twig', array(
            'pager' => $pager,
            'error' => !empty($offers) ? null : $this->get('translator')->trans('error.search-offers'),
        ));

        return new JsonResponse(array('success' => true, 'template' => $template));
    }

    //TODO: price sort ASC and DESC
    public function priceSortAction(Request $request) {
        $data = $request->getContent();

        $filteredOffers = $this->getDoctrine()->getRepository('AppBundle:Offer')->getFilteredOffers(array('priceSort' => $data));
        $template = $this->renderView('app/partials/results.html.twig', array(
            'offers' => $filteredOffers,
            'error' => !empty($filteredOffers) ? null : $this->get('translator')->trans('error.search-offers')
        ));

        return new JsonResponse(array('success' => true, 'template' => $template));
    }

    public function parseAction() {
        $parser = new Parser();
        $urls = $parser->parse(file_get_contents(__DIR__ . '../../Resources/config/parsed_urls.yml'));
        foreach ($urls as $key => $url) {
            $responseArray = $this->get("app.ws.params_helper")->getParserResponse($url);

            if (!empty($responseArray['tables'][0]['results'])) {
                $this->get("app.data_process_service")->prepareDataForSaving($responseArray['tables'][0]['results'], $key);
            }
        }

        return $this->redirect($this->generateUrl('app_homepage'));
    }

}
