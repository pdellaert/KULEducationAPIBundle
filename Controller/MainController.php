<?php
namespace Dellaert\KULEducationXMLBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class MainController extends Controller {

	public function indexAction() {
		return $this->render('DellaertKULEducationXMLBundle:Main:index.html.twig');
	}

	public function listFacultiesByIdTitleAction() {
		// Locale
		$language = substr($this->getRequest()->getLocale(),0,1);

		// Return value
		$data = array();

		// URL Setup
		$url = $this->container->getParameter('dellaert_kul_education_xml.baseurl');
		$year = $this->container->getParameter('dellaert_kul_education_xml.baseyear');
		$method = $this->container->getParameter('dellaert_kul_education_xml.method');
		$callUrl = $url.$year.'/opleidingen/n/'.$method.'/index.xml'; 
		
		// Getting XML
		if( $xml = simplexml_load_file($callUrl, null, LIBXML_NOCDATA) ) {
			foreach( $xml->xpath("data/instelling/departement") as $fChild ) {
				$title = $fChild->xpath("titels/titel[@taal='$language']");

				if( empty($title) ) {
					$title = $fChild->xpath("titels/titel[@taal='".$this->container->getParameter('dellaert_kul_education_xml.fallback_locale')."']");
				}

				$data[] = array('id'=>(string) $fChild['objid'],'title'=>(string) $title[0]);
			}
		}

		// Returning faculties
		$response = new Response(json_encode($data));
		$response->headers->set('Content-Type', 'application/json');
    	return $response;
	}

	public function listStudiesByIdTitleAction($fid) {
		// Locale
		$language = substr($this->getRequest()->getLocale(),0,1);

		// Return value
		$data = array();

		// URL Setup
		$url = $this->container->getParameter('dellaert_kul_education_xml.baseurl');
		$year = $this->container->getParameter('dellaert_kul_education_xml.baseyear');
		$method = $this->container->getParameter('dellaert_kul_education_xml.method');
		$callUrl = $url.$year.'/opleidingen/n/'.$method.'/index.xml';
		
		// Getting XML
		if( $xml = simplexml_load_file($callUrl, null, LIBXML_NOCDATA) ) {
			$faculty = $xml->xpath("data/instelling/departement[@objid='$fid']");

			if( !empty($faculty) ) {
				foreach( $faculty[0]->xpath("classificaties/classificatie/graad/diplomas/diploma") as $fChild ){
					if( ((string) $fChild->originele_titel['taal']) == $language ) {
						$data[] = array('id'=>(string) $fChild['objid'],'title'=>(string) $fChild->originele_titel);
					}
				}
			}
		}

		// Returning studies
		$response = new Response(json_encode($data));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}
}
