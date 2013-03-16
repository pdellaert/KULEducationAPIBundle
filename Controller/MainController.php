<?php
namespace Dellaert\KULEducationXMLBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class MainController extends Controller
{
	public function indexAction()
	{
		return $this->render('DellaertKULEducationXMLBundle:Main:index.html.twig');
	}
}
