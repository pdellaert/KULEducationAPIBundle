<?php
namespace Dellaert\KULEducationAPIBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * @ORM\Entity
 * @ORM\Table(name="kulapi_program")
 */
class KULProgram
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
	 * @ORM\ManyToOne(targetEntity="KULStudy",inversedBy="kulPrograms")
	 */
	protected $kulStudy;

    /**
     * @ORM\OneToMany(targetEntity="KULStageProgramCourseLink", mappedBy="kulProgram")
     */
    protected $kulStageProgramCourseLinks;

	/**
	 * @ORM\Column(type="string", length=255)
	 * @Assert\NotBlank()
	 */
	protected $title;
    
    public function __construct() {
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
     * @return KULProgram
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
     * @return KULProgram
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
     * @return KULProgram
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
     * @return KULProgram
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
     * Set kulStudy
     *
     * @param \Dellaert\KULEducationAPIBundle\Entity\KULStudy $kulStudy
     * @return KULProgram
     */
    public function setKulStudy(\Dellaert\KULEducationAPIBundle\Entity\KULStudy $kulStudy = null)
    {
        $this->kulStudy = $kulStudy;
    
        return $this;
    }

    /**
     * Get kulStudy
     *
     * @return \Dellaert\KULEducationAPIBundle\Entity\KULStudy 
     */
    public function getKulStudy()
    {
        return $this->kulStudy;
    }

    /**
     * Add kulStageProgramCourseLinks
     *
     * @param \Dellaert\KULEducationAPIBundle\Entity\KULStageProgramCourseLink $kulStageProgramCourseLinks
     * @return KULProgram
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