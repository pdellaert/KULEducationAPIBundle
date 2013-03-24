<?php
namespace Dellaert\KULEducationAPIBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * @ORM\Entity
 * @ORM\Table(name="kulapi_course")
 */
class KULCourse
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
	 * @ORM\Column(type="datetime", nullable=true)
	 * 
	 * @var \DateTime
	 */
	protected $updatedAt;

	/**
	 * @ORM\Column(type="boolean")
	 * 
	 * @var boolean
	 */
	protected $enabled;

	/**
	 * @ORM\Column(type="string", length=255)
	 */
	protected $kulId;
	
	/**
	 * @ORM\Column(type="string", length=255)
	 * @Assert\NotBlank()
	 */
	protected $courseId;
	
	/**
	 * @ORM\Column(type="string", length=255)
	 * @Assert\NotBlank()
	 */
	protected $title;

	/**
	 * @ORM\Column(type="integer")
	 */
	protected $period;

	/**
	 * @ORM\Column(type="integer")
	 */
	protected $studypoints;

	/**
	 * @ORM\Column(type="string", length=1)
	 */
	protected $mandatory;

	/**
	 * @ORM\ManyToMany(targetEntity="KULTeacher", inversedBy="kulCourses")
	 * @ORM\JoinTable(name="kulapi_course_teachers")
	 */
	protected $kulTeachers;

    /**
     * @ORM\OneToMany(targetEntity="KULStageProgramCourseLink", mappedBy="kulCourse")
     */
    protected $kulStageProgramCourseLinks;
    
    public function __construct() {
    	$this->kulTeachers = new ArrayCollection();
    	$this->kulStageProgramCourseLinks = new ArrayCollection();
    }

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
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return KULCourse
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    
        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime 
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     * @return KULCourse
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    
        return $this;
    }

    /**
     * Get enabled
     *
     * @return boolean 
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set kulId
     *
     * @param string $kulId
     * @return KULCourse
     */
    public function setKulId($kulId)
    {
        $this->kulId = $kulId;
    
        return $this;
    }

    /**
     * Get kulId
     *
     * @return string 
     */
    public function getKulId()
    {
        return $this->kulId;
    }

    /**
     * Set courseId
     *
     * @param string $courseId
     * @return KULCourse
     */
    public function setCourseId($courseId)
    {
        $this->courseId = $courseId;
    
        return $this;
    }

    /**
     * Get courseId
     *
     * @return string 
     */
    public function getCourseId()
    {
        return $this->courseId;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return KULCourse
     */
    public function setTitle($title)
    {
        $this->title = $title;
    
        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set period
     *
     * @param integer $period
     * @return KULCourse
     */
    public function setPeriod($period)
    {
        $this->period = $period;
    
        return $this;
    }

    /**
     * Get period
     *
     * @return integer 
     */
    public function getPeriod()
    {
        return $this->period;
    }

    /**
     * Set studypoints
     *
     * @param integer $studypoints
     * @return KULCourse
     */
    public function setStudypoints($studypoints)
    {
        $this->studypoints = $studypoints;
    
        return $this;
    }

    /**
     * Get studypoints
     *
     * @return integer 
     */
    public function getStudypoints()
    {
        return $this->studypoints;
    }

    /**
     * Set mandatory
     *
     * @param string $mandatory
     * @return KULCourse
     */
    public function setMandatory($mandatory)
    {
        $this->mandatory = $mandatory;
    
        return $this;
    }

    /**
     * Get mandatory
     *
     * @return string 
     */
    public function getMandatory()
    {
        return $this->mandatory;
    }

    /**
     * Add kulTeachers
     *
     * @param \Dellaert\KULEducationAPIBundle\Entity\KULTeacher $kulTeachers
     * @return KULCourse
     */
    public function addKulTeacher(\Dellaert\KULEducationAPIBundle\Entity\KULTeacher $kulTeachers)
    {
        $this->kulTeachers[] = $kulTeachers;
    
        return $this;
    }

    /**
     * Remove kulTeachers
     *
     * @param \Dellaert\KULEducationAPIBundle\Entity\KULTeacher $kulTeachers
     */
    public function removeKulTeacher(\Dellaert\KULEducationAPIBundle\Entity\KULTeacher $kulTeachers)
    {
        $this->kulTeachers->removeElement($kulTeachers);
    }

    /**
     * Get kulTeachers
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getKulTeachers()
    {
        return $this->kulTeachers;
    }

    /**
     * Add kulStageProgramCourseLinks
     *
     * @param \Dellaert\KULEducationAPIBundle\Entity\KULStageProgramCourseLink $kulStageProgramCourseLinks
     * @return KULCourse
     */
    public function addKulStageProgramCourseLink(\Dellaert\KULEducationAPIBundle\Entity\KULStageProgramCourseLink $kulStageProgramCourseLinks)
    {
        $this->kulStageProgramCourseLinks[] = $kulStageProgramCourseLinks;
    
        return $this;
    }

    /**
     * Remove kulStageProgramCourseLinks
     *
     * @param \Dellaert\KULEducationAPIBundle\Entity\KULStageProgramCourseLink $kulStageProgramCourseLinks
     */
    public function removeKulStageProgramCourseLink(\Dellaert\KULEducationAPIBundle\Entity\KULStageProgramCourseLink $kulStageProgramCourseLinks)
    {
        $this->kulStageProgramCourseLinks->removeElement($kulStageProgramCourseLinks);
    }

    /**
     * Get kulStageProgramCourseLinks
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getKulStageProgramCourseLinks()
    {
        return $this->kulStageProgramCourseLinks;
    }
}