<?php
error_reporting(E_ALL & ~E_DEPRECATED);

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
			$this->Repo->getAll()
		);
	}
	
	public function testGetAllWithmax()
	{
		$this->assertEquals(
			count( $this->Repo->getAll( 1 ) ),
			1
		);

       $this->assertEquals(
            count( $this->Repo->getAll( 2 ) ),
            2
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

	public function testAllGetByCategory()
	{
		$List = $this->Repo->getAllByCategory( 'Food' );

		$this->assertTrue(
			count( $List ) > 0
		);
	}

	public function testGetAllByCategoryFail()
	{
		$List = $this->Repo->getAllByCategory( 'squash' );

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
			$this->Repo->getAll()
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
}
