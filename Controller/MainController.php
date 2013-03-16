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

	public function listLevelsByIdTitleAction($fid) {
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
				foreach( $faculty[0]->xpath("classificaties/classificatie/graad") as $fChild ){
					$title = $fChild->xpath("omschrijvingen/omschrijving[@taal='$language']");

					if( empty($title) ) {
						$title = $fChild->xpath("omschrijvingen/omschrijving[@taal='".$this->container->getParameter('dellaert_kul_education_xml.fallback_locale')."']");
					}

					$data[] = array('id'=>(string) $fChild['id'],'title'=>(string) $title[0]);
				}
			}
		}

		// Returning levels
		$response = new Response(json_encode($data));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}

	public function listStudiesByIdTitleAction($fid,$lid) {
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
				$level = $faculty[0]->xpath("classificaties/classificatie/graad[@id='$lid']");

				if( !empty($level) ) {
					foreach( $level[0]->xpath("diplomas/diploma") as $fChild ){
						if( ((string) $fChild->originele_titel['taal']) == $language ) {
							$data[] = array('id'=>(string) $fChild['objid'],'title'=>(string) $fChild->originele_titel);
						}
					}
				}
			}
		}

		// Returning studies
		$response = new Response(json_encode($data));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}

	public function listProgramsByIdTitleAction($sid) {
		// Locale
		$language = substr($this->getRequest()->getLocale(),0,1);

		// Return value
		$data = array();

		// URL Setup
		$url = $this->container->getParameter('dellaert_kul_education_xml.baseurl');
		$year = $this->container->getParameter('dellaert_kul_education_xml.baseyear');
		$method = $this->container->getParameter('dellaert_kul_education_xml.method');
		$callUrl = $url.$year.'/opleidingen/'.$language.'/'.$method.'/CQ_'.$sid.'.xml';

		// Getting XML
		if( $xml = simplexml_load_file($callUrl, null, LIBXML_NOCDATA) ) {
			foreach( $xml->xpath("data/diploma/opleidingen/opleiding") as $fChild ){
				$title = $fChild->titel;

				if( !empty($title) ) {
					$data[] = array('id'=>(string) $fChild['objid'],'title'=>(string) $title,'studypoints'=>(string) $fChild->studiepunten);
				}
			}
		}

		// Returning studies
		$response = new Response(json_encode($data));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}

	public function listStagesByIdTitleAction($pid) {
		// Locale
		$language = substr($this->getRequest()->getLocale(),0,1);

		// Return value
		$data = array();

		// URL Setup
		$url = $this->container->getParameter('dellaert_kul_education_xml.baseurl');
		$year = $this->container->getParameter('dellaert_kul_education_xml.baseyear');
		$method = $this->container->getParameter('dellaert_kul_education_xml.method');
		$callUrl = $url.$year.'/opleidingen/'.$language.'/'.$method.'/SC_'.$pid.'.xml';

		// Getting XML
		if( $xml = simplexml_load_file($callUrl, null, LIBXML_NOCDATA) ) {
			foreach( $xml->xpath("data/opleiding/fases/fase") as $fChild ) {
				$data[] = array('id'=> (int) $fChild['code']);
			}
		}

		// Returning studies
		$response = new Response(json_encode($data));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}

	public function listCoursesByIdTitleAction($pid,$phid) {
		// Locale
		$language = substr($this->getRequest()->getLocale(),0,1);

		// Return value
		$data = array();

		// URL Setup
		$url = $this->container->getParameter('dellaert_kul_education_xml.baseurl');
		$year = $this->container->getParameter('dellaert_kul_education_xml.baseyear');
		$method = $this->container->getParameter('dellaert_kul_education_xml.method');
		$callUrl = $url.$year.'/opleidingen/'.$language.'/'.$method.'/SC_'.$pid.'.xml';

		// Getting XML
		if( $xml = simplexml_load_file($callUrl, null, LIBXML_NOCDATA) ) {
			foreach( $xml->xpath("opos/opo[fases/fase[countains(.,$phid)]]") as $fChild ) {
				var_dump($fChild);
				//$data[] = array('id'=> (int) $fChild['code']);
			}
		}

		// Returning studies
		$response = new Response(json_encode($data));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}
}
