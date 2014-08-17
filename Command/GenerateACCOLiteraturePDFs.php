<?php
namespace Dellaert\KULEducationAPIBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Dellaert\KULEducationAPIBundle\Utility\APIUtility;

class GenerateACCOLiteraturePDFs extends Command
{
	protected function configure() {
        $this
            ->setName('kulapi:generate-acco-literature-pdfs')
            ->setDescription('Generate the necessary PDFs of either an entire school or a single faculty for one or more phases')
            ->addOption(
                'scid',
                null,
                InputOption::VALUE_REQUIRED,
                'Which school do you want to generate the PDFs for?'
            )
            ->addOption(
                'fid',
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Which faculty or faculties do you want to generate the PDFs for? Can be added multiple times for multiple values.'
            )
            ->addOption(
                'lid',
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Which level or levels do you want to generate the PDFs for? Can be added multiple times for multiple values.'
            )
            ->addOption(
                'sid',
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Which stage or stages do you want to generate the PDFs for? Can be added multiple times for multiple values.'
            )
            ->addOption(
                'locale',
                null,
                InputOption::VALUE_REQUIRED,
                'What locale? nl|en'
            )
            ->addOption(
                'path',
                null,
                InputOption::VALUE_REQUIRED,
                'Where do you want to store the PDFs?'
            )
            ->addOption(
                'courses-xml',
                null,
                InputOption::VALUE_REQUIRED,
                'What course XML to use as base?'
            )
            ->addOption(
                'items-xml',
                null,
                InputOption::VALUE_REQUIRED,
                'What items XML to use as base?'
            )
            ->addOption(
                'wait-seconds',
                null,
                InputOption::VALUE_REQUIRED,
                'How many seconds does the script wait between faculties to prevent overload?',
                0
            )
            ->addOption(
                'disable-type',
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'What levels need to be skipped? Possible values: first-sublevel, sublevel, empty-course. Can be added multiple times for multiple values.'
            )
            ->addOption(
                'showhidden',
                null,
                InputOption::VALUE_NONE,
                'Do you want to show hidden sublevels?'
            )
            ->addOption(
                'debug',
                null,
                InputOption::VALUE_NONE,
                'Do you want to show debug output?'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        // Handling options
        $scid = $input->getOption('scid');
        $fids = $input->getOption('fid');
        $lids = $input->getOption('lid');
        $sids = $input->getOption('sid');
        $disable_types = $input->getOption('disable-type');
        $locale = $input->getOption('locale');
        $path = $input->getOption('path');
        $courses_xml = $input->getOption('courses-xml');
        $items_xml = $input->getOption('items-xml');
        $path = $input->getOption('path');
        $wait_seconds = $input->getOption('wait-seconds');
        $showhidden = $input->getOption('showhidden');
        $debug = $input->getOption('debug');
        $respect_no_show = !$showhidden;

        $hide_empty_courses = false;
        if( in_array('empty-course',$disable_types) ) {
            $hide_empty_courses = true;
        }

        // Base variables
        $items = array();
        $course_items = array();
        $courses = array();
        $stage_none = array('nl'=>'Geen','en'=>'No');

        // Container
        $container = $this->getApplication()->getKernel()->getContainer();

        // URL Base variables
        $url = APIUtility::getSchoolBaseURL($container,$scid);
        $language = substr($locale,0,1);
        $year = $container->getParameter('dellaert_kul_education_api.baseyear');
        $method = $container->getParameter('dellaert_kul_education_api.method');
        
        $this->debugOutput($output,$debug,'Handling items');
        // Handling Items
        if( $content = file_get_contents($items_xml) ) {
            $this->debugOutput($output,$debug,'Items XML loaded');
            $content = str_replace('xmlns=', 'ns=', $content);
            $xml = new \SimpleXMLElement($content);
            foreach( $xml->xpath('Response/Item/ItemData') as $item ) {
                $items[(string) $item->ItemNo] = array(
                    'title' => (string) $item->ProductTitle,
                    'price' => (string) $item->Price,
                    'accoprice' => (string) $item->AccoPrice
                    );
            }
        }
        unset($content);
        unset($xml);
        $this->debugOutput($output,$debug,'Loaded '.count($items).' items');

        $this->debugOutput($output,$debug,'Handling courses');
        // Handling Courses
        if( $content = file_get_contents($courses_xml) ) {
            $this->debugOutput($output,$debug,'Courses XML loaded');
            $content = str_replace('xmlns=', 'ns=', $content);
            $xml = new \SimpleXMLElement($content);
            foreach( $xml->xpath('Response/VakkenLijst/Vak') as $course ) {
                $materials = array();
                foreach( $course->EducatiefMateriaal as $material ) {
                    $materials[(string) $material->ItemNo] = $material->Verplicht;
                }
                $course_items[(string) $course->{'Vak-ID'}] = $materials;
            }
        }
        unset($content);
        unset($xml);
        $this->debugOutput($output,$debug,'Loaded '.count($course_items).' course items');

        // Main index XML
        $callUrl = $url.$year.'/opleidingen/n/'.$method.'/index.xml';
        if( $mainXml = @simplexml_load_file($callUrl, null, LIBXML_NOCDATA) ) {
            // FACULTY HANDLING
            foreach( $mainXml->xpath("data/instelling/hoofddepartement") as $faculty ) {
                $faculty_id = $faculty['id'];
                $faculty_title = $faculty->xpath("titels/titel[@taal='$language']");
                if( empty($faculty_title) ) {
                    $faculty_title = $faculty->xpath("titels/titel[@taal='".$container->getParameter('dellaert_kul_education_api.fallback_locale')."']");
                }
                $faculty_title = $faculty_title[0];

                if( !in_array($faculty_id,$fids) ) {
                    $this->debugOutput($output,$debug,'Skipping faculty: '.$faculty_id.' - '.$faculty_title);
                    continue;
                }

                $this->debugOutput($output,$debug,'Waiting '.$wait_seconds.' seconds before next faculty');
                sleep($wait_seconds);
                $this->debugOutput($output,$debug,'Parsing faculty: '.$faculty_id.' - '.$faculty_title);

                // LEVEL HANDLING
                foreach( $faculty->xpath("kwalificatie/classificatie/graad") as $level ) {
                    $level_id = $level['id'];
                    $level_title = $level->xpath("omschrijvingen/omschrijving[@taal='$language']");
                    if( empty($level_title) ) {
                        $level_title = $fChild->xpath("omschrijvingen/omschrijving[@taal='".$container->getParameter('dellaert_kul_education_api.fallback_locale')."']");
                    }
                    $level_title = $level_title[0];

                    if( !in_array($level_id,$lids) ) {
                        $this->debugOutput($output,$debug,'Skipping level: '.$level_id.' - '.$level_title);
                        continue;
                    }

                    $this->debugOutput($output,$debug,'Parsing level: '.$level_id.' - '.$level_title);

                    // STUDY HANDLING
                    foreach( $level->xpath("opleidingen/opleiding") as $study ) {
                        if( ((string) $study->titel['taal']) == $language ) {
                            $study_id = $study['id'];
                            $study_title = $study->titel;

                            $this->debugOutput($output,$debug,'Parsing study: '.$study_id.' - '.$study_title);

                            // PROGRAM HANDLING
                            $callUrl = $url.$year.'/opleidingen/'.$language.'/'.$method.'/CQ_'.$study_id.'.xml';
                            if( $studyXml = @simplexml_load_file($callUrl, null, LIBXML_NOCDATA) ) {
                                foreach( $studyXml->xpath("data/opleiding/programmas/programma") as $program ) {
                                    $program_id = $program['id'];
                                    $program_title = $program->titel;
                                    $program_studypoints = $program->studiepunten;

                                    $this->debugOutput($output,$debug,'Parsing program: '.$program_id.' - '.$program_title.' ('.$program_studypoints.')');

                                    if( !empty($program_title) ) {
                                        $output_structure = array();

                                        // STAGE HANDLING
                                        $callUrl = $url.$year.'/opleidingen/'.$language.'/'.$method.'/SC_'.$program_id.'.xml';
                                        if( $programXml = @simplexml_load_file($callUrl, null, LIBXML_NOCDATA) ) {
                                            foreach( $programXml->xpath("data/programma/fases/fase") as $stage ) {
                                                $stage_id = (int) $stage['code'];
                                                if( $locale == 'nl' ) {
                                                    $stage_title = 'Fase';
                                                } else {
                                                    $stage_title = 'Stage';
                                                }
                                                switch($stage_id) {
                                                    case '1':
                                                    case '2':
                                                    case '3':
                                                    case '4':
                                                    case '5':
                                                        $stage_title .= ' '.$stage_id;
                                                        break;
                                                    case '0':
                                                    default:
                                                        $stage_title = $stage_none[$locale].' '.$stage_title;
                                                        break;
                                                }

                                                if( !in_array($stage_id,$sids) ) {
                                                    $this->debugOutput($output,$debug,'Skipping stage: '.$stage_id.' - '.$stage_title);
                                                    continue;
                                                }

                                                $this->debugOutput($output,$debug,'Parsing stage: '.$stage_id);

                                                if( in_array('sublevel',$disable_types) ) {
                                                    // COURSES HANDLING
                                                    foreach( $programXml->xpath("//opleidingsonderdelen/opleidingsonderdeel[fases/fase[contains(.,$stage_id)]]") as $course ) {
                                                        $course_id = (string) $course['code'];
                                                        $course_title = (string) $course->titel;
                                                        $course_mandatory = (string) $course['verplicht'];
                                                        $course_period = (string) $course->aanbodperiode;
                                                        $current_course_items = array();

                                                        $this->debugOutput($output,$debug,'Found course: '.$course_id.' - '.$course_title.' - Mandatory:'.$course_mandatory);

                                                        // HANDLING ITEMS FOR COURSE
                                                        if( array_key_exists($course_id, $course_items) ) {
                                                            foreach( $course_items[$course_id] as $item_id => $mandatory ) {
                                                                $current_course_items[] = array(
                                                                    'id' => $item_id,
                                                                    'mandatory' => $mandatory,
                                                                    'title' => $items[$item_id]['title'],
                                                                    'price' => $items[$item_id]['price'],
                                                                    'accoprice' => $items[$item_id]['accoprice']
                                                                    );
                                                                $this->debugOutput($output,$debug,'Found courseitem: '.$item_id.' - '.$items[$item_id]['title'].' - '.$items[$item_id]['price'].' - '.$items[$item_id]['accoprice'].' - Mandatory: '.$mandatory);
                                                            }
                                                        }

                                                        $output_structure[] = array(
                                                            'id' => $course_id,
                                                            'title' => $course_title,
                                                            'mandatory' => $course_mandatory,
                                                            'period' => $course_period,
                                                            'items' => $current_course_items
                                                            );
                                                    }
                                                } else {
                                                    // TODO HANDLING WITH SUBLEVELS
                                                }

                                                $this->debugOutput($output,$debug,'Writing stage PDF: '.$program_title.' - '.$stage_title);
                                                // OUTPUT GENERATION
                                                $container->get('knp_snappy.pdf')->generateFromHtml(
                                                    $container->get('templating')->render(
                                                        'DellaertKULEducationAPIBundle::literature-list-stage.html.twig',
                                                        array(
                                                            'program_title'  => $program_title,
                                                            'stage_title' => $stage_title,
                                                            'output_structure' => $output_structure,
                                                            'hide_empty_courses' => $hide_empty_courses
                                                        )
                                                    ),
                                                    $path.'/'.$this->safe_filename(date('Ymd-Hi').'_'.$program_title.'_'.$stage_title).'.pdf'
                                                );
                                            }
                                        }
                                        // END OF STAGE HANDLING
                                    }
                                }
                            }
                            // END OF PROGRAM HANDLING
                        }
                    }
                    // END OF STUDY HANDLING
                }
                // END OF LEVEL HANDLING
            }
            // END OF FACULTY HANDLING
        }
    }

    protected function debugOutput($output,$debug,$msg) {
        if( $debug ) {
            $output->writeln(date('Y-m-d H:i:s').' - DEBUG: '.$msg);
        }
    }

    protected function safe_filename($filename) {
            // Convert spaces to dashes
            $filename = preg_replace('/ /', '-', $filename);
            // Remove unsafe characters
            $filename = preg_replace('/[^A-Z0-9-_]/i', '', $filename);
            // Remove duplicate dashes
            $filename = preg_replace('/-+/', '-', $filename);
            // Make lowercase
            $filename = mb_strtolower($filename);
            return $filename;
    }
}