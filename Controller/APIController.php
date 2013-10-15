<?php
namespace Dellaert\KULEducationAPIBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Dellaert\KULEducationAPIBundle\Utility\APIUtility;

class APIController extends Controller {

	public function listFacultiesByIdTitleAction() {
		// Getting Faculties live
		$data = APIUtility::getLiveFacultiesByIdTitle($this->container,$this->getRequest()->getLocale());

		// Returning faculties
		$response = new Response(json_encode($data));
		$response->headers->set('Content-Type', 'application/json');
    	return $response;
	}

	public function listLevelsByIdTitleAction($fid) {
		// Getting Levels live
		$data = APIUtility::getLiveLevelsByIdTitle($this->container,$this->getRequest()->getLocale(), $fid);

		// Returning levels
		$response = new Response(json_encode($data));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}

	public function listStudiesByIdTitleAction($fid,$lid) {
		// Getting Studies live
		$data = APIUtility::getLiveStudiesByIdTitle($this->container,$this->getRequest()->getLocale(),$fid,$lid);

		// Returning studies
		$response = new Response(json_encode($data));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}

	public function listProgramsByIdTitleAction($sid) {
		// Getting Programs live
		$data = APIUtility::getLiveProgramsByIdTitle($this->container,$this->getRequest()->getLocale(),$sid);

		// Returning programs
		$response = new Response(json_encode($data));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}

	public function listStagesByIdTitleAction($pid) {
		// Getting Stages live
		$data = APIUtility::getLiveStagesByIdTitle($this->container,$this->getRequest()->getLocale(),$pid);

		// Returning stages
		$response = new Response(json_encode($data));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}

	public function listCoursesInLevelAction($pid,$phid) {
		// Getting Courses live
		$data = APIUtility::getLiveCoursesInLevel($this->container,$this->getRequest()->getLocale(),$pid,$phid);

		// Returning courses
		$response = new Response(json_encode($data));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}

	public function listCoursesByGroupsInLevelAction($pid,$phid) {
		// Getting Courses live
		$data = APIUtility::getLiveCoursesByGroupsInLevel($this->container,$this->getRequest()->getLocale(),$pid,$phid);

		// Returning courses
		$response = new Response(json_encode($data));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}

	public function listCourseDetailsAction($cid) {
		// Getting Stages live
		$data = APIUtility::getLiveCourseDetails($this->container,$this->getRequest()->getLocale(),$cid);

		// Returning stages
		$response = new Response(json_encode($data));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}

}
