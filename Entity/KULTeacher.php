<?php
namespace Dellaert\KULEducationAPIBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * @ORM\Entity
 * @ORM\Table(name="kulapi_teacher")
 */
class KULTeacher
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\generatedValue(strategy="AUTO")
	 * 
	 * @var integer
	 */
	protected $id;

	/**
	 * @ORM\Column(type="datetime", nullable="true")
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
	 */
	protected $name;

	/**
	 * @ORM\Column(type="string", length="255")
	 */
	protected $firstname;

	/**
	 * @ORM\Column(type="string", length="255")
	 */
	protected $lastname;

	/**
	 * @ORM\Column(type="string", length="255")
	 */
	protected $firstletter;

	/**
	 * @ORM\ManyToMany(targetEntity="KULCourse", mappedBy="kulTeachers")
	 */
	protected $kulCourses;
    
    public function __construct() {
    	$this->kulCourses = new ArrayCollection();
    }
}