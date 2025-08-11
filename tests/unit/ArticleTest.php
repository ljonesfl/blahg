<?php

use PHPUnit\Framework\TestCase;
use Blahg\Repository;

class ArticleTest extends TestCase
{
	public $repo;

	protected function setUp() : void
	{
		parent::setUp();

		$this->repo = new Repository( 'example' );
	}

	public function testGetTags()
	{
		$article = $this->repo->getArticleBySlug( 'test-blog' );
		$tags    = $article->getTags();

		$this->assertIsArray( $tags );
		$this->assertCount( 2, $tags );
		$this->assertStringContainsString( "broccoli", $tags[0] );
		$this->assertStringContainsString( "food", $tags[1] );
	}

	public function testGetBody()
	{
		$article = $this->repo->getArticleBySlug( 'test-blog' );
		$Body    = $article->getBody();

		$this->assertIsString( $Body );
		$this->assertStringContainsString( "This is a Test Blog", $Body );
	}

	public function testArticleMissingBody()
	{
		$this->expectException( Blahg\Exception\ArticleMissingBody::class );

		// Attempt to get the body of an article that doesn't have one
		$article = $this->repo->getArticleBySlug( 'missing-body' );
		$article->getBody();
	}
}
