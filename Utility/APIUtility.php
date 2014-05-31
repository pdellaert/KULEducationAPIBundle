<?php
namespace Dellaert\KULEducationAPIBundle\Utility;

use Dellaert\KULEducationAPIBundle\Entity\School;

class APIUtility {

	public static function getSchoolBaseURL($container,$scid) {
		$repository = $container->get('doctrine')->getRepository('DellaertKULEducationAPIBundle:School');
		$school = $repository->findOneByShortname($scid);
		if( $school ) {
			return $school->getBaseURL();
		}
		return false;
	}

	public static function getLiveFacultiesByIdTitle($container,$locale,$scid) {
		// Locale
		$language = substr($locale,0,1);

		// Return value
		$data = array();

		// URL Setup
		$url = APIUtility::getSchoolBaseURL($container,$scid);
		$year = $container->getParameter('dellaert_kul_education_api.baseyear');
		$method = $container->getParameter('dellaert_kul_education_api.method');
		$callUrl = $url.$year.'/opleidingen/n/'.$method.'/index.xml'; 
		
		// Getting XML
		if( $xml = simplexml_load_file($callUrl, null, LIBXML_NOCDATA) ) {
			foreach( $xml->xpath("data/instelling/hoofddepartement") as $fChild ) {
				$title = $fChild->xpath("titels/titel[@taal='$language']");

				if( empty($title) ) {
					$title = $fChild->xpath("titels/titel[@taal='".$container->getParameter('dellaert_kul_education_api.fallback_locale')."']");
				}

				$data[] = array('id'=>(string) $fChild['id'],'title'=>(string) $title[0]);
			}
		}

		return $data;
	}


	public static function getLiveLevelsByIdTitle($container,$locale,$scid,$fid) {
		// Locale
		$language = substr($locale,0,1);

		// Return value
		$data = array();

		// URL Setup
		$url = APIUtility::getSchoolBaseURL($container,$scid);
		$year = $container->getParameter('dellaert_kul_education_api.baseyear');
		$method = $container->getParameter('dellaert_kul_education_api.method');
		$callUrl = $url.$year.'/opleidingen/n/'.$method.'/index.xml';
		
		// Getting XML
		if( $xml = simplexml_load_file($callUrl, null, LIBXML_NOCDATA) ) {
			$faculty = $xml->xpath("data/instelling/hoofddepartement[@id='$fid']");

			if( !empty($faculty) ) {
				foreach( $faculty[0]->xpath("kwalificatie/classificatie/graad") as $fChild ){
					$title = $fChild->xpath("omschrijvingen/omschrijving[@taal='$language']");

					if( empty($title) ) {
						$title = $fChild->xpath("omschrijvingen/omschrijving[@taal='".$container->getParameter('dellaert_kul_education_api.fallback_locale')."']");
					}

					$studiesInLang = $fChild->xpath("opleidingen/opleiding[titel[@taal='$language']]");
					if( !empty($studiesInLang) ) {
						$data[] = array('id'=>(string) $fChild['id'],'title'=>(string) $title[0]);
					}
				}
			}
		}

		return $data;
	}

	public static function getLiveStudiesByIdTitle($container,$locale,$scid,$fid,$lid) {
		// Locale
		$language = substr($locale,0,1);

		// Return value
		$data = array();

		// URL Setup
		$url = APIUtility::getSchoolBaseURL($container,$scid);
		$year = $container->getParameter('dellaert_kul_education_api.baseyear');
		$method = $container->getParameter('dellaert_kul_education_api.method');
		$callUrl = $url.$year.'/opleidingen/n/'.$method.'/index.xml';
		
		// Getting XML
		if( $xml = simplexml_load_file($callUrl, null, LIBXML_NOCDATA) ) {
			$faculty = $xml->xpath("data/instelling/hoofddepartement[@id='$fid']");

			if( !empty($faculty) ) {
				$level = $faculty[0]->xpath("kwalificatie/classificatie/graad[@id='$lid']");

				if( !empty($level) ) {
					foreach( $level[0]->xpath("opleidingen/opleiding") as $fChild ){
						if( ((string) $fChild->titel['taal']) == $language ) {
							$data[] = array('id'=>(string) $fChild['id'],'title'=>(string) $fChild->titel);
						}
					}
				}
			}
		}

		return $data;
	}

	public static function getLiveProgramsByIdTitle($container,$locale,$scid,$sid) {
		// Locale
		$language = substr($locale,0,1);

		// Return value
		$data = array();

		// URL Setup
		$url = APIUtility::getSchoolBaseURL($container,$scid);
		$year = $container->getParameter('dellaert_kul_education_api.baseyear');
		$method = $container->getParameter('dellaert_kul_education_api.method');
		$callUrl = $url.$year.'/opleidingen/'.$language.'/'.$method.'/CQ_'.$sid.'.xml';

		// Getting XML
		if( $xml = simplexml_load_file($callUrl, null, LIBXML_NOCDATA) ) {
			foreach( $xml->xpath("data/opleiding/programmas/programma") as $fChild ){
				$title = $fChild->titel;

				if( !empty($title) ) {
					$data[] = array('id'=>(string) $fChild['id'],'title'=>(string) $title,'studypoints'=>(string) $fChild->studiepunten);
				}
			}
		}

		return $data;
	}

	public static function getLiveStagesByIdTitle($container,$locale,$scid,$pid) {
		// Locale
		$language = substr($locale,0,1);

		// Return value
		$data = array();

		// URL Setup
		$url = APIUtility::getSchoolBaseURL($container,$scid);
		$year = $container->getParameter('dellaert_kul_education_api.baseyear');
		$method = $container->getParameter('dellaert_kul_education_api.method');
		$callUrl = $url.$year.'/opleidingen/'.$language.'/'.$method.'/SC_'.$pid.'.xml';

		// Getting XML
		if( $xml = simplexml_load_file($callUrl, null, LIBXML_NOCDATA) ) {
			foreach( $xml->xpath("data/programma/fases/fase") as $fChild ) {
				$data[] = array('id'=> (int) $fChild['code']);
			}
		}

		return $data;
	}

	public static function getLiveCoursesByGroupsInLevel($container,$locale,$scid,$pid,$phid,$respect_no_show) {
		// Locale
		$language = substr($locale,0,1);

		// Return value
		$data = array();

		// URL Setup
		$url = APIUtility::getSchoolBaseURL($container,$scid);
		$year = $container->getParameter('dellaert_kul_education_api.baseyear');
		$method = $container->getParameter('dellaert_kul_education_api.method');
		$callUrl = $url.$year.'/opleidingen/'.$language.'/'.$method.'/SC_'.$pid.'.xml';

		if( $xml = simplexml_load_file($callUrl, null, LIBXML_NOCDATA) ) {
			$cg = $xml->xpath("data/programma/modulegroep[@niveau='1']");
			if( !empty($cg) && ( $respect_no_show == 0 || ($respect_no_show == 1 && $cg[0]->tonen_in_programmagids != 'False') ) ) {
				$data[(string) $cg[0]->titel] = APIUtility::parseCourseGroupInLevel($cg[0],$phid,$respect_no_show);
			}
		}

		return $data;
	}

	public static function parseCourseGroupInLevel($cg,$phid,$respect_no_show) {
		$data = array();
		foreach( $cg->xpath("opleidingsonderdelen/opleidingsonderdeel[fases/fase[contains(.,$phid)]]") as $fChild ) {
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

			if( $fChild['verplicht'] == 'True' ) {
				$verplichtVal = 'J';
			} else {
				$verplichtVal = 'N';
			}

			$data['courses'][] = array(
				'id' => (string) $fChild['id'],
				'course_id' => (string) $fChild['code'],
				'title' => (string) $fChild->titel,
				'period' => (string) $fChild->aanbodperiode,
				'studypoints' => (string) $fChild->studiepunten,
				'mandatory' => (string) $verplichtVal,
				'original_language' => (string) $fChild->taal->code,
				'teachers' => $teachers
				);
		}

		$nextLevel = ((int) $cg['niveau'])+1;

		foreach( $cg->xpath("modulegroep[@niveau='$nextLevel']") as $fChild ) {
			if( $respect_no_show == 0 || ($respect_no_show == 1 && $fChild->tonen_in_programmagids != 'False') ) {
				$subCg = APIUtility::parseCourseGroupInLevel($fChild,$phid,$respect_no_show);
				if( !empty($subCg) ) {
					$data[(string) $fChild->titel] = $subCg;
				}
			}
		}

		return $data;
	}

	public static function getLiveCoursesInLevel($container,$locale,$scid,$pid,$phid,$respect_no_show) {

		$coursesInGroups = APIUtility::getLiveCoursesByGroupsInLevel($container,$locale,$scid,$pid,$phid,$respect_no_show);

		// Return value
		$data = APIUtility::handleCoursesInGroupsToFlatArray($coursesInGroups);
		if( !is_array($data) ) {
			$data = array();
		}

		return $data;
	}

	public static function handleCoursesInGroupsToFlatArray($data) {
		$result = array();
		foreach( $data as $fKey => $fData ) {
			if( $fKey == 'courses' ) {
				$result = array_merge($result,$fData);
			} elseif( is_array($fData) ) {
				$result = array_merge($result,APIUtility::handleCoursesInGroupsToFlatArray($fData));
			}
		}
		return $result;
	}

	public static function getLiveCourseDetails($container,$original_language,$scid,$cid) {
		// Locale
		$language = strtolower(substr($original_language,0,1));

		// Return value
		$data = array();

		// URL Setup
		$url = APIUtility::getSchoolBaseURL($container,$scid);
		$year = $container->getParameter('dellaert_kul_education_api.baseyear');
		$method = $container->getParameter('dellaert_kul_education_api.method');
		$callUrl = $url.$year.'/syllabi/'.$language.'/'.$method.'/'.$cid.strtoupper($language).'.xml';

		if( $xml = simplexml_load_file($callUrl, null, LIBXML_NOCDATA) ) {
			$course = $xml->xpath("data/opleidingsonderdeel");
			if( !empty($course) ) {
				$teachers = array();
				foreach( $course[0]->xpath("docenten/docent") as $fChild ) {
					$teachers[] = array(
						'function' => (string) $fChild['functie'],
						'personel_id' => (string) $fChild['persno'],
						'name' => (string) $fChild->naam,
						'firstname' => (string) $fChild->voornaam,
						'lastname' => (string) $fChild->familienaam,
						'firstletter' => (string) $fChild->voorletters,
						'on_who-is-who' => (string) $fChild->{'persoon_op_wie-is-wie'}
						);
				}

				// Teaching parts
				$olas = array();
				foreach( $course[0]->xpath("onderwijsleeractiviteiten/onderwijsleeractiviteit") as $fChild ) {
					$olaTeachers = array();
					foreach( $fChild->xpath("docenten/docent") as $sChild ) {
						$olaTeachers[] = array(
							'function' => (string) $sChild['functie'],
							'personel_id' => (string) $sChild['persno'],
							'name' => (string) $sChild->naam,
							'firstname' => (string) $sChild->voornaam,
							'lastname' => (string) $sChild->familienaam,
							'firstletter' => (string) $sChild->voorletters,
							'on_who-is-who' => (string) $fChild->{'persoon_op_wie-is-wie'}
							);
					}

					$olas[] = array(
						'id' => (string) $fChild['id'],
						'ola_id' => (string) $fChild['code'],
						'title' => (string) $fChild->titel,
						'period' => (string) $fChild->aanbodperiode,
						'studypoints' => (string) $fChild->studiepunten,
						'duration' => (string) $fChild->begeleidingsuren,
						'format' => (string) $fChild->werkvorm,
						'format_extra' => (string) $fChild->toelichting_werkvorm,
						'language' => array( ((string) $fChild->doceertaal->code) => ((string) $fChild->doceertaal->tekst) ),
						'language_extra' => (string) $fChild->toelichting_doceertaal,
						'content' => (string) $fChild->inhoud,
						'aims' => (string) $fChild->doelstellingen,
						'course_material' => (string) $fChild->studiemateriaal,
						'teachers' => $olaTeachers
						);
				}

				// Evaluation parts
				$evas = array();
				foreach( $course[0]->xpath("evaluatieactiviteiten/evaluatieactiviteit") as $fChild ) {
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
						'id' => (string) $fChild['id'],
						'eva_id' => (string) $fChild['code'],
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
					'id' => (string) $course[0]['id'],
					'course_id' => (string) $course[0]['code'],
					'title' => (string) $course[0]->titel,
					'period' => (string) $course[0]->aanbodperiode,
					'studypoints' => (string) $course[0]->studiepunten,
					'duration' => (string) $course[0]->begeleidingsuren,
					'language' => array( ((string) $course[0]->doceertalen[0]->code) => (string) $course[0]->doceertalen[0]->tekst ),
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
