<?php

namespace Blahg;

use Blahg\Exception\ArticleMissingBody;
use Blahg\Exception\ArticleNotFound;
use League\CommonMark\Exception\CommonMarkException;
use Suin\RSSWriter\Channel;
use Suin\RSSWriter\Feed;
use Suin\RSSWriter\Item;
use Symfony\Component\Yaml\Yaml;

function ArticleCmp( $ArticleA, $ArticleB ): int
{
	$TimeA = strtotime( $ArticleA->getDatePublished() );
	$TimeB = strtotime( $ArticleB->getDatePublished() );

	if( $TimeA == $TimeB )
	{
		return 0;
	}
	return ( $TimeB < $TimeA ) ? -1 : 1;
}

class Repository
{
	private array $_List = [];
	private string $_Root;
	private bool $_ShowDrafts;

	/**
	 * Repository constructor.
	 * @param string $Dir
	 * @param bool $ShowDrafts
	 */
	public function __construct( string $Dir, bool $ShowDrafts = false )
	{
		$this->_Root = $Dir;

		$this->setShowDrafts( $ShowDrafts );

		$this->loadArticles();
	}

	/**
	 * @return bool
	 */
	public function getShowDrafts() : bool
	{
		return $this->_ShowDrafts;
	}

	/**
	 * @param mixed $ShowDrafts
	 * @return Repository
	 */
	public function setShowDrafts( bool $ShowDrafts ) : Repository
	{
		$this->_ShowDrafts = $ShowDrafts;
		return $this;
	}

	/**
	 * @param Article $Article
	 * @return bool
	 *
	 * Test whether an article should be visible or not.
	 */
	public function shouldDisplay( Article $Article ): bool
	{
		$Display = true;

		if( !$this->getShowDrafts() && $Article->isDraft() )
		{
			$Display = false;
		}

		if( strtotime( $Article->getDatePublished() ) > time() )
		{
			$Display = false;
		}

		return $Display;
	}

	/**
	 * @param int $Max
	 * @return array
	 */
	public function getArticles( int $Max = 0 ): array
	{
		if( $Max )
		{
			return array_slice( $this->_List, 0, $Max );
		}

		return $this->_List;
	}

	/**
	 * Loads an article from a YAML file.
	 *
	 * @param string $FileName
	 * @return Article
	 */
	protected function loadArticle( string $FileName ): Article
	{
		$File = Yaml::parseFile( $FileName );

		$Article = new Article();

		$requiredFields = [
			'title',
			'slug',
			'datePublished',
			'path'
		];

		foreach( $requiredFields as $field )
		{
			if( !isset( $File[ $field ] ) )
			{
				throw new Exception\ArticleMissingData( $field );
			}
		}
		$Article->setTitle( $File[ 'title' ] );
		$Article->setSlug( $File[ 'slug' ] );
		$Article->setDatePublished( $File[ 'datePublished' ] );
		$Article->setBodyPath( $File[ 'path' ] );

		if( isset( $File[ 'category' ] ) )
		{
			$Article->setCategory( $File[ 'category' ] );
		}

		if( isset( $File[ 'tags' ] ) )
		{
			$Article->setTags( $File[ 'tags' ] );
		}

		if( isset( $File[ 'githubFlavored' ] ) )
		{
			$Article->setGithubFlavored( $File[ 'githubFlavored' ] );
		}

		if( isset( $File[ 'description' ] ) )
		{
			$Article->setDescription( $File[ 'description' ] );
		}

		if( isset( $File[ 'draft' ] ) )
		{
			$Article->setDraft( $File[ 'draft' ] );
		}

		if( isset( $File[ 'canonicalUrl' ] ) )
		{
			$Article->setCanonicalUrl( $File[ 'canonicalUrl' ] );
		}

		if( isset( $File[ 'author' ] ) )
		{
			$Article->setAuthor( $File[ 'author' ] );
		}

		return $Article;
	}

	/**
	 * @param string $Slug
	 * @return Article
	 *
	 * @throws ArticleNotFound
	 */
	public function getArticleBySlug( string $Slug ): Article
	{
		foreach( $this->_List as $Article )
		{
			if( $Article->getSlug() == $Slug )
			{
					$Article->loadBody( $this->_Root );

				return $Article;
			}
		}

		throw new ArticleNotFound();
	}

	/**
	 * @param string $Tag
	 * @return array
	 */
	public function getArticlesByTag( string $Tag ): array
	{
		$List = [];

		foreach( $this->_List as $Article )
		{
			if( $Article->hasTag( $Tag ) )
			{
				$List[] = $Article;
			}
		}

		return $List;
	}

	/**
	 * @param string $Category
	 * @return array
	 */
	public function getArticlesByCategory( string $Category ): array
	{
		$List = [];

		foreach( $this->_List as $Article )
		{
			if( $Article->getCategory() == $Category )
			{
				$List[] = $Article;
			}
		}

		return $List;
	}

	/**
	 * Gets all articles with an author field that contains any of the specified text.
	 *
	 * @param string $Author
	 * @return array
	 */
	public function getArticlesByAuthor( string $Author ): array
	{
		$List = [];

		foreach( $this->_List as $Article )
		{
			if( strstr( $Article->getAuthor(), $Author ) )
			{
				$List[] = $Article;
			}
		}

		return $List;
	}

	/**
	 * Returns a list of all authors in the repository.
	 *
	 * @return array
	 */
	public function getAuthors(): array
	{
		$Authors = [];

		foreach( $this->_List as $Article )
		{
			if( !in_array( $Article->getAuthor(), $Authors ) )
			{
				if( $Article->getAuthor() === null || $Article->getAuthor() === '' )
				{
					continue;
				}

				$Authors[] = $Article->getAuthor();
			}
		}

		sort( $Authors );

		return $Authors;
	}

	/**
	 * Returns a list of all categories in the repository.
	 *
	 * @return array
	 */
	public function getCategories(): array
	{
		$Categories = [];

		foreach( $this->_List as $Article )
		{
			if( !in_array( $Article->getCategory(), $Categories ) )
			{
				$Categories[] = $Article->getCategory();
			}
		}

		sort( $Categories );

		return $Categories;
	}

	/**
	 * Returns a list of all tags in the repository.
	 *
	 * @return array
	 */
	public function getTags(): array
	{
		$Tags = [];

		foreach( $this->_List as $Article )
		{
			foreach( $Article->getTags() as $Tag )
			{
				if( !in_array( $Tag, $Tags ) )
				{
					$Tags[] = $Tag;
				}
			}
		}

		sort( $Tags );

		return $Tags;
	}

	/**
	 * Generates an RSS feed for the repository.
	 *
	 * @param string $Name
	 * @param string $Description
	 * @param string $Url
	 * @param string $FeedUrl
	 * @param array $Articles
	 * @return string
	 */
	public function getFeed( string $Name, string $Description, string $Url, string $FeedUrl, array $Articles ): string
	{
		error_reporting(E_ALL & ~E_DEPRECATED );

		$Feed = new Feed();

		$Channel = new Channel();

		$Channel->title( $Name )
				  ->description( $Description )
				  ->url( $Url )
				  ->feedUrl( $FeedUrl )
				  ->language( 'en-US' )
				  ->pubDate( time() )
				  ->ttl( 60 )
				  ->appendTo( $Feed );

		foreach( $Articles as $Data )
		{
			try
			{
				$Article = $this->getArticleBySlug( $Data->getSlug() );
			}
			catch( ArticleNotFound | ArticleMissingBody $e )
			{
				continue;
			}

			$Item = new Item();

			$Link = $Url . '/blahg/' . $Article->getSlug();

			try
			{
				$Item->title( $Article->getTitle() )
					  ->description( $Article->getBodyHtml() )
					  ->contentEncoded( $Article->getBodyHtml() )
					  ->url( $Link )
					  ->pubDate( strtotime( $Article->getDatePublished() ) )
					  ->guid( $Link, true )
					  ->preferCdata( true )
					  ->appendTo( $Channel );
			}
			catch( CommonMarkException $e )
			{
				continue;
			}
		}

		return $Feed->render();
	}

	/**
	 * @param array $Files
	 * @param string $Dir
	 * @return void
	 */
	protected function loadArticles(): void
	{
		$Files = @scandir( $this->_Root );

		if( !is_array( $Files ) )
		{
			return;
		}

		foreach( $Files as $File )
		{
			if( $File[ 0 ] == '.' )
			{
				continue;
			}

			if( !fnmatch( "*.yaml", $File ) )
			{
				continue;
			}

			$Path = $this->_Root . '/' . $File;

			try
			{
				$Article = $this->loadArticle( $Path );
			}
			catch( Exception\ArticleMissingData $e )
			{
				continue;
			}

			if( !$this->shouldDisplay( $Article ) )
			{
				continue;
			}

			$this->_List[] = $Article;
		}

		usort( $this->_List, 'Blahg\ArticleCmp' );
	}
}
