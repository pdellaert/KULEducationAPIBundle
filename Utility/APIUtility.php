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
				'original_language' => (string) $fChild['originele_taal'],
				'teachers' => $teachers
				);
			}
		}

		return $data;
	}

	public static function getLiveCoursesByGroupsInLevel($container,$locale,$pid,$phid,$respect_no_show) {
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
			if( !empty($cg) && ( $respect_no_show == 0 || ($respect_no_show == 1 && $cg->tonen != 'N') ) ) {
				$data[(string) $cg[0]->titel] = APIUtility::parseCourseGroupInLevel($cg[0],$phid,$respect_no_show);
			}
		}

		return $data;
	}

	public static function parseCourseGroupInLevel($cg,$phid,$respect_no_show) {
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
				'original_language' => (string) $fChild->originele_taal,
				'teachers' => $teachers
				);
		}

		$nextLevel = ((int) $cg['level'])+1;

		foreach( $cg->xpath("cg[@level='$nextLevel']") as $fChild ) {
			$subCg = APIUtility::parseCourseGroupInLevel($fChild,$phid,$respect_no_show);
			if( !empty($subCg) && ( $respect_no_show == 0 || ($respect_no_show == 1 && $subCg->tonen != 'N') ) ) {
				$data[(string) $fChild->titel] = $subCg;
			}
		}

		return $data;
	}

	public static function getLiveCourseDetails($container,$original_language,$cid) {
		// Locale
		$language = strtolower(substr($original_language,0,1));

		// Return value
		$data = array();

		// URL Setup
		$url = $container->getParameter('dellaert_kul_education_api.baseurl');
		$year = $container->getParameter('dellaert_kul_education_api.baseyear');
		$method = $container->getParameter('dellaert_kul_education_api.method');
		$callUrl = $url.$year.'/syllabi/'.$language.'/'.$method.'/'.$cid.strtoupper($language).'.xml';

		if( $xml = simplexml_load_file($callUrl, null, LIBXML_NOCDATA) ) {
			$course = $xml->xpath("data/opo");
			if( !empty($course) ) {
				$teachers = array();
				foreach( $course[0]->xpath("docenten/docent") as $fChild ) {
					$teachers[] = array(
						'function' => (string) $fChild['functie'],
						'personel_id' => (string) $fChild['persno'],
						'name' => (string) $fChild->naam,
						'firstname' => (string) $fChild->voornaam,
						'lastname' => (string) $fChild->familienaam,
						'firstletter' => (string) $fChild->voorletters
						);
				}

				// Teaching parts
				$olas = array();
				foreach( $course[0]->xpath("olas/ola") as $fChild ) {
					$olaTeachers = array();
					foreach( $fChild->xpath("docenten/docent") as $sChild ) {
						$olaTeachers[] = array(
							'function' => (string) $sChild['functie'],
							'personel_id' => (string) $sChild['persno'],
							'name' => (string) $sChild->naam,
							'firstname' => (string) $sChild->voornaam,
							'lastname' => (string) $sChild->familienaam,
							'firstletter' => (string) $sChild->voorletters
							);
					}

					$olas[] = array(
						'id' => (string) $fChild['objid'],
						'ola_id' => (string) $fChild['short'],
						'title' => (string) $fChild->titel,
						'period' => (string) $fChild->periode,
						'studypoints' => (string) $fChild->studiepunten,
						'duration' => (string) $fChild->begeleidingsuren,
						'format' => (string) $fChild->werkvorm,
						'format_extra' => (string) $fChild->toelichting_werkvorm,
						'language' => array( ((string) $fChild->onderwijstaal->code) => ((string) $fChild->onderwijstaal->tekst) ),
						'language_extra' => (string) $fChild->toelichting_onderwijstaal,
						'content' => (string) $fChild->inhoud,
						'aims' => (string) $fChild->doelstellingen,
						'course_material' => (string) $fChild->studiemateriaal,
						'teachers' => $olaTeachers
						);
				}

				// Evaluation parts
				$evas = array();
				foreach( $course[0]->xpath("evas/eva") as $fChild ) {
					$forms = array();
					foreach( $fChild->xpath("vormen/vorm") as $sChild ) {
						$forms[] = $sChild;
					}

					$question_types = array();
					foreach( $fChild->xpath("vraagvormen/vraagvorm") as $sChild ) {
						$question_types[] = $sChild;
					}

					$study_resources = array();
					foreach( $fChild->xpath("leermaterialen/leermateriaal") as $sChild ) {
						$study_resources[] = $sChild;
					}

					$evas[] = array(
						'id' => (string) $fChild['objid'],
						'eva_id' => (string) $fChild['short'],
						'title' => (string) $fChild->titel,
						'written' => (string) $fChild->schriftelijk,
						'oral' => (string) $fChild->mondeling,
						'explanation' => (string) $fChild->toelichting,
						'retake_policy' => (string) $fChild->herexamen_mogelijk,
						'retake_extra' => (string) $fChild->herexamen_toelichting,
						'type' => array( ((string) $fChild->type['code']) => (string) $fChild->type->omschrijving ),
						'form' => $forms,
						'question_types' => $question_types,
						'study_resources' => $study_resources
						);
				}

				$data = array(
					'id' => (string) $course[0]['objid'],
					'course_id' => (string) $course[0]['short'],
					'title' => (string) $course[0]->titel,
					'period' => (string) $course[0]->periode,
					'studypoints' => (string) $course[0]->studiepunten,
					'duration' => (string) $course[0]->begeleidingsuren,
					'language' => array( ((string) $course[0]->onderwijstaal->code) => (string) $course[0]->onderwijstaal->tekst ),
					'level' => array( ((string) $course[0]->niveau->code) => (string) $course[0]->niveau->tekst ),
					'aims' => (string) $course[0]->doelstellingen,
					'previous_knowledge' => (string) $course[0]->begintermen,
					'teachers' => $teachers,
					'teaching_activities' => $olas,
					'evaluation_activities' => $evas
					);

			}
		}
		return $data;
	}
}
