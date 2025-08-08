<?php

use Blahg\Repository;
use Blahg\Exception\ArticleNotFound;

class RepositoryTest extends PHPUnit\Framework\TestCase
{
	public $Repo;

	protected function setUp() : void
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

	public function testFootnotes()
	{
		$html = $this->Repo->getArticleBySlug( 'test-blog2' )->getBodyHtml();

		$this->assertStringContainsString(
			"footnote",
			$html
		);
	}

	public function testDescription()
	{
		$this->assertStringContainsString(
			"article",
			$this->Repo->getArticleBySlug( 'test-blog' )->getDescription()
		);
	}

	public function testAuthor()
	{
		$Article = $this->Repo->getArticleBySlug( 'test-blog' );
		$Author  = $Article->getAuthor();

		$this->assertStringContainsString(
			"Lee Jones",
			$Author
		);
	}

	public function testCanonicalUrl()
	{
		$this->assertStringContainsString(
			"original",
			$this->Repo->getArticleBySlug( 'test-blog' )->getCanonicalUrl()
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
			$this->Repo->getArticles()
		);
	}
	
	public function testGetAllWithmax()
	{
		$this->assertEquals(
			count( $this->Repo->getArticles( 1 ) ),
			1
		);

       $this->assertEquals(
            count( $this->Repo->getArticles( 2 ) ),
            2
        );
	}

	public function testAllGetByTag()
	{
		$List = $this->Repo->getArticlesByTag( 'broccoli' );

		$this->assertTrue(
			count( $List ) > 0
		);
	}

	public function testGetAllByTagFail()
	{
		$List = $this->Repo->getArticlesByTag( 'squash' );

		$this->assertFalse(
			count( $List ) > 0
		);
	}

	public function testAllGetByCategory()
	{
		$List = $this->Repo->getArticlesByCategory( 'Food' );

		$this->assertTrue(
			count( $List ) > 0
		);
	}

	public function testGetAllByCategoryFail()
	{
		$List = $this->Repo->getArticlesByCategory( 'squash' );

		$this->assertFalse(
			count( $List ) > 0
		);
	}

	public function testFeed()
	{
		$Feed = $this->Repo->getFeed(
			'Test',
			'Mah blagh',
			'http://me.blagh',
			'http://me.blagh/blagh',
			$this->Repo->getArticles()
		);


		$this->assertStringContainsString(
			'CDATA',
			$Feed
		);
 	}

    public function testDrafts()
    {
        $this->Repo = new Repository( 'example', true );

        $this->assertNotNull(
            $this->Repo->getArticleBySlug( 'test-draft' )
        );
    }

    public function testNoDrafts()
    {
        $Found = true;

        try
        {
            $this->Repo->getArticleBySlug( 'test-draft' );
        }
        catch( ArticleNotFound $Exception )
        {
            $Found = false;
        }

        $this->assertFalse( $Found );
    }

	 public function testGetAllByAuthor()
	 {
		 $List = $this->Repo->getArticlesByAuthor( 'Lee Jones' );

		 $this->assertTrue(
			 count( $List ) > 0
		 );
	 }

	 public function testEmptyDirectory()
	 {
		 $this->Repo = new Repository( 'empty' );

		 $this->assertEmpty(
			 $this->Repo->getArticles()
		 );
	 }

	public function testGetAuthors()
	{
		$Authors = $this->Repo->getAuthors();

		$this->assertIsArray( $Authors );
		$this->assertTrue( count( $Authors ) > 0 );

		foreach( $Authors as $Author )
		{
			$this->assertIsString( $Author );
			$this->assertNotEmpty( $Author );
		}
	}

	public function testGetCategories()
	{
		$Categories = $this->Repo->getCategories();

		$this->assertIsArray( $Categories );
		$this->assertTrue( count( $Categories ) > 0 );

		foreach( $Categories as $Category )
		{
			$this->assertIsString( $Category );
			$this->assertNotEmpty( $Category );
		}
	}

	public function testGetTags()
	{
		$Tags = $this->Repo->getTags();

		$this->assertIsArray( $Tags );
		$this->assertTrue( count( $Tags ) > 0 );

		foreach( $Tags as $Tag )
		{
			$this->assertIsString( $Tag );
			$this->assertNotEmpty( $Tag );
		}
	}
}
