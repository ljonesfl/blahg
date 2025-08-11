<?php

use Blahg\Repository;
use Blahg\Exception\ArticleNotFound;

class RepositoryTest extends PHPUnit\Framework\TestCase
{
	public $repo;

	protected function setUp() : void
	{
		parent::setUp();

		$this->repo = new Repository( 'example' );
	}

	public function testGetArticleBySlug()
	{
		$this->assertNotNull(
			$this->repo->getArticleBySlug( 'test-blog' )
		);
	}

	public function testFootnotes()
	{
		$html = $this->repo->getArticleBySlug( 'test-blog2' )->getBodyHtml();

		$this->assertStringContainsString(
			"footnote",
			$html
		);
	}

	public function testDescription()
	{
		$this->assertStringContainsString(
			"article",
			$this->repo->getArticleBySlug( 'test-blog' )->getDescription()
		);
	}

	public function testAuthor()
	{
		$Article = $this->repo->getArticleBySlug( 'test-blog' );
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
			$this->repo->getArticleBySlug( 'test-blog' )->getCanonicalUrl()
		);
	}

	public function testGetArticleBySlugFail()
	{
		$Found = true;

		try
		{
			$this->repo->getArticleBySlug( 'test-fail' );
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
			$this->repo->getArticles()
		);
	}
	
	public function testGetAllWithmax()
	{
		$this->assertEquals(
			count( $this->repo->getArticles( 1 ) ),
			1
		);

       $this->assertEquals(
            count( $this->repo->getArticles( 2 ) ),
            2
        );
	}

	public function testPagingWithOffset()
	{
		$allArticles = $this->repo->getArticles();
		
		if( count( $allArticles ) >= 3 )
		{
			$pagedArticles = $this->repo->getArticles( 2, 1 );
			
			$this->assertEquals( 2, count( $pagedArticles ) );
			
			$this->assertEquals(
				$allArticles[1]->getSlug(),
				$pagedArticles[0]->getSlug()
			);
			
			$this->assertEquals(
				$allArticles[2]->getSlug(),
				$pagedArticles[1]->getSlug()
			);
		}
	}
	
	public function testPagingOffsetOnly()
	{
		$allArticles = $this->repo->getArticles();
		
		if( count( $allArticles ) >= 2 )
		{
			$offsetArticles = $this->repo->getArticles( 0, 1 );
			
			$this->assertEquals(
				count( $allArticles ) - 1,
				count( $offsetArticles )
			);
			
			$this->assertEquals(
				$allArticles[1]->getSlug(),
				$offsetArticles[0]->getSlug()
			);
		}
	}
	
	public function testPagingBeyondBounds()
	{
		$allArticles = $this->repo->getArticles();
		$totalCount = count( $allArticles );
		
		$pagedArticles = $this->repo->getArticles( 10, $totalCount );
		
		$this->assertIsArray( $pagedArticles );
		$this->assertEmpty( $pagedArticles );
	}

	public function testGetArticleCount()
	{
		$articleCount = $this->repo->getArticleCount();
		$allArticles = $this->repo->getArticles();
		
		$this->assertIsInt( $articleCount );
		$this->assertEquals( count( $allArticles ), $articleCount );
		$this->assertGreaterThan( 0, $articleCount );
	}

	public function testGetPageCount()
	{
		$articleCount = $this->repo->getArticleCount();
		
		// Test with valid page sizes
		$this->repo->setPageSize( 1 );
		$pageCount = $this->repo->getPageCount();
		$this->assertEquals( $articleCount, $pageCount );
		
		$this->repo->setPageSize( 2 );
		$pageCount = $this->repo->getPageCount();
		$this->assertEquals( (int) ceil( $articleCount / 2 ), $pageCount );
		
		$this->repo->setPageSize( 10 );
		$pageCount = $this->repo->getPageCount();
		$this->assertEquals( (int) ceil( $articleCount / 10 ), $pageCount );
		
		// Test with page size larger than total articles
		$this->repo->setPageSize( $articleCount + 10 );
		$pageCount = $this->repo->getPageCount();
		$this->assertEquals( 1, $pageCount );
	}

	public function testGetArticlePage()
	{
		$allArticles = $this->repo->getArticles();
		
		// Test with page size of 2
		$this->repo->setPageSize( 2 );
		
		// Get first page
		$page1 = $this->repo->getArticlePage( 1 );
		$this->assertCount( 2, $page1 );
		$this->assertEquals( $allArticles[0]->getSlug(), $page1[0]->getSlug() );
		$this->assertEquals( $allArticles[1]->getSlug(), $page1[1]->getSlug() );
		
		// Get second page
		if( count( $allArticles ) >= 3 )
		{
			$page2 = $this->repo->getArticlePage( 2 );
			$this->assertTrue( count( $page2 ) > 0 );
			$this->assertEquals( $allArticles[2]->getSlug(), $page2[0]->getSlug() );
		}
		
		// Test invalid page numbers
		$this->assertEmpty( $this->repo->getArticlePage( 0 ) );
		$this->assertEmpty( $this->repo->getArticlePage( -1 ) );
		$this->assertEmpty( $this->repo->getArticlePage( 1000 ) );
	}

	public function testPageSizeGetterSetter()
	{
		// Test default page size
		$this->assertEquals( 10, $this->repo->getPageSize() );
		
		// Test setting page size
		$this->repo->setPageSize( 5 );
		$this->assertEquals( 5, $this->repo->getPageSize() );
		
		// Test that invalid page sizes don't change the value
		$this->repo->setPageSize( 0 );
		$this->assertEquals( 5, $this->repo->getPageSize() );
		
		$this->repo->setPageSize( -10 );
		$this->assertEquals( 5, $this->repo->getPageSize() );
	}

	public function testAllGetByTag()
	{
		$List = $this->repo->getArticlesByTag( 'broccoli' );

		$this->assertTrue(
			count( $List ) > 0
		);
	}

	public function testGetAllByTagFail()
	{
		$List = $this->repo->getArticlesByTag( 'squash' );

		$this->assertFalse(
			count( $List ) > 0
		);
	}

	public function testAllGetByCategory()
	{
		$List = $this->repo->getArticlesByCategory( 'Food' );

		$this->assertTrue(
			count( $List ) > 0
		);
	}

	public function testGetAllByCategoryFail()
	{
		$List = $this->repo->getArticlesByCategory( 'squash' );

		$this->assertFalse(
			count( $List ) > 0
		);
	}

	public function testFeed()
	{
		$Feed = $this->repo->getFeed(
			'Test',
			'Mah blagh',
			'http://me.blagh',
			'http://me.blagh/blagh',
			$this->repo->getArticles()
		);


		$this->assertStringContainsString(
			'CDATA',
			$Feed
		);
 	}

    public function testDrafts()
    {
        $this->repo = new Repository( 'example', true );

        $this->assertNotNull(
            $this->repo->getArticleBySlug( 'test-draft' )
        );
    }

    public function testNoDrafts()
    {
        $Found = true;

        try
        {
            $this->repo->getArticleBySlug( 'test-draft' );
        }
        catch( ArticleNotFound $Exception )
        {
            $Found = false;
        }

        $this->assertFalse( $Found );
    }

	 public function testGetAllByAuthor()
	 {
		 $List = $this->repo->getArticlesByAuthor( 'Lee Jones' );

		 $this->assertTrue(
			 count( $List ) > 0
		 );
	 }

	 public function testEmptyDirectory()
	 {
		 $this->repo = new Repository( 'empty' );

		 $this->assertEmpty(
			 $this->repo->getArticles()
		 );
	 }

	public function testGetAuthors()
	{
		$Authors = $this->repo->getAuthors();

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
		$Categories = $this->repo->getCategories();

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
		$Tags = $this->repo->getTags();

		$this->assertIsArray( $Tags );
		$this->assertTrue( count( $Tags ) > 0 );

		foreach( $Tags as $Tag )
		{
			$this->assertIsString( $Tag );
			$this->assertNotEmpty( $Tag );
		}
	}
}
