<?php
namespace Dellaert\KULEducationXMLBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class MainController extends Controller
{
	public function indexAction()
	{
		return $this->render('DellaertKULEducationXMLBundle:Main:index.html.twig',array('faculties'=>$this->getFaculties()));
	}

	public function getFaculties() {
		// Return value
		$fac = array();

		// URL Setup
		$url = $this->container->getParameter('dellaert_kul_education_xml.baseurl');
		$year = $this->container->getParameter('dellaert_kul_education_xml.baseyear');
		$language = substr($this->getRequest()->query->get('_locale'),0,1);
		$method = $this->container->getParameter('dellaert_kul_education_xml.method');
		$callUrl = $url.$year.'/opleidingen/'.$language.'/'.$method.'/index.xml'; 
		
		// Getting XML
		if( $xml = simplexml_load_file($callUrl, null, LIBXML_NOCDATA) ) {
			foreach( $xml->xpath('data/instelling/departement') as $fChild ) {
				$fac[] = array('id'=>(string) $fChild['objid'],'title'=>(string) $fChild->titels->titel);
			}
		}

		// Returning faculties
		return $fac;
	}
}
