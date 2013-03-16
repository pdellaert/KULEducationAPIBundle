<?php
namespace Dellaert\KULEducationXMLBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class MainController extends Controller {
	public function indexAction() {
		$lang = substr($this->getRequest()->getLocale(),0,1);
		return $this->render('DellaertKULEducationXMLBundle:Main:index.html.twig',array('faculties'=>$this->getFaculties($lang)));
	}

	public function getFaculties($language="n") {
		// Return value
		$fac = array();

		// URL Setup
		$url = $this->container->getParameter('dellaert_kul_education_xml.baseurl');
		$year = $this->container->getParameter('dellaert_kul_education_xml.baseyear');
		$method = $this->container->getParameter('dellaert_kul_education_xml.method');
		$callUrl = $url.$year.'/opleidingen/n/'.$method.'/index.xml'; 
		
		// Getting XML
		if( $xml = simplexml_load_file($callUrl, null, LIBXML_NOCDATA) ) {
			foreach( $xml->xpath('data/instelling/departement') as $fChild ) {
				$title = $fChild->xpath("titels/titel[@taal='$language']");

				if( empty($title) ) {
					$title = $fChild->xpath("titels/titel[@taal='".$this->container->getParameter('dellaert_kul_education_xml.fallback_locale')."']");
				}

				$fac[] = array('id'=>(string) $fChild['objid'],'title'=>(string) $title[0]);
			}
		}

		// Returning faculties
		return $fac;
	}
}
