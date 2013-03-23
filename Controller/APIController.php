<?php
namespace Dellaert\KULEducationAPIBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Dellaert\KULEducationAPIBundle\Utility\APIUtility;

class APIController extends Controller {

	public function listFacultiesByIdTitleAction() {
		// Getting Faculties live
		$data = APIUtility::getLiveFacultiesByIdTitle($this);

		// Returning faculties
		$response = new Response(json_encode($data));
		$response->headers->set('Content-Type', 'application/json');
    	return $response;
	}

	public function listLevelsByIdTitleAction($fid) {
		// Getting Levels live
		$data = APIUtility::getLiveLevelsByIdTitle($this, $fid);

		// Returning levels
		$response = new Response(json_encode($data));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}

	public function listStudiesByIdTitleAction($fid,$lid) {
		// Getting Studies live
		$data = APIUtility::getLiveStudiesByIdTitle($this,$fid,$lid);

		// Returning studies
		$response = new Response(json_encode($data));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}

	public function listProgramsByIdTitleAction($sid) {
		// Getting Programs live
		$data = APIUtility::getLiveProgramsByIdTitle($this,$sid);

		// Returning programs
		$response = new Response(json_encode($data));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}

	public function listStagesByIdTitleAction($pid) {
		// Getting Stages live
		$data = APIUtility::getLiveStagesByIdTitle($this,$pid);

		// Returning stages
		$response = new Response(json_encode($data));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}

	public function listCoursesInLevelAction($pid,$phid) {
		// Getting Courses live
		$data = APIUtility::getLiveCoursesInLevel($this,$pid,$phid);

		// Returning courses
		$response = new Response(json_encode($data));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}

	public function listCoursesByGroupsInLevelAction($pid,$phid) {
		// Getting Courses live
		$data = APIUtility::getLiveCoursesByGroupsInLevel($this,$pid,$phid);

		// Returning courses
		$response = new Response(json_encode($data));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}
	
}
