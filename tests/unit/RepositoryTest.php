<?php

use Blahg\Repository;
use Blahg\Exception\ArticleNotFound;

class RepositoryTest extends PHPUnit\Framework\TestCase
{
	public $Repo;

	protected function setUp()
	{
		parent::setUp();

		$this->Repo = new Repository( 'example' );
	}

	public function testGetArticleBySlug()
	{
		$this->assertNotNull(
			$this->Repo->getArticleBySlug( 'test-blog' )
		);
	}

	public function testGetArticleBySlugFail()
	{
		$Found = true;

		try
		{
			$this->Repo->getArticleBySlug( 'test-fail' );
		}
		catch( ArticleNotFound $Exception )
		{
			$Found = false;
		}

		$this->assertFalse( $Found );
	}

	public function testGetList()
	{
		$this->assertIsArray(
			$this->Repo->getAll()
		);
	}

	public function testAllGetByTag()
	{
		$List = $this->Repo->getAllByTag( 'broccoli' );

		$this->assertTrue(
			count( $List ) > 0
		);
	}

	public function testGetAllByTagFail()
	{
		$List = $this->Repo->getAllByTag( 'squash' );

		$this->assertFalse(
			count( $List ) > 0
		);
	}

}
