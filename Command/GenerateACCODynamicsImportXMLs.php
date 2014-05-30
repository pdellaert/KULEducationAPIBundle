<?php
namespace Dellaert\KULEducationAPIBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Dellaert\KULEducationAPIBundle\Utility\APIUtility;

/**
 * BIG TODO:
 * Everything concerning structure XML
 * Only Courses XML is ready for now!
 **/

class GenerateACCODynamicsImportXMLs extends Command
{
	protected function configure() {
        $this
            ->setName('kulapi:generate-acco-dynamics-import-xmls')
            ->setDescription('Generate the necessary XMLs of either an entire school or a single faculty')
            ->addOption(
                'scid',
                null,
                InputOption::VALUE_REQUIRED,
                'Which school do you want to generate the XMLs for?'
            )
            ->addOption(
                'fid',
                null,
                InputOption::VALUE_REQUIRED,
                'Which faculty do you want to generate the XMLs for?',
                -1
            )
            ->addOption(
                'listid',
                null,
                InputOption::VALUE_REQUIRED,
                'Which Dynamics literature list would you like to generate the XMLs for?'
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
                'Where do you want to store the XMLs?'
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
        $fid = $input->getOption('fid');
        $listid = $input->getOption('listid');
        $locale = $input->getOption('locale');
        $path = $input->getOption('path');
        $showhidden = $input->getOption('showhidden');
        $debug = $input->getOption('debug');
        $respect_no_show = !$showhidden;

        // Base variables
        $teachers = array();
        $courses = array();
        $stage_none = array('nl'=>'Geen','en'=>'No');

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
            foreach( $mainXml->xpath("data/instelling/hoofddepartement") as $faculty ) {
                $faculty_id = $faculty['id'];
                $faculty_title = $faculty->xpath("titels/titel[@taal='$language']")[0];
                if( empty($faculty_title) ) {
                    $faculty_title = $faculty->xpath("titels/titel[@taal='".$container->getParameter('dellaert_kul_education_api.fallback_locale')."']")[0];
                }

                if( $fid != -1 && $faculty['id'] != $fid ) {
                    $this->debugOutput($output,$debug,'Skipping faculty: '.$faculty_id.' - '.$faculty_title);
                    continue;
                }

                $this->debugOutput($output,$debug,'Parsing faculty: '.$faculty_id.' - '.$faculty_title);

                // LEVEL HANDLING
                foreach( $faculty->xpath("kwalificatie/classificatie/graad") as $level ) {
                    $level_id = $level['id'];
                    $level_title = $level->xpath("omschrijvingen/omschrijving[@taal='$language']")[0];
                    if( empty($level_title) ) {
                        $level_title = $fChild->xpath("omschrijvingen/omschrijving[@taal='".$container->getParameter('dellaert_kul_education_api.fallback_locale')."']")[0];
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
                            if( $studyXml = simplexml_load_file($callUrl, null, LIBXML_NOCDATA) ) {
                                foreach( $studyXml->xpath("data/opleiding/programmas/programma") as $program ) {
                                    $program_id = $program['id'];
                                    $program_title = $program->titel;
                                    $program_studypoints = $program->studiepunten;

                                    $this->debugOutput($output,$debug,'Parsing program: '.$program_id.' - '.$program_title.' ('.$program_studypoints.')');

                                    if( !empty($programTitle) ) {
                                        // STAGE HANDLING
                                        $callUrl = $url.$year.'/opleidingen/'.$language.'/'.$method.'/SC_'.$program_id.'.xml';
                                        if( $programXml = simplexml_load_file($callUrl, null, LIBXML_NOCDATA) ) {
                                            foreach( $programXml->xpath("data/programma/fases/fase") as $stage ) {
                                                $stage_id = $stage['id'];
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

                                               $this->debugOutput($output,$debug,'Parsing stage: '.$stage_id);

                                                //TODO: SUBLEVELS EN VAKKEN VANAF HIER!!!!
                                                foreach( $programXml->xpath("data/programma/modulegroep[@niveau='1']") as $course_group ) {
                                                    if( $respect_no_show == 0 || ($respect_no_show == 1 && $course_group->tonen_in_programmagids != 'False') ) {
                                                        $this->parseCourseGroup($container,$output,$debug,$course_group,$stage_id,$scid,$courses,$teachers);
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

        // GENERATING COURSES XML
        $this->debugOutput($output,$debug,'Creating courses XML');
        $course_xml = new \DOMDocument();
        $course_xml->formatOutput = true;
        
        // Root element
        $course_xml_literaturelist = $course_xml->createElement('literatuurlijst');

        // List id element
        $course_xml_listid = $course_xml->createElement('literatuurlijst-ID',$listid);
        $course_xml_literaturelist->appendChild($course_xml_listid);

        // Teachers element
        $course_xml_teachers = $course_xml->createElement('docenten');
        ksort($teachers);
        foreach( $teachers as $teacher_id => $teacher ) {
            // Teacher element
            $course_xml_teacher = $course_xml->createElement('docent');
            {
                // Teacher ID element
                $course_xml_teacher_id = $course_xml->createElement('docent-ID',$teacher_id);
                $course_xml_teacher->appendChild($course_xml_teacher_id);
                // Teacher firstname element
                $course_xml_teacher_firstname = $course_xml->createElement('voornaam');
                {
                    $course_xml_teacher_firstname_cdata = $course_xml->createCDATASection($teacher['firstname']);
                    $course_xml_teacher_firstname->appendChild($course_xml_teacher_firstname_cdata);
                }
                $course_xml_teacher->appendChild($course_xml_teacher_firstname);
                // Teacher lastname element
                $course_xml_teacher_lastname = $course_xml->createElement('familienaam');
                {
                    $course_xml_teacher_lastname_cdata = $course_xml->createCDATASection($teacher['lastname']);
                    $course_xml_teacher_lastname->appendChild($course_xml_teacher_lastname_cdata);
                }
                $course_xml_teacher->appendChild($course_xml_teacher_lastname);
                // Teacher email element
                $course_xml_teacher_email = $course_xml->createElement('email');
                {
                    $course_xml_teacher_email_cdata = $course_xml->createCDATASection($teacher['email']);
                    $course_xml_teacher_email->appendChild($course_xml_teacher_email_cdata);
                }
                $course_xml_teacher->appendChild($course_xml_teacher_email);
            }
            $course_xml_teachers->appendChild($course_xml_teacher);
        }
        $course_xml_literaturelist->appendChild($course_xml_teachers);

        // Courses element
        $course_xml_courses = $course_xml->createElement('vakken');
        ksort($courses);
        foreach( $courses as $course_id => $course ) {
            // Course element
            $course_xml_course = $course_xml->createElement('vak');
            {
                // Course ID element
                $course_xml_course_id = $course_xml->createElement('vak-ID',$course_id);
                $course_xml_course->appendChild($course_xml_course_id);
                // Course Module ID element
                $course_xml_course_module_id = $course_xml->createElement('module-ID');
                $course_xml_course->appendChild($course_xml_course_module_id);
                // Course Title element
                $course_xml_course_title = $course_xml->createElement('titel');
                {
                    $course_xml_course_title_cdata = $course_xml->createCDATASection($course['title']);
                    $course_xml_course_title->appendChild($course_xml_course_title_cdata);
                }
                $course_xml_course->appendChild($course_xml_course_title);
                // Course Info element
                $course_xml_course_info = $course_xml->createElement('info');
                {
                    $course_xml_course_info_cdata = $course_xml->createCDATASection(substr($course['aims'],0,250));
                    $course_xml_course_info->appendChild($course_xml_course_info_cdata);
                }
                $course_xml_course->appendChild($course_xml_course_info);
                // Course Studypoints element
                $course_xml_course_studypoints = $course_xml->createElement('studiepunten',$course['studiepunten']);
                $course_xml_course->appendChild($course_xml_course_studypoints);
                // Course Period element
                $course_xml_course_period = $course_xml->createElement('periode','SEM '.$course['period']);
                $course_xml_course->appendChild($course_xml_course_period);
                // Course Students element
                $course_xml_course_students = $course_xml->createElement('studenten',0);
                $course_xml_course->appendChild($course_xml_course_students);
                // Course Teachers
                foreach( $course['teachers'] as $teacher ) {
                    $course_xml_course_teacher = $course_xml->createElement('docent');
                    {
                        // Course Teacher ID element
                        $course_xml_course_teacher_id = $course_xml->createElement('docent-ID',$teacher['personel_id']);
                        $course_xml_course_teacher->appendChild($course_xml_course_teacher_id);
                        // Course Teacher Title element
                        $course_xml_course_teacher_title = $course_xml->createElement('titel',$teacher['function']);
                        $course_xml_course_teacher->appendChild($course_xml_course_teacher_title);
                    }
                    $course_xml_course->appendChild($course_xml_course_teacher);
                }
                /**
                 * BIG TODO: 
                 * OLAS HANDLING
                 **/
            }
            $course_xml_courses->appendChild($course_xml_course);
        }
        $course_xml_literaturelist->appendChild($course_xml_courses);

        // Closing Root tag
        $course_xml->appendChild($course_xml_literaturelist);

        // Saving as file
        $course_xml_result = $course_xml->save($path.'/'.$listid.'-courses.xml');
        if( $course_xml_result ) {
            $output->writeln('Created courses XML, containing '.count($courses).' courses and '.count($teachers).' teachers, with size '.$course_xml_result.' bytes');
        } else {
            $output->writeln('Failed to create courses XML, containing '.count($courses).' courses and '.count($teachers).' teachers!');
        }
    }

    protected function parseCourseGroup($container, $output, $debug, $course_group, $stage_id, $scid, $courses, $teachers) {
        $course_group_title = $course_group->titel;
        $this->debugOutput($output,$debug,'Parsing course group: '.$course_group_title);

        // COURSES IN THIS LEVEL HANDLING
        foreach( $course_group->xpath("opleidingsonderdelen/opleidingsonderdeel[fases/fase[contains(.,$phid)]]") as $course ) {
            $course_id = $course['code'];
            $this->debugOutput($output,$debug,'Checking course: '.$course_id);

            // IF COURSE DOES NOT EXIST, ADD IT
            if( !array_key_exists($course_id, $courses) ) {
                // GETTING COURSE DETAILS
                $this->debugOutput($output,$debug,'Parsing course: '.$course_id);
                $course_details = APIUtility::getLiveCourseDetails($container,$course->taal->code,$scid,$course_id);
                // COURSE HANDLING
                // TODO: VERPLICHT EN KOPPELEN IN BOOM
                $courses[$course_id] = $course_details;

                // TEACHER HANDLING
                foreach( $course_details['teachers'] as $teacher ) {
                    $teacher_id = $teacher['persno'];
                    $course_array['teachers'][] = array(
                        'function' => (string) $teacher['functie'],
                        'teacher_id' => $teacher_id
                    );

                    // IF TEACHER DOES NOT EXIST, ADD IT
                    if( !array_key_exists($teacher_id, $teachers) ) {
                        $teachers[$teacher_id] = array(
                            'firstname' => (string) $teacher->voornaam,
                            'lastname' => (string) $teacher->familienaam,
                            'email' => ''
                        );
                    }
                }
            }
        }

        // HANDLING SUBLEVELS
        $next_level = ((int) $course_group['niveau'])+1;
        foreach( $course_group->xpath("modulegroep[@niveau='$nextLevel']") as $sub_course_group ) {
            if( $respect_no_show == 0 || ($respect_no_show == 1 && $sub_course_group->tonen_in_programmagids != 'False') ) {
                $this->parseCourseGroup($container,$output,$debug,$sub_course_group,$stage_id,$scid,$courses,$teachers);
            }
        }
    }

    protected function debugOutput($output,$debug,$msg) {
        if( $debug ) {
            $output->writeln(date('Y-m-d H:i:s').' - DEBUG: '.$msg);
        }
    }
}
