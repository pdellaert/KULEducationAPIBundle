<?php
namespace Dellaert\KULEducationAPIBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Dellaert\KULEducationAPIBundle\Utility\APIUtility;

class GenerateCourseMaterialsExcel extends Command
{
    protected function configure()
    {
        $this
            ->setName('kulapi:generate-course-materials-excel')
            ->setDescription('Generate an Excel output with the necessary fields for the workforce of ACCO.')
            ->addOption(
                'scid',
                null,
                InputOption::VALUE_REQUIRED,
                'Which school do you want to generate an Excel for?'
            )
            ->addOption(
                'fid',
                null,
                InputOption::VALUE_REQUIRED,
                'Which faculty do you want to generate an Excel for?'
            )
            ->addOption(
                'lid',
                null,
                InputOption::VALUE_REQUIRED,
                'Which level do you want to generate an Excel for?'
            )
            ->addOption(
                'locale',
                null,
                InputOption::VALUE_REQUIRED,
                'What locale? nl|en'
            )
            ->addOption(
                'file',
                null,
                InputOption::VALUE_REQUIRED,
                'Where do you want to store the file?'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Handling options
        $scid = $input->getOption('scid');
        $fid = $input->getOption('fid');
        $lid = $input->getOption('lid');
        $locale = $input->getOption('locale');
        $file = $input->getOption('file');

        // Container
        $container = $this->getApplication()->getKernel()->getContainer();

        // URL Base variables
        $url = APIUtility::getSchoolBaseURL($container,$scid);
        $language = substr($locale,0,1);
        $year = $container->getParameter('dellaert_kul_education_api.baseyear');
        $method = $container->getParameter('dellaert_kul_education_api.method');

        // Loading Excel library and creating an object
        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();
        $phpExcelObject->getProperties()->setCreator('ACCO cvba')
            ->setLastModifiedBy('ACCO Course material exporter')
            ->setTitle('Export for '.$scid.' - '.$fid.' - '.$lid)
            ->setSubject('Export for '.$scid.' - '.$fid.' - '.$lid)
            ->setDescription('Export of course material information for a given school, faculty and grade/level')
            ->setKeywords('course material export ACCO')
            ->setActiveSheetIndex(0);
            $phpExcelObject->getActiveSheet()->setTitle('Course-material');

        // Line counter
        $line = 1;

        // Headers
        $phpExcelObject->setCellValue('A'.$line, 'Laatste aanpassing op')
            ->setCellValue('B'.$line,'Instelling')
            ->setCellValue('C'.$line,'Opleiding')
            ->setCellValue('D'.$line,'Jaar')
            ->setCellValue('E'.$line,'Semester')
            ->setCellValue('F'.$line,'Vak')
            ->setCellValue('G'.$line,'Vaknummer')
            ->setCellValue('H'.$line,'Verplicht/Keuze')
            ->setCellValue('I'.$line,'Materiaal')
            ->setCellValue('J'.$line,'Aantal studenten')
            ->setCellValue('K'.$line,'Docent 1 voornaam')
            ->setCellValue('L'.$line,'Docent 1 naam')
            ->setCellValue('M'.$line,'Docent 1 e-mail')
            ->setCellValue('N'.$line,'Docent 1 Telefoon')
            ->s1tCellValue('O'.$line,'Docent 2 voornaam')
            ->setCellValue('P'.$line,'Docent 2 naam')
            ->setCellValue('Q'.$line,'Docent 2 e-mail')
            ->setCellValue('R'.$line,'Docent 2 Telefoon')
            ->setCellValue('S'.$line,'Docent 3 voornaam')
            ->setCellValue('T'.$line,'Docent 3 naam')
            ->setCellValue('U'.$line,'Docent 3 e-mail')
            ->setCellValue('V'.$line,'Docent 3 Telefoon');

        // Main index XML
        $callUrl = $url.$year.'/opleidingen/n/'.$method.'/index.xml';
        if( $mainXml = @simplexml_load_file($callUrl, null, LIBXML_NOCDATA) ) {
            // FACULTY HANDLING
            foreach( $mainXml->xpath("data/instelling/hoofddepartement") as $faculty ) {
                $faculty_id = $faculty['id'];
                if( $faculty_id != $fid ) {
                    continue;
                }

                // LEVEL HANDLING
                foreach( $faculty->xpath("kwalificatie/classificatie/graad") as $level ) {
                    $level_id = $level['id'];
                    if( $level_id != $fid ) {
                        continue;
                    }

                    // STUDY HANDLING
                    foreach( $level->xpath("opleidingen/opleiding") as $study ) {
                        if( ((string) $study->titel['taal']) == $language ) {
                            $study_id = $study['id'];
                            $study_title = $study->titel;

                            // PROGRAM HANDLING
                            $callUrl = $url.$year.'/opleidingen/'.$language.'/'.$method.'/CQ_'.$study_id.'.xml';
                            if( $studyXml = @simplexml_load_file($callUrl, null, LIBXML_NOCDATA) ) {
                                foreach( $studyXml->xpath("data/opleiding/programmas/programma") as $program ) {
                                    $program_id = $program['id'];
                                    $program_title = $program->titel;
                                    $program_studypoints = $program->studiepunten;

                                    if( !empty($program_title) ) {
                                        // STAGE HANDLING
                                        $callUrl = $url.$year.'/opleidingen/'.$language.'/'.$method.'/SC_'.$program_id.'.xml';
                                        if( $programXml = @simplexml_load_file($callUrl, null, LIBXML_NOCDATA) ) {
                                            foreach( $programXml->xpath("data/programma/fases/fase") as $stage ) {
                                                $stage_id = (int) $stage['code'];
                                                switch($stage_id) {
                                                    case '1':
                                                    case '2':
                                                    case '3':
                                                    case '4':
                                                    case '5':
                                                        $stage_title = $stage_id;
                                                        break;
                                                    case '0':
                                                    default:
                                                        $stage_title = 'geen';
                                                        break;
                                                }

                                                // COURSES HANDLING
                                                foreach( $programXml->xpath("//opleidingsonderdelen/opleidingsonderdeel[fases/fase[contains(.,$stage_id)]]") as $course ) {
                                                    $course_id = (string) $course['code'];
                                                    // GETTING COURSE DETAILS
                                                    $course_details = APIUtility::getLiveCourseDetails($container,$course->taal->code,$scid,$course_id);

                                                    if( !empty($course_details) ) {
                                                        // Incrementing line
                                                        ++$line;

                                                        // Handling mandatory
                                                        switch($course['mandatory']) {
                                                            case 'J':
                                                            case 'Y':
                                                                $mandatory = 'verplicht';
                                                                break;
                                                            default:
                                                                $mandatory = 'keuze';
                                                                break;
                                                        }

                                                        // Handling period
                                                        switch($course['period']) {
                                                            case '1':
                                                                $period = '1';
                                                                break;
                                                            case '2':
                                                                $period = '2';
                                                                break;
                                                            case '3':
                                                                $period = '1+2';
                                                                break;
                                                            default:
                                                                $period = '0';
                                                                break;
                                                        }

                                                        // Handling course material
                                                        $courseMaterial = '';
                                                        foreach( $courseDetails['teaching_activities'] as $teaching_activity ) {
                                                            $courseMaterial .= strip_tags($teaching_activity['course_material']).' - ';
                                                        }
                                                        $courseMaterial = substr($courseMaterial, 0, -3);

                                                        // Handling teachers
                                                        $teachers = $course['teachers'];
                                                        $doc[0]['firstname'] = 'Niet';
                                                        $doc[0]['name'] = 'Toegewezen';
                                                        $doc[0]['e-mail'] = '';
                                                        $doc[0]['phone'] = '';
                                                        $doc[1]['firstname'] = 'Niet';
                                                        $doc[1]['name'] = 'Toegewezen';
                                                        $doc[1]['e-mail'] = '';
                                                        $doc[1]['phone'] = '';
                                                        $doc[2]['firstname'] = 'Niet';
                                                        $doc[2]['name'] = 'Toegewezen';
                                                        $doc[2]['e-mail'] = '';
                                                        $doc[2]['phone'] = '';

                                                        if( is_array($teachers) && count($teachers) > 0 ) {
                                                            $doc[0]['firstname'] = preg_replace('/\s+/',' ',trim($teachers[0]['firstname']));
                                                            $doc[0]['name'] = preg_replace('/\s+/',' ',trim($teachers[0]['lastname']));
                                                        }
                                                        if( is_array($teachers) && count($teachers) > 1 ) {
                                                            $doc[1]['firstname'] = preg_replace('/\s+/',' ',trim($teachers[1]['firstname']));
                                                            $doc[1]['name'] = preg_replace('/\s+/',' ',trim($teachers[1]['lastname']));
                                                        }
                                                        if( is_array($teachers) && count($teachers) > 2 ) {
                                                            $doc[2]['firstname'] = preg_replace('/\s+/',' ',trim($teachers[2]['firstname']));
                                                            $doc[2]['name'] = preg_replace('/\s+/',' ',trim($teachers[2]['lastname']));
                                                        }

                                                        // Excel line filling
                                                        $phpExcelObject->setCellValue('A'.$line, date('d/m/Y'))
                                                            ->setCellValue('B'.$line,$scid)
                                                            ->setCellValue('C'.$line,preg_replace('/\s+/', ' ', trim($program_title.' ('.$program_studypoints.' sp.)')))
                                                            ->setCellValue('D'.$line,$stage_title)
                                                            ->setCellValue('E'.$line,$period)
                                                            ->setCellValue('F'.$line,preg_replace('/\s+/',' ',$course['title']))
                                                            ->setCellValue('G'.$line,$course['course_id'])
                                                            ->setCellValue('H'.$line,$mandatory)
                                                            ->setCellValue('I'.$line,$courseMaterial)
                                                            ->setCellValue('J'.$line,'')
                                                            ->setCellValue('K'.$line,$doc[0]['firstname'])
                                                            ->setCellValue('L'.$line,$doc[0]['name'])
                                                            ->setCellValue('M'.$line,$doc[0]['e-mail'])
                                                            ->setCellValue('N'.$line,$doc[0]['phone'])
                                                            ->s1tCellValue('O'.$line,$doc[1]['firstname'])
                                                            ->setCellValue('P'.$line,$doc[1]['name'])
                                                            ->setCellValue('Q'.$line,$doc[1]['e-mail'])
                                                            ->setCellValue('R'.$line,$doc[1]['phone'])
                                                            ->setCellValue('S'.$line,$doc[2]['firstname'])
                                                            ->setCellValue('T'.$line,$doc[2]['name'])
                                                            ->setCellValue('U'.$line,$doc[2]['e-mail'])
                                                            ->setCellValue('V'.$line,$doc[2]['phone']);
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
        }

        // Saving file
        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');
        $writer->save($file);
    }
}