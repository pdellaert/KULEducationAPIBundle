<?php
namespace Dellaert\KULEducationAPIBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Dellaert\KULEducationAPIBundle\Utility\APIUtility;

class GenerateCompareCSV extends Command
{
	protected function configure()
	{
		$this
			->setName('kulapi:generate-compare-csv')
			->setDescription('Generate a CSV output of all the courses to be used for comparison with the active OpenMercury ACCO website content.')
			->addOption(
				'scid',
				null,
				InputOption::VALUE_REQUIRED,
				'Which school do you want to generate a CSV for.'
			)
			->addOption(
				'fid',
				null,
				InputOption::VALUE_REQUIRED,
				'Which faculty do you want to generate a CSV for.'
			)
			->addOption(
				'lid',
				null,
				InputOption::VALUE_REQUIRED,
				'Which level do you want to generate a CSV for.'
			)
			->addOption(
				'locale',
				null,
				InputOption::VALUE_REQUIRED,
				'What locale? nl|en'
			)
			->addOption(
				'options',
				null,
				InputOption::VALUE_NONE,
				'Do you want to have the first level options present'
			)
		;
	}

	protected function handleCoursesByGroups($data, $level, &$result, $savelevel=null) {
		if($level == 1) {
			foreach($data as $fData) {
				$result = $this->handleCoursesByGroups($fData,2,$result);
			}
		} else {
			foreach($data as $fKey => $fData) {
				if($fKey == 'courses'){
					if( $savelevel == null ) {
						$result['courses'] = $fData;
					} else {
						foreach($fData as $course) {
							$result[$savelevel][] = $course;
						}
					}
				} elseif($level == 2) {
					$this->handleCoursesByGroups($fData,3,$result,$fKey);
				} else {
					$this->handleCoursesByGroups($fData,$level+1,$result,$savelevel);
				}
			}
		}
		return $result;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		// Handling options
		$scid = $input->getOption('scid');
		$fid = $input->getOption('fid');
		$lid = $input->getOption('lid');
		$locale = $input->getOption('locale'); 
		$options = $input->getOption('options');

		// Headers
		$output->writeln('"Vaknummer";"Vak";"Hoofddocent";"Studie";"Fase";"Verplicht";"Semester"');

		$studies = APIUtility::getLiveStudiesByIdTitle($this->getApplication()->getKernel()->getContainer(),$locale,$scid,$fid,$lid);
		foreach($studies as $study) {
			$programs = APIUtility::getLiveProgramsByIdTitle($this->getApplication()->getKernel()->getContainer(),$locale,$scid,$study['id']);
			foreach($programs as $program) {
				$stages = APIUtility::getLiveStagesByIdTitle($this->getApplication()->getKernel()->getContainer(),$locale,$scid,$program['id']);
				foreach($stages as $stage) {
					switch($stage['id']) {
						case '1':
							$ftxt = 'eerste fase';
							break;
						case '2':
							$ftxt = 'tweede fase';
							break;
						case '3':
							$ftxt = 'derde fase';
							break;
						case '4':
							$ftxt = 'vierde fase';
							break;
						case '5':
							$ftxt = 'vijfde fase';
							break;
						case '0':
						default:
							$ftxt = 'geen fase';
							break;
					}
					if($options){
						$coursesInGroups = APIUtility::getLiveCoursesByGroupsInLevel($this->getApplication()->getKernel()->getContainer(),$locale,$scid,$program['id'],$stage['id'],1);
						$tmpArray = array();
						$coursesListInFirstGroup = $this->handleCoursesByGroups($coursesInGroups,1,$tmpArray);
						foreach($coursesListInFirstGroup as $group => $courses) {
							if($group == 'courses') {
								$programTxt = preg_replace('/\s+/',' ',$program['title'].'('.$program['studypoints'].' sp.)');
							} else {
								$programTxt = preg_replace('/\s+/',' ',$program['title'].'('.$group.')('.$program['studypoints'].' sp.)');
							}
							foreach($courses as $course) {
								switch($course['mandatory']) {
									case 'J':
									case 'Y':
										$mtxt = 'verplicht';
										break;
									default:
										$mtxt = 'keuze';
										break;
								}
								switch($course['period']) {
									case '1':
										$ptxt = 'eerste semester';
										break;
									case '2':
										$ptxt = 'tweede semester';
										break;
									case '3':
									default:
										$ptxt = 'jaarvak';
										break;
								}
								$teachers = $course['teachers'];
								if( count($teachers) == 0 ) {
									$output->writeln('"'.$course['course_id'].'";"'.preg_replace('/\s+/',' ',$course['title']).'";"niet toegewezen";"'.$programTxt.'";"'.$ftxt.'";"'.$mtxt.'";"'.$ptxt.'"');
								} else {
									foreach( $teachers as $teacher ) {
										$output->writeln('"'.$course['course_id'].'";"'.preg_replace('/\s+/',' ',$course['title']).'";"'.preg_replace('/\s+/',' ',$teacher['name']).'";"'.$programTxt.'";"'.$ftxt.'";"'.$mtxt.'";"'.$ptxt.'"');
									}
								}
							}
						}
					} else {
						$courses = APIUtility::getLiveCoursesInLevel($this->getApplication()->getKernel()->getContainer(),$locale,$scid,$program['id'],$stage['id'],1);
						$programTxt = preg_replace('/\s+/',' ',$program['title'].'('.$program['studypoints'].' sp.)');
						foreach($courses as $course) {
							switch($course['mandatory']) {
								case 'J':
								case 'Y':
									$mtxt = 'verplicht';
									break;
								default:
									$mtxt = 'keuze';
									break;
							}
							switch($course['period']) {
								case '1':
									$ptxt = 'eerste semester';
									break;
								case '2':
									$ptxt = 'tweede semester';
									break;
								case '3':
								default:
									$ptxt = 'jaarvak';
									break;
							}
							$teachers = $course['teachers'];
							if( count($teachers) == 0 ) {
								$output->writeln('"'.$course['course_id'].'";"'.preg_replace('/\s+/',' ',$course['title']).'";"niet toegewezen";"'.$programTxt.'";"'.$ftxt.'";"'.$mtxt.'";"'.$ptxt.'"');
							} else {
								foreach( $teachers as $teacher ) {
									$output->writeln('"'.$course['course_id'].'";"'.preg_replace('/\s+/',' ',$course['title']).'";"'.preg_replace('/\s+/',' ',$teacher['name']).'";"'.$programTxt.'";"'.$ftxt.'";"'.$mtxt.'";"'.$ptxt.'"');
								}
							}
						}
					}
				}
			}
		}
	}
}