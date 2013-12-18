<?php
namespace Dellaert\KULEducationAPIBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Dellaert\KULEducationAPIBundle\Utility\APIUtility;

class APIController extends Controller {

	public function listSchoolsByIdTitleAction() {
		// Getting Schools live
		$data = APIUtility::getLiveSchoolsByIdTitle($this->container,$this->getRequest()->getLocale());

		// Returning faculties
		$response = new Response(json_encode($data));
		$response->headers->set('Content-Type', 'application/json');
    	return $response;
	}

	public function listFacultiesByIdTitleAction($scid) {
		// Getting Faculties live
		$data = APIUtility::getLiveFacultiesByIdTitle($this->container,$this->getRequest()->getLocale(),$scid);

		// Returning faculties
		$response = new Response(json_encode($data));
		$response->headers->set('Content-Type', 'application/json');
    	return $response;
	}

	public function listLevelsByIdTitleAction($scid,$fid) {
		// Getting Levels live
		$data = APIUtility::getLiveLevelsByIdTitle($this->container,$this->getRequest()->getLocale(), $scid, $fid);

		// Returning levels
		$response = new Response(json_encode($data));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}

	public function listStudiesByIdTitleAction($scid,$fid,$lid) {
		// Getting Studies live
		$data = APIUtility::getLiveStudiesByIdTitle($this->container,$this->getRequest()->getLocale(),$scid,$fid,$lid);

		// Returning studies
		$response = new Response(json_encode($data));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}

	public function listProgramsByIdTitleAction($scid,$sid) {
		// Getting Programs live
		$data = APIUtility::getLiveProgramsByIdTitle($this->container,$this->getRequest()->getLocale(),$scid,$sid);

		// Returning programs
		$response = new Response(json_encode($data));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}

	public function listStagesByIdTitleAction($scid,$pid) {
		// Getting Stages live
		$data = APIUtility::getLiveStagesByIdTitle($this->container,$this->getRequest()->getLocale(),$scid,$pid);

		// Returning stages
		$response = new Response(json_encode($data));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}

	public function listCoursesInLevelAction($scid,$pid,$phid,$respect_no_show) {
		// Getting Courses live
		$data = APIUtility::getLiveCoursesInLevel($this->container,$this->getRequest()->getLocale(),$scid,$pid,$phid,$respect_no_show);

		// Returning courses
		$response = new Response(json_encode($data));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}

	public function listCoursesByGroupsInLevelAction($scid,$pid,$phid,$respect_no_show) {
		// Getting Courses live
		$data = APIUtility::getLiveCoursesByGroupsInLevel($this->container,$this->getRequest()->getLocale(),$scid,$pid,$phid,$respect_no_show);

		// Returning courses
		$response = new Response(json_encode($data));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}

	public function listCourseDetailsAction($scid,$cid) {
		// Getting Stages live
		$data = APIUtility::getLiveCourseDetails($this->container,$this->getRequest()->getLocale(),$scid,$cid);

		// Returning stages
		$response = new Response(json_encode($data));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}

}
