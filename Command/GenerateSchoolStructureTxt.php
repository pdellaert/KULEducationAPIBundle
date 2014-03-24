<?php
namespace Dellaert\KULEducationAPIBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Dellaert\KULEducationAPIBundle\Utility\APIUtility;

class GenerateSchoolStructureTxt extends Command
{
	protected function configure() {
		$this
			->setName('kulapi:generate-school-structure')
			->setDescription('Generate a TXT output of all the levels (and possible sublevels) of a school')
			->addOption(
				'scid',
				null,
				InputOption::VALUE_REQUIRED,
				'Which school do you want to generate a structure for.'
			)
			->addOption(
				'locale',
				null,
				InputOption::VALUE_REQUIRED,
				'What locale? nl|en'
			)
			->addOption(
				'sublevels',
				null,
				InputOption::VALUE_NONE,
				'Do you want to have the sublevels present?'
			)
			->addOption(
				'showhidden',
				null,
				InputOption::VALUE_NONE,
				'Do you want to show hidden sublevels'
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		// Handling options
		$scid = $input->getOption('scid');
		$locale = $input->getOption('locale'); 
		$sublevels = $input->getOption('sublevels');
		$showhidden = $input->getOption('showhidden');
		$respect_no_show = !$showhidden;

		// Container
		$container = $this->getApplication()->getKernel()->getContainer();

		// XML Base variables
		$url = APIUtility::getSchoolBaseURL($container,$scid);
		$language = substr($locale,0,1);
		$year = $container->getParameter('dellaert_kul_education_api.baseyear');
		$method = $container->getParameter('dellaert_kul_education_api.method');

		// Main index XML
		$callUrl = $url.$year.'/opleidingen/n/'.$method.'/index.xml';
		if( $mainXml = simplexml_load_file($callUrl, null, LIBXML_NOCDATA) ) {
			// FACULTY HANDLING
			foreach( $mainXml->xpath("data/instelling/departement") as $faculty ) {
				$facultyTitle = $faculty->xpath("titels/titel[@taal='$language']");
				if( empty($facultyTitle) ) {
					$facultyTitle = $faculty->xpath("titels/titel[@taal='".$container->getParameter('dellaert_kul_education_api.fallback_locale')."']");
				}

				// Printing faculty
				$output->writeln($facultyTitle);

				// LEVEL HANDLING
				foreach( $faculty->xpath("classificaties/classificatie/graad") as $level ) {
					$levelTitle = $level->xpath("omschrijvingen/omschrijving[@taal='$language']");
					if( empty($levelTitle) ) {
						$levelTitle = $level->xpath("omschrijvingen/omschrijving[@taal='".$container->getParameter('dellaert_kul_education_api.fallback_locale')."']");
					}

					// Printing level
					$output->writeln('|- '.$levelTitle[0]);

					// STUDY HANDLING
					foreach( $level->xpath("diplomas/diploma[originele_titel[@taal='$language']]") as $study ) {
						$studyId = $study['objid'];
						$studyTitle = $study->originele_titel;

						// Printing study
						$output->writeln('   |- '.$studyTitle);

						// PROGRAM HANDLING
						$callUrl = $url.$year.'/opleidingen/'.$language.'/'.$method.'/CQ_'.$studyId.'.xml';
						if( $studyXml = simplexml_load_file($callUrl, null, LIBXML_NOCDATA) ) {
							foreach( $studyXml->xpath("data/opleiding/programmas/programma") as $program ){
								$programId = $program['id'];
								$programTitle = $program->titel;
								$programStudypoints = $program->studiepunten;

								if( !empty($programTitle) ) {
									// Printing program
									$output->writeln('      |- '.$programTitle.' ('.$programStudypoints.')');

									// STAGE HANDLING
									$callUrl = $url.$year.'/opleidingen/'.$language.'/'.$method.'/SC_'.$programId.'.xml';
									if( $progamXml = simplexml_load_file($callUrl, null, LIBXML_NOCDATA) ) {
										foreach( $programXml->xpath("data/programma/fases/fase") as $stage ) {
											$stageCode = $stage['code'];
											// Printing stage
											$output->writeln('         |- Fase '.$stageCode);

											if( $sublevels ) {
												$cg = $progamXml->xpath("data/programma/modulegroep[@niveau='1']");
												if( !empty($cg) && ( $respect_no_show == 0 || ($respect_no_show == 1 && $cg[0]->tonen_in_programmagids != 'False') ) ) {
													$data[(string) $cg[0]->titel] = APIUtility::parseCourseGroupInLevel($cg[0],$stageCode,$respect_no_show);
												}
												// COURSE GROUP HANDLING
												parseSublevels($data,0);
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
	}

	protected function parseSublevels($data,$level) {
		foreach( $data as $sublevelTitle=>$sublevel ) {
			if( $sublevelTitle != 'courses' ) {
				$output->writeln(str_repeat(' ', ($level*3)+12).'|- '.$sublevelTitle);
				parseSublevels($sublevel,$level+1);
			}
		}
	}
}