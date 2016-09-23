<?php
/**
 * Tests for the \Maleficarum\Api\Profiler\Time class.
 */

namespace Maleficarum\Api\Test\Profiler;

class TimeTest extends \Maleficarum\Api\Test\ApiTestCase {
	/**
	 * PROVIDERS
	 */
	
	public function provideIncorrectProfileLabelNames() {
		return [
			[uniqid(), null],
		    [null, uniqid()],
		    ['test', uniqid()],
		    [uniqid(), 'test']
		];
	} 
	
	/**
	 * TESTS
	 */

	/**  METHOD: \Maleficarum\Api\Profiler\Time::getMilestoneLabels() */
	
	public function testGetmilestonelabelsEmpty() {
		$time = \Maleficarum\Ioc\Container::get('Maleficarum\Api\Profiler\Time');
		$this->assertTrue(is_array($time->getMilestoneLabels()));
		$this->assertEmpty($time->getMilestoneLabels());
	}	
	
	/**  METHOD: \Maleficarum\Api\Profiler\Time::begin() */

	/**
	 * @expectedException \RuntimeException
	 */
	public function testBeginIncorrect() {
		\Maleficarum\Ioc\Container::get('Maleficarum\Api\Profiler\Time')->begin()->begin();
	}
	
	public function testBeginCorrectWithoutTimestamp() {
		$time = \Maleficarum\Ioc\Container::get('Maleficarum\Api\Profiler\Time')->begin();
		$this->assertCount(1, $time->getMilestoneLabels());
	}
	
	public function testBeginCorrectWithTimestamp() {
		$time = \Maleficarum\Ioc\Container::get('Maleficarum\Api\Profiler\Time')->begin(1.0);
		$this->assertCount(1, $time->getMilestoneLabels());
		
		$prop = new \ReflectionProperty($time, 'data');
		$prop->setAccessible(true);
		$data = $prop->getValue($time);
		
		$this->assertTrue(array_pop($data)['timestamp'] === 1.0);
	}

	/**  METHOD: \Maleficarum\Api\Profiler\Time::end() */

	/**
	 * @expectedException \RuntimeException
	 */
	public function testEndNotStarted() {
		\Maleficarum\Ioc\Container::get('Maleficarum\Api\Profiler\Time')->end();
	}

	/**
	 * @expectedException \RuntimeException
	 */
	public function testEndAlreadyEnded() {
		\Maleficarum\Ioc\Container::get('Maleficarum\Api\Profiler\Time')->begin()->end()->end();
	}
	
	public function testEndCorrect() {
		$time = \Maleficarum\Ioc\Container::get('Maleficarum\Api\Profiler\Time')->begin()->end();
		$this->assertCount(2, $time->getMilestoneLabels());
	}
	
	/**  METHOD: \Maleficarum\Api\Profiler\Time::isComplete() */
	
	public function testIscompleteNotStarted() {
		$this->assertFalse(\Maleficarum\Ioc\Container::get('Maleficarum\Api\Profiler\Time')->isComplete());
	}
	
	public function testIscompleteInProgress() {
		$this->assertFalse(\Maleficarum\Ioc\Container::get('Maleficarum\Api\Profiler\Time')->begin()->isComplete());
	}
	
	public function testIscompleteConcluded() {
		$this->assertTrue(\Maleficarum\Ioc\Container::get('Maleficarum\Api\Profiler\Time')->begin()->end()->isComplete());
	}

	/**  METHOD: \Maleficarum\Api\Profiler\Time::addMilestone() */

	/**
	 * @expectedException \RuntimeException
	 */
	public function testAddmilestoneNotRunning() {
		\Maleficarum\Ioc\Container::get('Maleficarum\Api\Profiler\Time')->addMilestone(uniqid());
	}


	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testAddmilestoneIncorrectLabel() {
		\Maleficarum\Ioc\Container::get('Maleficarum\Api\Profiler\Time')->begin()->addMilestone(null);
	}


	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testAddmilestoneIncorrectComment() {
		\Maleficarum\Ioc\Container::get('Maleficarum\Api\Profiler\Time')->begin()->addMilestone(uniqid(), 1);
	}
	
	public function testAddMilestoneCorrect() {
		$time = \Maleficarum\Ioc\Container::get('Maleficarum\Api\Profiler\Time')->begin()->addMilestone(uniqid())->end();
		
		$milestones = $time->getMilestoneLabels();
		$this->assertCount(3, $milestones);
	}
	
	/**  METHOD: \Maleficarum\Api\Profiler\Time::getMilestone() */

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testGetmilestoneIncorrectName() {
		\Maleficarum\Ioc\Container::get('Maleficarum\Api\Profiler\Time')->getMilestone(null);
	}


	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testGetmilestoneMissing() {
		\Maleficarum\Ioc\Container::get('Maleficarum\Api\Profiler\Time')->getMilestone(uniqid());
	}
	
	public function testGetmilestoneCorrect() {
		$label = uniqid();
		$time = \Maleficarum\Ioc\Container::get('Maleficarum\Api\Profiler\Time')->begin()->addMilestone($label);
		
		$milestone = $time->getMilestone($label);
		$this->assertTrue(is_array($milestone));
		$this->assertArrayHasKey('timestamp', $milestone);
		$this->assertArrayHasKey('comment', $milestone);
	}
	
	/**  METHOD: \Maleficarum\Api\Profiler\Time::getProfile() */

	/**
	 * @expectedException \RuntimeException
	 */
	public function testGetprofileNotRunning() {
		\Maleficarum\Ioc\Container::get('Maleficarum\Api\Profiler\Time')->getProfile(uniqid(), uniqid());
	}
	
	/**
	 * @dataProvider provideIncorrectProfileLabelNames
	 * @expectedException \InvalidArgumentException
	 */
	public function testGetprofileIncorrectNames($start, $end) {
		\Maleficarum\Ioc\Container::get('Maleficarum\Api\Profiler\Time')->begin()->addMilestone('test')->getProfile($start, $end);
	}
	
	
}