<?php

use PHPUnit\Framework\TestCase;
use Blahg\Repository;

class ArticleTest extends TestCase
{
	public $Repo;

	protected function setUp() : void
	{
		parent::setUp();

		$this->Repo = new Repository( 'example' );
	}

	public function testGetTags()
	{
		$Article = $this->Repo->getArticleBySlug( 'test-blog' );
		$Tags    = $Article->getTags();

		$this->assertIsArray( $Tags );
		$this->assertCount( 2, $Tags );
		$this->assertStringContainsString( "broccoli", $Tags[0] );
		$this->assertStringContainsString( "food", $Tags[1] );
	}

	public function testGetBody()
	{
		$Article = $this->Repo->getArticleBySlug( 'test-blog' );
		$Body    = $Article->getBody();

		$this->assertIsString( $Body );
		$this->assertStringContainsString( "This is a Test Blog", $Body );
	}

	public function testArticleMissingBody()
	{
		$this->expectException( Blahg\Exception\ArticleMissingBody::class );

		// Attempt to get the body of an article that doesn't have one
		$Article = $this->Repo->getArticleBySlug( 'missing-body' );
		$Article->getBody();
	}
}
