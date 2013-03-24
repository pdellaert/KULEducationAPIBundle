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
	 * @ORM\Column(type="string", length="255")
	 */
	protected $kulId;
	
	/**
	 * @ORM\Column(type="string", length="255")
	 * @Assert\NotBlank()
	 */
	protected $courseId;
	
	/**
	 * @ORM\Column(type="string", length="255")
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
	 * @ORM\Column(type="string", length="1")
	 */
	protected $mandatory;

	/**
	 * @ORM\ManyToMany(targetEntity="KULTeacher", inversedBy="kulCourses")
	 * @JoinTable(name="kulapi_course_teachers")
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
}