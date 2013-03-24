<?php
namespace Dellaert\KULEducationAPIBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * @ORM\Entity
 * @ORM\Table(name="kulapi_study")
 */
class KULStudy
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
     * @ORM\ManyToOne(targetEntity="KULFaculty")
     */
    protected $kulFaculty;
    
    /**
     * @ORM\ManyToOne(targetEntity="KULLevel")
     */
    protected $kulLevel;

    /**
     * @ORM\OneToMany(targetEntity="KULProgram", mappedBy="kulStudy")
     */
    protected $kulPrograms;

	/**
	 * @ORM\Column(type="string", length=255)
	 * @Assert\NotBlank()
	 */
	protected $title;
    
    public function __construct() {
    	$this->kulPrograms = new ArrayCollection();
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
     * @return KULStudy
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
     * @return KULStudy
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
     * @return KULStudy
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
     * @return KULStudy
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
     * Set kulFaculty
     *
     * @param \Dellaert\KULEducationAPIBundle\Entity\KULFaculty $kulFaculty
     * @return KULStudy
     */
    public function setKulFaculty(\Dellaert\KULEducationAPIBundle\Entity\KULFaculty $kulFaculty = null)
    {
        $this->kulFaculty = $kulFaculty;
    
        return $this;
    }

    /**
     * Get kulFaculty
     *
     * @return \Dellaert\KULEducationAPIBundle\Entity\KULFaculty 
     */
    public function getKulFaculty()
    {
        return $this->kulFaculty;
    }

    /**
     * Set kulLevel
     *
     * @param \Dellaert\KULEducationAPIBundle\Entity\KULLevel $kulLevel
     * @return KULStudy
     */
    public function setKulLevel(\Dellaert\KULEducationAPIBundle\Entity\KULLevel $kulLevel = null)
    {
        $this->kulLevel = $kulLevel;
    
        return $this;
    }

    /**
     * Get kulLevel
     *
     * @return \Dellaert\KULEducationAPIBundle\Entity\KULLevel 
     */
    public function getKulLevel()
    {
        return $this->kulLevel;
    }

    /**
     * Add kulPrograms
     *
     * @param \Dellaert\KULEducationAPIBundle\Entity\KULProgram $kulPrograms
     * @return KULStudy
     */
    public function addKulProgram(\Dellaert\KULEducationAPIBundle\Entity\KULProgram $kulPrograms)
    {
        $this->kulPrograms[] = $kulPrograms;
    
        return $this;
    }

    /**
     * Remove kulPrograms
     *
     * @param \Dellaert\KULEducationAPIBundle\Entity\KULProgram $kulPrograms
     */
    public function removeKulProgram(\Dellaert\KULEducationAPIBundle\Entity\KULProgram $kulPrograms)
    {
        $this->kulPrograms->removeElement($kulPrograms);
    }

    /**
     * Get kulPrograms
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getKulPrograms()
    {
        return $this->kulPrograms;
    }
}