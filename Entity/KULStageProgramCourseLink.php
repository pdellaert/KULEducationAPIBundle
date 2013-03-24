<?php
namespace Dellaert\KULEducationAPIBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * @ORM\Entity
 * @ORM\Table(name="kulapi_stage_program_course")
 */
class KULStageProgramCourseLink
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 * 
	 * @var integer
	 */
	protected $id;
	
	/**
	 * @ORM\ManyToOne(targetEntity="KULStage",inversedBy="kulStageProgramCouseLinks")
	 */
	protected $kulStage;
	
	/**
	 * @ORM\ManyToOne(targetEntity="KULProgram",inversedBy="kulStageProgramCouseLinks")
	 */
	protected $kulProgram;
	
	/**
	 * @ORM\ManyToOne(targetEntity="KULCourse",inversedBy="kulStageProgramCouseLinks")
	 */
	protected $kulCourse;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set kulStage
     *
     * @param \Dellaert\KULEducationAPIBundle\Entity\KULStage $kulStage
     * @return KULStageProgramCourseLink
     */
    public function setKulStage(\Dellaert\KULEducationAPIBundle\Entity\KULStage $kulStage = null)
    {
        $this->kulStage = $kulStage;
    
        return $this;
    }

    /**
     * Get kulStage
     *
     * @return \Dellaert\KULEducationAPIBundle\Entity\KULStage 
     */
    public function getKulStage()
    {
        return $this->kulStage;
    }

    /**
     * Set kulProgram
     *
     * @param \Dellaert\KULEducationAPIBundle\Entity\KULProgram $kulProgram
     * @return KULStageProgramCourseLink
     */
    public function setKulProgram(\Dellaert\KULEducationAPIBundle\Entity\KULProgram $kulProgram = null)
    {
        $this->kulProgram = $kulProgram;
    
        return $this;
    }

    /**
     * Get kulProgram
     *
     * @return \Dellaert\KULEducationAPIBundle\Entity\KULProgram 
     */
    public function getKulProgram()
    {
        return $this->kulProgram;
    }

    /**
     * Set kulCourse
     *
     * @param \Dellaert\KULEducationAPIBundle\Entity\KULCourse $kulCourse
     * @return KULStageProgramCourseLink
     */
    public function setKulCourse(\Dellaert\KULEducationAPIBundle\Entity\KULCourse $kulCourse = null)
    {
        $this->kulCourse = $kulCourse;
    
        return $this;
    }

    /**
     * Get kulCourse
     *
     * @return \Dellaert\KULEducationAPIBundle\Entity\KULCourse 
     */
    public function getKulCourse()
    {
        return $this->kulCourse;
    }
}