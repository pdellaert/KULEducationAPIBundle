<?php
namespace Dellaert\KULEducationAPIBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class APIController extends Controller {

	public function listFacultiesByIdTitleAction() {
		// Locale
		$language = substr($this->getRequest()->getLocale(),0,1);

		// Return value
		$data = array();

		// URL Setup
		$url = $this->container->getParameter('dellaert_kul_education_api.baseurl');
		$year = $this->container->getParameter('dellaert_kul_education_api.baseyear');
		$method = $this->container->getParameter('dellaert_kul_education_api.method');
		$callUrl = $url.$year.'/opleidingen/n/'.$method.'/index.xml'; 
		
		// Getting XML
		if( $xml = simplexml_load_file($callUrl, null, LIBXML_NOCDATA) ) {
			foreach( $xml->xpath("data/instelling/departement") as $fChild ) {
				$title = $fChild->xpath("titels/titel[@taal='$language']");

				if( empty($title) ) {
					$title = $fChild->xpath("titels/titel[@taal='".$this->container->getParameter('dellaert_kul_education_api.fallback_locale')."']");
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
		$url = $this->container->getParameter('dellaert_kul_education_api.baseurl');
		$year = $this->container->getParameter('dellaert_kul_education_api.baseyear');
		$method = $this->container->getParameter('dellaert_kul_education_api.method');
		$callUrl = $url.$year.'/opleidingen/n/'.$method.'/index.xml';
		
		// Getting XML
		if( $xml = simplexml_load_file($callUrl, null, LIBXML_NOCDATA) ) {
			$faculty = $xml->xpath("data/instelling/departement[@objid='$fid']");

			if( !empty($faculty) ) {
				foreach( $faculty[0]->xpath("classificaties/classificatie/graad") as $fChild ){
					$title = $fChild->xpath("omschrijvingen/omschrijving[@taal='$language']");

					if( empty($title) ) {
						$title = $fChild->xpath("omschrijvingen/omschrijving[@taal='".$this->container->getParameter('dellaert_kul_education_api.fallback_locale')."']");
					}

					$studiesInLang = $fChild->xpath("diplomas/diploma[originele_titel[@taal='$language']]");
					if( !empty($studiesInLang) ) {
						$data[] = array('id'=>(string) $fChild['id'],'title'=>(string) $title[0]);
					}
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
		$url = $this->container->getParameter('dellaert_kul_education_api.baseurl');
		$year = $this->container->getParameter('dellaert_kul_education_api.baseyear');
		$method = $this->container->getParameter('dellaert_kul_education_api.method');
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
		$url = $this->container->getParameter('dellaert_kul_education_api.baseurl');
		$year = $this->container->getParameter('dellaert_kul_education_api.baseyear');
		$method = $this->container->getParameter('dellaert_kul_education_api.method');
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
		$url = $this->container->getParameter('dellaert_kul_education_api.baseurl');
		$year = $this->container->getParameter('dellaert_kul_education_api.baseyear');
		$method = $this->container->getParameter('dellaert_kul_education_api.method');
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

	public function listCoursesInLevelAction($pid,$phid) {
		// Locale
		$language = substr($this->getRequest()->getLocale(),0,1);

		// Return value
		$data = array();

		// URL Setup
		$url = $this->container->getParameter('dellaert_kul_education_api.baseurl');
		$year = $this->container->getParameter('dellaert_kul_education_api.baseyear');
		$method = $this->container->getParameter('dellaert_kul_education_api.method');
		$callUrl = $url.$year.'/opleidingen/'.$language.'/'.$method.'/SC_'.$pid.'.xml';

		// Getting XML
		if( $xml = simplexml_load_file($callUrl, null, LIBXML_NOCDATA) ) {
			foreach( $xml->xpath("//opos/opo[fases/fase[contains(.,$phid)]]") as $fChild ) {
				$teachers = array();
				foreach( $fChild->xpath("docenten/docent") as $sChild ) {
					$teachers[] = array(
						'personel_id' => (string) $sChild['persno'],
						'firstname' => (string) $sChild->voornaam,
						'lastname' => (string) $sChild->familienaam
						);
				}
				switch((string) $fChild['verplicht']) {
					case 'J':
						$base = 'mandatory';
						break;
					default:
						$base = 'optional';
				}
				$data[$base][] = array(
					'id' => (string) $fChild['objid'],
					'course_id' => (string) $fChild['short'],
					'title' => (string) $fChild->titel,
					'period' => (string) $fChild->periode,
					'studypoints' => (string) $fChild->pts,
					'teachers' => $teachers
					);
			}
		}

		// Returning courses
		$response = new Response(json_encode($data));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}

	public function listCoursesByGroupsInLevelAction($pid,$phid) {
		// Locale
		$language = substr($this->getRequest()->getLocale(),0,1);

		// Return value
		$data = array();

		// URL Setup
		$url = $this->container->getParameter('dellaert_kul_education_api.baseurl');
		$year = $this->container->getParameter('dellaert_kul_education_api.baseyear');
		$method = $this->container->getParameter('dellaert_kul_education_api.method');
		$callUrl = $url.$year.'/opleidingen/'.$language.'/'.$method.'/SC_'.$pid.'.xml';

		if( $xml = simplexml_load_file($callUrl, null, LIBXML_NOCDATA) ) {
			$cg = $xml->xpath("data/opleiding/cg[@level='1'");
			if( !empty($cg) ) {
				$data[(string) $cg->titel] = $this->parseCourseGroupInLevel($cg);
			}
		}

		// Returning courses
		$response = new Response(json_encode($data));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}

	private function parseCourseGroupInLevel($cg,$phid) {
		$data = array();
		foreach( $cg->xpath("opos/opo[fases/fase[contains(.,$phid)]]") as $fChild ) {
			$teachers = array();
			foreach( $fChild->xpath("docenten/docent") as $sChild ) {
				$teachers[] = array(
					'personel_id' => (string) $sChild['persno'],
					'naam' => (string) $sChild->naam,
					'firstname' => (string) $sChild->voornaam,
					'lastname' => (string) $sChild->familienaam
					);
			}

			$data['courses'][] = array(
				'id' => (string) $fChild['objid'],
				'course_id' => (string) $fChild['short'],
				'title' => (string) $fChild->titel,
				'period' => (string) $fChild->periode,
				'studypoints' => (string) $fChild->pts,
				'mandatory' => (string) $fChild['verplicht'],
				'teachers' => $teachers
				);
		}

		$nextLevel = ((int) $cg['level'])+1;

		foreach( $cg->xpath("cg[@level='$nextLevel'") as $fChild ) {
			$data[(string) $fChild->titel] = $this->parseCourseGroupInLevel($fChil,$phid);
		}

		return $data;
	}
}
