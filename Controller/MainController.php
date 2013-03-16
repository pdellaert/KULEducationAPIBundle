<?php
namespace Dellaert\KULEducationXMLBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class MainController extends Controller
{
	public function indexAction()
	{
		$baseurl = $this->container->getParameter('dellaert_kul_education_xml.baseurl');
		return $this->render('DellaertKULEducationXMLBundle:Main:index.html.twig',array('baseurl'=>$baseurl));
	}
}
