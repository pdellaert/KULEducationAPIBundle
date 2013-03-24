<?php
namespace Dellaert\KULEducationAPIBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * @ORM\Entity
 * @ORM\Table(name="kulapi_faculty")
 */
class KULFaculty
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
	 * @ORM\ManyToOne(targetEntity="KULAcademyYear")
	 */
	protected $kulYear;

	/**
	 * @ORM\ManyToOne(targetEntity="KULLanguage")
	 */
	protected $kulLanguage;

	/**
	 * @ORM\ManyToMany(targetEntity="KULLevel", mappedBy="kulFaculties")
	 */
	protected $kulLevels;

	/**
	 * @ORM\Column(type="string", length=255)
	 * @Assert\NotBlank()
	 */
	protected $title;
    
    public function __construct() {
    	$this->kulLevels = new ArrayCollection();
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
     * @return KULFaculty
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
     * @return KULFaculty
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
     * @return KULFaculty
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
     * Set title
     *
     * @param string $title
     * @return KULFaculty
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
     * Set kulYear
     *
     * @param \Dellaert\KULEducationAPIBundle\Entity\KULAcademyYear $kulYear
     * @return KULFaculty
     */
    public function setKulYear(\Dellaert\KULEducationAPIBundle\Entity\KULAcademyYear $kulYear = null)
    {
        $this->kulYear = $kulYear;
    
        return $this;
    }

    /**
     * Get kulYear
     *
     * @return \Dellaert\KULEducationAPIBundle\Entity\KULAcademyYear 
     */
    public function getKulYear()
    {
        return $this->kulYear;
    }

    /**
     * Set kulLanguage
     *
     * @param \Dellaert\KULEducationAPIBundle\Entity\KULLanguage $kulLanguage
     * @return KULFaculty
     */
    public function setKulLanguage(\Dellaert\KULEducationAPIBundle\Entity\KULLanguage $kulLanguage = null)
    {
        $this->kulLanguage = $kulLanguage;
    
        return $this;
    }

    /**
     * Get kulLanguage
     *
     * @return \Dellaert\KULEducationAPIBundle\Entity\KULLanguage 
     */
    public function getKulLanguage()
    {
        return $this->kulLanguage;
    }

    /**
     * Add kulLevels
     *
     * @param \Dellaert\KULEducationAPIBundle\Entity\KULLevel $kulLevels
     * @return KULFaculty
     */
    public function addKulLevel(\Dellaert\KULEducationAPIBundle\Entity\KULLevel $kulLevels)
    {
        $this->kulLevels[] = $kulLevels;
    
        return $this;
    }

    /**
     * Remove kulLevels
     *
     * @param \Dellaert\KULEducationAPIBundle\Entity\KULLevel $kulLevels
     */
    public function removeKulLevel(\Dellaert\KULEducationAPIBundle\Entity\KULLevel $kulLevels)
    {
        $this->kulLevels->removeElement($kulLevels);
    }

    /**
     * Get kulLevels
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getKulLevels()
    {
        return $this->kulLevels;
    }
}