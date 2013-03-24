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
}