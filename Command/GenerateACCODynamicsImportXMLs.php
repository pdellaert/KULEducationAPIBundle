<?php
namespace Dellaert\KULEducationAPIBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Dellaert\KULEducationAPIBundle\Utility\APIUtility;

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
                'fids',
                null,
                InputOption::VALUE_REQUIRED,
                'Which faculty or faculties do you want to generate the XMLs for? (comma seperated, no spaces)',
                -1
            )
            ->addOption(
                'lids',
                null,
                InputOption::VALUE_REQUIRED,
                'Which level or levels do you want to generate the XMLs for? (comma seperated, no spaces)',
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
        $fids = explode(',',$input->getOption('fids'));
        $lids = explode(',',$input->getOption('lids'));
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
        $xml_cur_level_id = 0;

        // Container
        $container = $this->getApplication()->getKernel()->getContainer();

        // URL Base variables
        $url = APIUtility::getSchoolBaseURL($container,$scid);
        $language = substr($locale,0,1);
        $year = $container->getParameter('dellaert_kul_education_api.baseyear');
        $method = $container->getParameter('dellaert_kul_education_api.method');

        // Structure XML start
        $structure_xml = new \DOMDocument('1.0', 'UTF-8');
        $structure_xml->formatOutput = true;
        // Root element
        $structure_xml_literaturelist = $structure_xml->createElement('literatuurLijst');
        // List id element
        $structure_xml_listid = $structure_xml->createElement('literatuurLijst-ID',$listid);
        $structure_xml_literaturelist->appendChild($structure_xml_listid);

        // Main index XML
        $callUrl = $url.$year.'/opleidingen/n/'.$method.'/index.xml';
        if( $mainXml = simplexml_load_file($callUrl, null, LIBXML_NOCDATA) ) {
            // FACULTY HANDLING
            foreach( $mainXml->xpath("data/instelling/hoofddepartement") as $faculty ) {
                $faculty_id = $faculty['id'];
                $faculty_title = $faculty->xpath("titels/titel[@taal='$language']");
                if( empty($faculty_title) ) {
                    $faculty_title = $faculty->xpath("titels/titel[@taal='".$container->getParameter('dellaert_kul_education_api.fallback_locale')."']");
                }
                $faculty_title = $faculty_title[0];
                $xml_faculty_id = ++$xml_cur_level_id;

                if( $fids[0] != -1 && !in_array($faculty['id'],$fids) ) {
                    $this->debugOutput($output,$debug,'Skipping faculty: '.$faculty_id.' - '.$faculty_title);
                    continue;
                }

                $this->debugOutput($output,$debug,'Parsing faculty: '.$faculty_id.' - '.$faculty_title);

                // Structure XML Faculty Adding
                $structure_xml_level = $structure_xml->createElement('niveau');
                {
                    // XML Level ID
                    $structure_xml_level_id = $structure_xml->createElement('niveau-ID',$xml_faculty_id);
                    $structure_xml_level->appendChild($structure_xml_level_id);
                    // XML Level Parent ID
                    $structure_xml_parent_level_id = $structure_xml->createElement('niveauParent-ID');
                    $structure_xml_level->appendChild($structure_xml_parent_level_id);
                    // XML Level Title
                    $structure_xml_level_title = $structure_xml->createElement('titel');
                    {
                        $structure_xml_level_title_cdata = $structure_xml->createCDATASection($faculty_title);
                        $structure_xml_level_title->appendChild($structure_xml_level_title_cdata);
                    }
                    $structure_xml_level->appendChild($structure_xml_level_title);
                    // XML Level Mandatory
                    $structure_xml_level_mandatory = $structure_xml->createElement('verplicht');
                    $structure_xml_level->appendChild($structure_xml_level_mandatory);
                }
                $structure_xml_literaturelist->appendChild($structure_xml_level);

                // LEVEL HANDLING
                foreach( $faculty->xpath("kwalificatie/classificatie/graad") as $level ) {
                    $level_id = $level['id'];
                    $level_title = $level->xpath("omschrijvingen/omschrijving[@taal='$language']");
                    if( empty($level_title) ) {
                        $level_title = $fChild->xpath("omschrijvingen/omschrijving[@taal='".$container->getParameter('dellaert_kul_education_api.fallback_locale')."']");
                    }
                    $level_title = $level_title[0];
                    $xml_level_id = ++$xml_cur_level_id;

                    if( $lids[0] != -1 && !in_array($level['id'],$lids) ) {
                        $this->debugOutput($output,$debug,'Skipping level: '.$level_id.' - '.$level_title);
                        continue;
                    }

                    $this->debugOutput($output,$debug,'Parsing level: '.$level_id.' - '.$level_title);

                    // Structure XML Level Adding
                    $structure_xml_level = $structure_xml->createElement('niveau');
                    {
                        // XML Level ID
                        $structure_xml_level_id = $structure_xml->createElement('niveau-ID',$xml_level_id);
                        $structure_xml_level->appendChild($structure_xml_level_id);
                        // XML Level Parent ID
                        $structure_xml_parent_level_id = $structure_xml->createElement('niveauParent-ID',$xml_faculty_id);
                        $structure_xml_level->appendChild($structure_xml_parent_level_id);
                        // XML Level Title
                        $structure_xml_level_title = $structure_xml->createElement('titel');
                        {
                            $structure_xml_level_title_cdata = $structure_xml->createCDATASection($level_title);
                            $structure_xml_level_title->appendChild($structure_xml_level_title_cdata);
                        }
                        $structure_xml_level->appendChild($structure_xml_level_title);
                        // XML Level Mandatory
                        $structure_xml_level_mandatory = $structure_xml->createElement('verplicht');
                        $structure_xml_level->appendChild($structure_xml_level_mandatory);
                    }
                    $structure_xml_literaturelist->appendChild($structure_xml_level);

                    // STUDY HANDLING
                    foreach( $level->xpath("opleidingen/opleiding") as $study ) {
                        if( ((string) $study->titel['taal']) == $language ) {
                            $study_id = $study['id'];
                            $study_title = $study->titel;
                            $xml_study_id = ++$xml_cur_level_id;

                            $this->debugOutput($output,$debug,'Parsing study: '.$study_id.' - '.$study_title);

                            // Structure XML Study Adding
                            $structure_xml_level = $structure_xml->createElement('niveau');
                            {
                                // XML Level ID
                                $structure_xml_level_id = $structure_xml->createElement('niveau-ID',$xml_study_id);
                                $structure_xml_level->appendChild($structure_xml_level_id);
                                // XML Level Parent ID
                                $structure_xml_parent_level_id = $structure_xml->createElement('niveauParent-ID',$xml_level_id);
                                $structure_xml_level->appendChild($structure_xml_parent_level_id);
                                // XML Level Title
                                $structure_xml_level_title = $structure_xml->createElement('titel');
                                {
                                    $structure_xml_level_title_cdata = $structure_xml->createCDATASection($study_title);
                                    $structure_xml_level_title->appendChild($structure_xml_level_title_cdata);
                                }
                                $structure_xml_level->appendChild($structure_xml_level_title);
                                // XML Level Mandatory
                                $structure_xml_level_mandatory = $structure_xml->createElement('verplicht');
                                $structure_xml_level->appendChild($structure_xml_level_mandatory);
                            }
                            $structure_xml_literaturelist->appendChild($structure_xml_level);

                            // PROGRAM HANDLING
                            $callUrl = $url.$year.'/opleidingen/'.$language.'/'.$method.'/CQ_'.$study_id.'.xml';
                            if( $studyXml = simplexml_load_file($callUrl, null, LIBXML_NOCDATA) ) {
                                foreach( $studyXml->xpath("data/opleiding/programmas/programma") as $program ) {
                                    $program_id = $program['id'];
                                    $program_title = $program->titel;
                                    $program_studypoints = $program->studiepunten;
                                    $xml_program_id = ++$xml_cur_level_id;

                                    $this->debugOutput($output,$debug,'Parsing program: '.$program_id.' - '.$program_title.' ('.$program_studypoints.')');

                                    // Structure XML Program Adding
                                    $structure_xml_level = $structure_xml->createElement('niveau');
                                    {
                                        // XML Level ID
                                        $structure_xml_level_id = $structure_xml->createElement('niveau-ID',$xml_program_id);
                                        $structure_xml_level->appendChild($structure_xml_level_id);
                                        // XML Level Parent ID
                                        $structure_xml_parent_level_id = $structure_xml->createElement('niveauParent-ID',$xml_study_id);
                                        $structure_xml_level->appendChild($structure_xml_parent_level_id);
                                        // XML Level Title
                                        $structure_xml_level_title = $structure_xml->createElement('titel');
                                        {
                                            $structure_xml_level_title_cdata = $structure_xml->createCDATASection($program_title.' ('.$program_studypoints.')');
                                            $structure_xml_level_title->appendChild($structure_xml_level_title_cdata);
                                        }
                                        $structure_xml_level->appendChild($structure_xml_level_title);
                                        // XML Level Mandatory
                                        $structure_xml_level_mandatory = $structure_xml->createElement('verplicht');
                                        $structure_xml_level->appendChild($structure_xml_level_mandatory);
                                    }
                                    $structure_xml_literaturelist->appendChild($structure_xml_level);

                                    if( !empty($program_title) ) {
                                        // STAGE HANDLING
                                        $callUrl = $url.$year.'/opleidingen/'.$language.'/'.$method.'/SC_'.$program_id.'.xml';
                                        if( $programXml = simplexml_load_file($callUrl, null, LIBXML_NOCDATA) ) {
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
                                                $xml_stage_id = ++$xml_cur_level_id;

                                                $this->debugOutput($output,$debug,'Parsing stage: '.$stage_id);

                                                // Structure XML Stage Adding
                                                $structure_xml_level = $structure_xml->createElement('niveau');
                                                {
                                                    // XML Level ID
                                                    $structure_xml_level_id = $structure_xml->createElement('niveau-ID',$xml_stage_id);
                                                    $structure_xml_level->appendChild($structure_xml_level_id);
                                                    // XML Level Parent ID
                                                    $structure_xml_parent_level_id = $structure_xml->createElement('niveauParent-ID',$xml_program_id);
                                                    $structure_xml_level->appendChild($structure_xml_parent_level_id);
                                                    // XML Level Title
                                                    $structure_xml_level_title = $structure_xml->createElement('titel');
                                                    {
                                                        $structure_xml_level_title_cdata = $structure_xml->createCDATASection($stage_title);
                                                        $structure_xml_level_title->appendChild($structure_xml_level_title_cdata);
                                                    }
                                                    $structure_xml_level->appendChild($structure_xml_level_title);
                                                    // XML Level Mandatory
                                                    $structure_xml_level_mandatory = $structure_xml->createElement('verplicht');
                                                    $structure_xml_level->appendChild($structure_xml_level_mandatory);
                                                }
                                                $structure_xml_literaturelist->appendChild($structure_xml_level);

                                                foreach( $programXml->xpath("data/programma/modulegroep[@niveau='1']") as $course_group ) {
                                                    if( $respect_no_show == 0 || ($respect_no_show == 1 && $course_group->tonen_in_programmagids != 'False') ) {
                                                        $this->parseCourseGroup($container,$output,$debug,$course_group,$stage_id,$scid,$respect_no_show,$courses,$teachers,$structure_xml,$structure_xml_literaturelist,$xml_stage_id,$xml_cur_level_id);
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

        // GENERATING LITERATURE XML
        // Closing Root tag
        $structure_xml->appendChild($structure_xml_literaturelist);

        $this->debugOutput($output,$debug,'Finished structure XML');

        // Saving as file
        $structure_xml_result = $structure_xml->save($path.'/'.$listid.'-structure.xml');
        if( $structure_xml_result ) {
            $output->writeln('Created structure XML, with size '.$structure_xml_result.' bytes');
        } else {
            $output->writeln('Failed to create structure XML!');
        }

        // GENERATING COURSES XML
        $this->debugOutput($output,$debug,'Creating courses XML');
        $course_xml = new \DOMDocument('1.0', 'UTF-8');
        $course_xml->formatOutput = true;
        
        // Root element
        $course_xml_literaturelist = $course_xml->createElement('literatuurLijst');

        // List id element
        $course_xml_listid = $course_xml->createElement('literatuurLijst-ID',$listid);
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
                    $course_xml_course_info_cdata = $course_xml->createCDATASection(utf8_encode(substr(html_entity_decode(strip_tags($course['aims'])),0,250)));
                    $course_xml_course_info->appendChild($course_xml_course_info_cdata);
                }
                $course_xml_course->appendChild($course_xml_course_info);
                // Course Studypoints element
                $course_xml_course_studypoints = $course_xml->createElement('studiepunten',$course['studypoints']);
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
            }
            $course_xml_courses->appendChild($course_xml_course);
            // Course OLAs are just new courses with the same course_id but different module_id
            foreach( $course['teaching_activities'] as $course_ola ) {
                // Course element
                $course_xml_ola = $course_xml->createElement('vak');
                {
                    // Course ID element
                    $course_xml_ola_id = $course_xml->createElement('vak-ID',$course_id);
                    $course_xml_ola->appendChild($course_xml_ola_id);
                    // Course Module ID element
                    $course_xml_ola_module_id = $course_xml->createElement('module-ID',$course_ola['ola_id']);
                    $course_xml_ola->appendChild($course_xml_ola_module_id);
                    // Course Title element
                    $course_xml_ola_title = $course_xml->createElement('titel');
                    {
                        $course_xml_ola_title_cdata = $course_xml->createCDATASection($course_ola['title']);
                        $course_xml_ola_title->appendChild($course_xml_ola_title_cdata);
                    }
                    $course_xml_ola->appendChild($course_xml_ola_title);
                    // Course Info element
                    $course_xml_ola_info = $course_xml->createElement('info');
                    {
                        $course_xml_ola_info_cdata = $course_xml->createCDATASection(utf8_encode(substr(html_entity_decode(strip_tags($course_ola['content'])),0,250)));
                        $course_xml_ola_info->appendChild($course_xml_ola_info_cdata);
                    }
                    $course_xml_ola->appendChild($course_xml_ola_info);
                    // Course Studypoints element
                    $course_xml_ola_studypoints = $course_xml->createElement('studiepunten',$course_ola['studypoints']);
                    $course_xml_ola->appendChild($course_xml_ola_studypoints);
                    // Course Period element
                    $course_xml_ola_period = $course_xml->createElement('periode','SEM '.$course_ola['period']);
                    $course_xml_ola->appendChild($course_xml_ola_period);
                    // Course Students element
                    $course_xml_ola_students = $course_xml->createElement('studenten',0);
                    $course_xml_ola->appendChild($course_xml_ola_students);
                    // Course Teachers
                    foreach( $course_ola['teachers'] as $teacher ) {
                        $course_xml_ola_teacher = $course_xml->createElement('docent');
                        {
                            // Course Teacher ID element
                            $course_xml_ola_teacher_id = $course_xml->createElement('docent-ID',$teacher['personel_id']);
                            $course_xml_ola_teacher->appendChild($course_xml_ola_teacher_id);
                            // Course Teacher Title element
                            $course_xml_ola_teacher_title = $course_xml->createElement('titel',$teacher['function']);
                            $course_xml_ola_teacher->appendChild($course_xml_ola_teacher_title);
                        }
                        $course_xml_ola->appendChild($course_xml_ola_teacher);
                    }
                }
                $course_xml_courses->appendChild($course_xml_ola);
            }
        }
        $course_xml_literaturelist->appendChild($course_xml_courses);

        // Closing Root tag
        $course_xml->appendChild($course_xml_literaturelist);

        $this->debugOutput($output,$debug,'Finished courses XML');

        // Saving as file
        $course_xml_result = $course_xml->save($path.'/'.$listid.'-courses.xml');
        if( $course_xml_result ) {
            $output->writeln('Created courses XML, containing '.count($courses).' courses and '.count($teachers).' teachers, with size '.$course_xml_result.' bytes');
        } else {
            $output->writeln('Failed to create courses XML, containing '.count($courses).' courses and '.count($teachers).' teachers!');
        }
    }

    protected function parseCourseGroup($container, $output, $debug, $course_group, $stage_id, $scid, $respect_no_show, &$courses, &$teachers, $structure_xml, $structure_xml_literaturelist, $xml_parent_id, &$xml_cur_level_id) {
        $course_group_title = (string) $course_group->titel;
        $course_group_stages = explode(',',$course_group['fases']);
        if( in_array($stage_id,$course_group_stages) )  {
            $xml_cg_id = ++$xml_cur_level_id;

            $this->debugOutput($output,$debug,'Parsing course group: '.$course_group_title);

            // Structure XML Course Group Adding
            $structure_xml_level = $structure_xml->createElement('niveau');
            {
                // XML Level ID
                $structure_xml_level_id = $structure_xml->createElement('niveau-ID',$xml_cg_id);
                $structure_xml_level->appendChild($structure_xml_level_id);
                // XML Level Parent ID
                $structure_xml_parent_level_id = $structure_xml->createElement('niveauParent-ID',$xml_parent_id);
                $structure_xml_level->appendChild($structure_xml_parent_level_id);
                // XML Level Title
                $structure_xml_level_title = $structure_xml->createElement('titel');
                {
                    $structure_xml_level_title_cdata = $structure_xml->createCDATASection($course_group_title);
                    $structure_xml_level_title->appendChild($structure_xml_level_title_cdata);
                }
                $structure_xml_level->appendChild($structure_xml_level_title);
                // XML Level Mandatory
                $structure_xml_level_mandatory = $structure_xml->createElement('verplicht',(string) $course_group['verplicht']);
                $structure_xml_level->appendChild($structure_xml_level_mandatory);
            }

            // COURSES IN THIS LEVEL HANDLING
            foreach( $course_group->xpath("opleidingsonderdelen/opleidingsonderdeel[fases/fase[contains(.,$stage_id)]]") as $course ) {
                $course_id = (string) $course['code'];
                $this->debugOutput($output,$debug,'Checking course: '.$course_id);

                // XML Level Course Adding
                $structure_xml_course = $structure_xml->createElement('vak');
                {
                    // XML Course ID
                    $structure_xml_course_id = $structure_xml->createElement('vak-ID',$course_id);
                    $structure_xml_course->appendChild($structure_xml_course_id);
                    // XML Courese Mandatory
                    $structure_xml_course_mandatory = $structure_xml->createElement('verplicht',(string) $course['verplicht']);
                    $structure_xml_course->appendChild($structure_xml_course_mandatory);
                }
                $structure_xml_level->appendChild($structure_xml_course);

                // IF COURSE DOES NOT EXIST, ADD IT
                if( !array_key_exists($course_id, $courses) ) {
                    // GETTING COURSE DETAILS
                    $this->debugOutput($output,$debug,'Parsing course: '.$course_id);
                    $course_details = APIUtility::getLiveCourseDetails($container,$course->taal->code,$scid,$course_id);
                    // COURSE HANDLING
                    // TODO: VERPLICHT EN KOPPELEN IN BOOM
                    $courses[$course_id] = $course_details;

                    // COURSE TEACHER HANDLING
                    foreach( $course_details['teachers'] as $teacher ) {
                        $teacher_id = (string) $teacher['personel_id'];
                        $this->debugOutput($output,$debug,'Checking teacher: '.$teacher_id);

                        // IF TEACHER DOES NOT EXIST, ADD IT
                        if( !array_key_exists($teacher_id, $teachers) ) {
                            $this->debugOutput($output,$debug,'Parsing teacher: '.$teacher_id);
                            $teacher_email = '';
                            if( $teacher['on_who-is-who'] == 'True' ) {
                                $teacher_email = $this->getTeacherEmail($container,$teacher_id);
                            }

                            $teachers[$teacher_id] = array(
                                'firstname' => (string) $teacher['firstname'],
                                'lastname' => (string) $teacher['lastname'],
                                'email' => $teacher_email
                            );
                        }
                    }

                    // COURSE MODULE TEACHER HANDLING
                    foreach( $course_details['teaching_activities'] as $ola ) {
                        foreach( $ola['teachers'] as $teacher ) {
                            $teacher_id = (string) $teacher['personel_id'];
                            $this->debugOutput($output,$debug,'Checking teacher: '.$teacher_id);

                            // IF TEACHER DOES NOT EXIST, ADD IT
                            if( !array_key_exists($teacher_id, $teachers) ) {
                                $this->debugOutput($output,$debug,'Parsing teacher: '.$teacher_id);
                                $teacher_email = '';
                                if( $teacher['on_who-is-who'] == 'True' ) {
                                    $teacher_email = $this->getTeacherEmail($container,$teacher_id);
                                }

                                $teachers[$teacher_id] = array(
                                    'firstname' => (string) $teacher['firstname'],
                                    'lastname' => (string) $teacher['lastname'],
                                    'email' => $teacher_email
                                );
                            }
                        }
                    }
                }
            }
            // Structure XML Course Group Appending to list
            $structure_xml_literaturelist->appendChild($structure_xml_level);

            // HANDLING SUBLEVELS
            $next_level = ((int) $course_group['niveau'])+1;
            foreach( $course_group->xpath("modulegroep[@niveau='$next_level']") as $sub_course_group ) {
                if( $respect_no_show == 0 || ($respect_no_show == 1 && $sub_course_group->tonen_in_programmagids != 'False') ) {
                    $this->parseCourseGroup($container,$output,$debug,$sub_course_group,$stage_id,$scid,$respect_no_show,$courses,$teachers,$structure_xml,$structure_xml_literaturelist,$xml_cg_id,$xml_cur_level_id);
                }
            }
        }
    }

    protected function getTeacherEmail($container,$personel_id) {
        $baseurl = $container->getParameter('dellaert_kul_education_api.who_is_who_baseurl');
        $teacher_content = file_get_contents($baseurl.$personel_id);
        $teacher_mail_link = '';
        if( $teacher_content && preg_match_all('/document\.write\(String\.fromCharCode\((\([\(\)0-9,+]*?\))[\)]{2}/',$teacher_content,$matches) ) {
                $character_codes = explode(',',$matches[1][0]);
                foreach( $character_codes as $code ) {
                        $numbers = explode('+',trim($code,'()'));
                        $char = chr(array_sum($numbers));
                        $teacher_mail_link .= $char;
                }
        }

        if( preg_match('/\"mailto:(.*?)\"/',$teacher_mail_link,$matches) ) {
                return $matches[1];
        }
        return '';
    }

    protected function debugOutput($output,$debug,$msg) {
        if( $debug ) {
            $output->writeln(date('Y-m-d H:i:s').' - DEBUG: '.$msg);
        }
    }
}
