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
	 */
	protected $name;

	/**
	 * @ORM\Column(type="string", length=255)
	 */
	protected $firstname;

	/**
	 * @ORM\Column(type="string", length=255)
	 */
	protected $lastname;

	/**
	 * @ORM\Column(type="string", length=255)
	 */
	protected $firstletter;

	/**
	 * @ORM\ManyToMany(targetEntity="KULCourse", mappedBy="kulTeachers")
	 */
	protected $kulCourses;
    
    public function __construct() {
    	$this->kulCourses = new ArrayCollection();
    }
    
    public function preInsert()
    {
        $this->preUpdate();
    }
    
    public function preUpdate()
    {
        $this->updatedAt = new \DateTime();
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
     * @return KULTeacher
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
     * @return KULTeacher
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
     * @return KULTeacher
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
     * Set name
     *
     * @param string $name
     * @return KULTeacher
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set firstname
     *
     * @param string $firstname
     * @return KULTeacher
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
    
        return $this;
    }

    /**
     * Get firstname
     *
     * @return string 
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set lastname
     *
     * @param string $lastname
     * @return KULTeacher
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
    
        return $this;
    }

    /**
     * Get lastname
     *
     * @return string 
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set firstletter
     *
     * @param string $firstletter
     * @return KULTeacher
     */
    public function setFirstletter($firstletter)
    {
        $this->firstletter = $firstletter;
    
        return $this;
    }

    /**
     * Get firstletter
     *
     * @return string 
     */
    public function getFirstletter()
    {
        return $this->firstletter;
    }

    /**
     * Add kulCourses
     *
     * @param \Dellaert\KULEducationAPIBundle\Entity\KULCourse $kulCourses
     * @return KULTeacher
     */
    public function addKulCourse(\Dellaert\KULEducationAPIBundle\Entity\KULCourse $kulCourses)
    {
        $this->kulCourses[] = $kulCourses;
    
        return $this;
    }

    /**
     * Remove kulCourses
     *
     * @param \Dellaert\KULEducationAPIBundle\Entity\KULCourse $kulCourses
     */
    public function removeKulCourse(\Dellaert\KULEducationAPIBundle\Entity\KULCourse $kulCourses)
    {
        $this->kulCourses->removeElement($kulCourses);
    }

    /**
     * Get kulCourses
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getKulCourses()
    {
        return $this->kulCourses;
    }
}