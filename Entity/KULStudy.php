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
	 * @ORM\Column(type="string", length="255")
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
	 * @ORM\Column(type="string", length="255")
	 * @Assert\NotBlank()
	 */
	protected $title;
    
    public function __construct() {
    	$this->kulPrograms = new ArrayCollection();
    }
}