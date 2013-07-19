<?php
namespace Dellaert\KULEducationAPIBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Dellaert\KULEducationAPIBundle\Utility\APIUtility;

class GenerateCSV extends Command
{
	protected function configure()
	{
		$this
			->setName('kulapi:generate-csv')
			->setDescription('Generate a CSV output of all the courses.')
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
		$output->writeln('"Vaknummer";"Vak";"Hoofddocent";"Studie";"Fase";"Verplicht";"Semester"');

		$studies = APIUtility::getLiveStudiesByIdTitle($this->container,$locale,$fid,$lid);
		foreach($studies => $study) {
			$output->writeln('"vn";"v";"d";"'.$study['title'].'";"f";"m";"s"');
		}
	}
}