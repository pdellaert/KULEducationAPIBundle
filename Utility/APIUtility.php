<?php
namespace Dellaert\KULEducationAPIBundle\Utility;

class APIUtility {

	public static function getLiveFacultiesByIdTitle($container,$locale) {
		// Locale
		$language = substr($locale,0,1);

		// Return value
		$data = array();

		// URL Setup
		$url = $container->getParameter('dellaert_kul_education_api.baseurl');
		$year = $container->getParameter('dellaert_kul_education_api.baseyear');
		$method = $container->getParameter('dellaert_kul_education_api.method');
		$callUrl = $url.$year.'/opleidingen/n/'.$method.'/index.xml'; 
		
		// Getting XML
		if( $xml = simplexml_load_file($callUrl, null, LIBXML_NOCDATA) ) {
			foreach( $xml->xpath("data/instelling/departement") as $fChild ) {
				$title = $fChild->xpath("titels/titel[@taal='$language']");

				if( empty($title) ) {
					$title = $fChild->xpath("titels/titel[@taal='".$container->getParameter('dellaert_kul_education_api.fallback_locale')."']");
				}

				$data[] = array('id'=>(string) $fChild['objid'],'title'=>(string) $title[0]);
			}
		}

		return $data;
	}


	public static function getLiveLevelsByIdTitle($container,$locale,$fid) {
		// Locale
		$language = substr($locale,0,1);

		// Return value
		$data = array();

		// URL Setup
		$url = $container->getParameter('dellaert_kul_education_api.baseurl');
		$year = $container->getParameter('dellaert_kul_education_api.baseyear');
		$method = $container->getParameter('dellaert_kul_education_api.method');
		$callUrl = $url.$year.'/opleidingen/n/'.$method.'/index.xml';
		
		// Getting XML
		if( $xml = simplexml_load_file($callUrl, null, LIBXML_NOCDATA) ) {
			$faculty = $xml->xpath("data/instelling/departement[@objid='$fid']");

			if( !empty($faculty) ) {
				foreach( $faculty[0]->xpath("classificaties/classificatie/graad") as $fChild ){
					$title = $fChild->xpath("omschrijvingen/omschrijving[@taal='$language']");

					if( empty($title) ) {
						$title = $fChild->xpath("omschrijvingen/omschrijving[@taal='".$container->getParameter('dellaert_kul_education_api.fallback_locale')."']");
					}

					$studiesInLang = $fChild->xpath("diplomas/diploma[originele_titel[@taal='$language']]");
					if( !empty($studiesInLang) ) {
						$data[] = array('id'=>(string) $fChild['id'],'title'=>(string) $title[0]);
					}
				}
			}
		}

		return $data;
	}

	public static function getLiveStudiesByIdTitle($container,$locale,$fid,$lid) {
		// Locale
		$language = substr($locale,0,1);

		// Return value
		$data = array();

		// URL Setup
		$url = $container->getParameter('dellaert_kul_education_api.baseurl');
		$year = $container->getParameter('dellaert_kul_education_api.baseyear');
		$method = $container->getParameter('dellaert_kul_education_api.method');
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

		return $data;
	}

	public static function getLiveProgramsByIdTitle($container,$locale,$sid) {
		// Locale
		$language = substr($locale,0,1);

		// Return value
		$data = array();

		// URL Setup
		$url = $container->getParameter('dellaert_kul_education_api.baseurl');
		$year = $container->getParameter('dellaert_kul_education_api.baseyear');
		$method = $container->getParameter('dellaert_kul_education_api.method');
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

		return $data;
	}

	public static function getLiveStagesByIdTitle($container,$locale, $pid) {
		// Locale
		$language = substr($locale,0,1);

		// Return value
		$data = array();

		// URL Setup
		$url = $container->getParameter('dellaert_kul_education_api.baseurl');
		$year = $container->getParameter('dellaert_kul_education_api.baseyear');
		$method = $container->getParameter('dellaert_kul_education_api.method');
		$callUrl = $url.$year.'/opleidingen/'.$language.'/'.$method.'/SC_'.$pid.'.xml';

		// Getting XML
		if( $xml = simplexml_load_file($callUrl, null, LIBXML_NOCDATA) ) {
			foreach( $xml->xpath("data/opleiding/fases/fase") as $fChild ) {
				$data[] = array('id'=> (int) $fChild['code']);
			}
		}

		return $data;
	}

	public static function getLiveCoursesInLevel($container,$locale,$pid,$phid) {
		// Locale
		$language = substr($locale,0,1);

		// Return value
		$data = array();

		// URL Setup
		$url = $container->getParameter('dellaert_kul_education_api.baseurl');
		$year = $container->getParameter('dellaert_kul_education_api.baseyear');
		$method = $container->getParameter('dellaert_kul_education_api.method');
		$callUrl = $url.$year.'/opleidingen/'.$language.'/'.$method.'/SC_'.$pid.'.xml';

		// Getting XML
		if( $xml = simplexml_load_file($callUrl, null, LIBXML_NOCDATA) ) {
			foreach( $xml->xpath("//opos/opo[fases/fase[contains(.,$phid)]]") as $fChild ) {
			$teachers = array();
			foreach( $fChild->xpath("docenten/docent") as $sChild ) {
				$teachers[] = array(
					'function' => (string) $sChild['functie'],
					'personel_id' => (string) $sChild['persno'],
					'name' => (string) $sChild->naam,
					'firstname' => (string) $sChild->voornaam,
					'lastname' => (string) $sChild->familienaam,
					'firstletter' => (string) $sChild->voorletters
					);
			}

			$data[] = array(
				'id' => (string) $fChild['objid'],
				'course_id' => (string) $fChild['short'],
				'title' => (string) $fChild->titel,
				'period' => (string) $fChild->periode,
				'studypoints' => (string) $fChild->pts,
				'mandatory' => (string) $fChild['verplicht'],
				'teachers' => $teachers
				);
			}
		}

		return $data;
	}

	public static function getLiveCoursesByGroupsInLevel($container,$locale,$pid,$phid) {
		// Locale
		$language = substr($locale,0,1);

		// Return value
		$data = array();

		// URL Setup
		$url = $container->getParameter('dellaert_kul_education_api.baseurl');
		$year = $container->getParameter('dellaert_kul_education_api.baseyear');
		$method = $container->getParameter('dellaert_kul_education_api.method');
		$callUrl = $url.$year.'/opleidingen/'.$language.'/'.$method.'/SC_'.$pid.'.xml';

		if( $xml = simplexml_load_file($callUrl, null, LIBXML_NOCDATA) ) {
			$cg = $xml->xpath("data/opleiding/cg[@level='1']");
			if( !empty($cg) ) {
				$data[(string) $cg[0]->titel] = APIUtility::parseCourseGroupInLevel($cg[0],$phid);
			}
		}

		return $data;
	}

	public static function parseCourseGroupInLevel($cg,$phid) {
		$data = array();
		foreach( $cg->xpath("opos/opo[fases/fase[contains(.,$phid)]]") as $fChild ) {
			$teachers = array();
			foreach( $fChild->xpath("docenten/docent") as $sChild ) {
				$teachers[] = array(
					'function' => (string) $sChild['functie'],
					'personel_id' => (string) $sChild['persno'],
					'name' => (string) $sChild->naam,
					'firstname' => (string) $sChild->voornaam,
					'lastname' => (string) $sChild->familienaam,
					'firstletter' => (string) $sChild->voorletters
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

		foreach( $cg->xpath("cg[@level='$nextLevel']") as $fChild ) {
			$subCg = APIUtility::parseCourseGroupInLevel($fChild,$phid);
			if( !empty($subCg) ) {
				$data[(string) $fChild->titel] = $subCg;
			}
		}

		return $data;
	}
}