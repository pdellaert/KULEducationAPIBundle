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

}