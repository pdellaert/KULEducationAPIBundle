<?php
namespace Dellaert\KULEducationAPIBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Dellaert\KULEducationAPIBundle\Utility\APIUtility;

class GenerateCourseMaterialsCSV extends Command
{
	protected function configure()
	{
		$this
			->setName('kulapi:generate-course-materials-csv')
			->setDescription('Generate a CSV output of all the courses to be used for comparison with the active OpenMercury ACCO website content.')
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
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		// Handling options
		$fid = $input->getOption('fid');
		$lid = $input->getOption('lid');
		$locale = $input->getOption('locale'); 

		// Headers
		$output->write("\xEF\xBB\xBF");
		$output->writeln('"Laatste aanpassing op";"Instelling";"Opleiding";"Jaar";"Semester";"Vak";"Vaknummer";"Verplicht/Keuze";"Materiaal";"Aantal studenten";"Docent 1 voornaam";"Docent 1 naam";"Docent 1 e-mail";"Docent 1 Telefoon";"Docent 2 voornaam";"Docent 2 naam";"Docent 2 e-mail";"Docent 2 Telefoon";"Docent 3 voornaam";"Docent 3 naam";"Docent 3 e-mail";"Docent 3 Telefoon"');

		$studies = APIUtility::getLiveStudiesByIdTitle($this->getApplication()->getKernel()->getContainer(),$locale,$fid,$lid);
		foreach($studies as $study) {
			$programs = APIUtility::getLiveProgramsByIdTitle($this->getApplication()->getKernel()->getContainer(),$locale,$study['id']);
			foreach($programs as $program) {
				$stages = APIUtility::getLiveStagesByIdTitle($this->getApplication()->getKernel()->getContainer(),$locale,$program['id']);
				foreach($stages as $stage) {
					switch($stage['id']) {
						case '1':
							$ftxt = '1';
							break;
						case '2':
							$ftxt = '2';
							break;
						case '3':
							$ftxt = '3';
							break;
						case '4':
							$ftxt = '4';
							break;
						case '5':
							$ftxt = '5';
							break;
						case '0':
						default:
							$ftxt = 'geen';
							break;
					}
					$courses = APIUtility::getLiveCoursesInLevel($this->getApplication()->getKernel()->getContainer(),$locale,$program['id'],$stage['id'],1);
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
								$ptxt = '1';
								break;
							case '2':
								$ptxt = '2';
								break;
							case '3':
							default:
								$ptxt = '1+2';
								break;
						}

						$courseDetails = APIUtility::getLiveCourseDetails($this->getApplication()->getKernel()->getContainer(),$course['original_language'],$course['course_id']);
						$courseMaterial = '';
						foreach( $courseDetails['teaching_activities'] as $teaching_activity ) {
							$courseMaterial .= strip_tags($teaching_activity['course_material']).' - ';
						}
						$courseMaterial = substr($courseMaterial, 0, -3);

						//'"Laatste aanpassing op";"Instelling";"Opleiding";"Jaar";"Semester";"Vak";"Vaknummer";"Verplicht/Keuze";"Materiaal";"Aantal studenten";"Docent 1 voornaam";"Docent 1 naam";"Docent 1 e-mail";"Docent 1 Telefoon";"Docent 2 voornaam";"Docent 2 naam";"Docent 2 e-mail";"Docent 2 Telefoon";"Docent 3 voornaam";"Docent 3 naam";"Docent 3 e-mail";"Docent 3 Telefoon"');
						$courseLine = '"'.date("d/m/Y").'";"KUL";"'.$programTxt.'";"'.$ftxt.'";"'.$ptxt.'";"'.preg_replace('/\s+/',' ',$course['title']).'";"'.$course['course_id'].'";"'.$mtxt.'";"'.$courseMaterial.'";"";';

						$teachers = $course['teachers'];
						switch(count($teachers)) {
							case 0:
								$courseLine .= '"Niet";"Toegewezen";"";"";"";"";"";"";"";"";"";""';
								break;
							case 1:
								$courseLine .= '"'.preg_replace('/\s+/',' ',$teachers[0]['firstname']).'";"'.preg_replace('/\s+/',' ',$teachers[0]['lastname']).'";"";"";"";"";"";"";"";"";"";""';
								break;
							case 2:
								$courseLine .= '"'.preg_replace('/\s+/',' ',$teachers[0]['firstname']).'";"'.preg_replace('/\s+/',' ',$teachers[0]['lastname']).'";"";"";"'.preg_replace('/\s+/',' ',$teachers[1]['firstname']).'";"'.preg_replace('/\s+/',' ',$teachers[1]['lastname']).'";"";"";"";"";"";""';
								break;
							case 3:
							default:
								$courseLine .= '"'.preg_replace('/\s+/',' ',$teachers[0]['firstname']).'";"'.preg_replace('/\s+/',' ',$teachers[0]['lastname']).'";"";"";"'.preg_replace('/\s+/',' ',$teachers[1]['firstname']).'";"'.preg_replace('/\s+/',' ',$teachers[1]['lastname']).'";"";"";"'.preg_replace('/\s+/',' ',$teachers[2]['firstname']).'";"'.preg_replace('/\s+/',' ',$teachers[2]['lastname']).'";"";""';
								break;
						}


						$output->writeln($courseLine);
					}
				}
			}
		}
	}
}