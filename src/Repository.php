<?php

namespace Blahg;

use Blahg\Exception\ArticleMissingBody;
use Blahg\Exception\ArticleNotFound;
use League\CommonMark\Exception\CommonMarkException;
use Suin\RSSWriter\Channel;
use Suin\RSSWriter\Feed;
use Suin\RSSWriter\Item;
use Symfony\Component\Yaml\Yaml;

function ArticleCmp( $articleA, $articleB ): int
{
	$timeA = strtotime( $articleA->getDatePublished() );
	$timeB = strtotime( $articleB->getDatePublished() );

	if( $timeA == $timeB )
	{
		return 0;
	}
	return ( $timeB < $timeA ) ? -1 : 1;
}

class Repository implements IRepository
{
	private array $_list = [];
	private string $_root;
	private bool $_showDrafts;
	private int $_pageSize = 10;

	/**
	 * Repository constructor.
	 * @param string $dir
	 * @param bool $showDrafts
	 */
	public function __construct( string $dir, bool $showDrafts = false )
	{
		$this->_root = $dir;

		$this->setShowDrafts( $showDrafts );

		$this->loadArticles();
	}

	/**
	 * @return bool
	 */
	public function getShowDrafts() : bool
	{
		return $this->_showDrafts;
	}

	/**
	 * @param mixed $showDrafts
	 * @return IRepository
	 */
	public function setShowDrafts( bool $showDrafts ) : IRepository
	{
		$this->_showDrafts = $showDrafts;
		return $this;
	}

	/**
	 * @param IArticle $article
	 * @return bool
	 *
	 * Test whether an article should be visible or not.
	 */
	public function isDisplayable( IArticle $article ): bool
	{
		$display = true;

		if( !$this->getShowDrafts() && $article->isDraft() )
		{
			$display = false;
		}

		if( strtotime( $article->getDatePublished() ) > time() )
		{
			$display = false;
		}

		return $display;
	}

	/**
	 * @param int $limit Maximum number of articles to return (0 = all)
	 * @param int $offset Number of articles to skip from the beginning
	 * @return array
	 */
	public function getArticles( int $limit = 0, int $offset = 0 ): array
	{
		if( $limit > 0 )
		{
			return array_slice( $this->_list, $offset, $limit );
		}

		if( $offset > 0 )
		{
			return array_slice( $this->_list, $offset );
		}

		return $this->_list;
	}

	/**
	 * Returns the total count of articles in the repository
	 *
* @return int
	 */
	public function getArticleCount(): int
	{
		return count( $this->_list );
	}

	/**
	 * Returns the number of pages based on the current page size
	 *
	 * @return int Number of pages (0 if pageSize is invalid)
	 */
	public function getPageCount(): int
	{
		if( $this->_pageSize <= 0 )
		{
			return 0;
		}

		return (int) ceil( $this->getArticleCount() / $this->_pageSize );
	}

	/**
	 * @return int
	 */
	public function getPageSize(): int
	{
		return $this->_pageSize;
	}

	/**
	 * @param int $pageSize
	 * @return IRepository
	 */
	public function setPageSize( int $pageSize ): IRepository
	{
		if( $pageSize > 0 )
		{
			$this->_pageSize = $pageSize;
		}
		return $this;
	}

	/**
	 * Returns articles for a specific page number
	 *
	 * @param int $pageNumber Page number (1-based)
	 * @return array
	 */
	public function getArticlePage( int $pageNumber ): array
	{
		if( $pageNumber < 1 || $this->_pageSize <= 0 )
		{
			return [];
		}

		$offset = ( $pageNumber - 1 ) * $this->_pageSize;
		return $this->getArticles( $this->_pageSize, $offset );
	}

	/**
	 * Loads an article from a YAML file.
	 *
	 * @param string $fileName
	 * @return IArticle
	 */
	protected function loadArticle( string $fileName ): IArticle
	{
		$file = Yaml::parseFile( $fileName );

		$article = new Article();

		$requiredFields = [
			'title',
			'slug',
			'datePublished',
			'path'
		];

		foreach( $requiredFields as $field )
		{
			if( !isset( $file[ $field ] ) )
			{
				throw new Exception\ArticleMissingData( $field );
			}
		}
		$article->setTitle( $file[ 'title' ] );
		$article->setSlug( $file[ 'slug' ] );
		$article->setDatePublished( $file[ 'datePublished' ] );
		$article->setBodyPath( $file[ 'path' ] );

		if( isset( $file[ 'category' ] ) )
		{
			$article->setCategory( $file[ 'category' ] );
		}

		if( isset( $file[ 'tags' ] ) )
		{
			$article->setTags( $file[ 'tags' ] );
		}

		if( isset( $file[ 'githubFlavored' ] ) )
		{
			$article->setGithubFlavored( $file[ 'githubFlavored' ] );
		}

		if( isset( $file[ 'description' ] ) )
		{
			$article->setDescription( $file[ 'description' ] );
		}

		if( isset( $file[ 'draft' ] ) )
		{
			$article->setDraft( $file[ 'draft' ] );
		}

		if( isset( $file[ 'canonicalUrl' ] ) )
		{
			$article->setCanonicalUrl( $file[ 'canonicalUrl' ] );
		}

		if( isset( $file[ 'author' ] ) )
		{
			$article->setAuthor( $file[ 'author' ] );
		}

		return $article;
	}

	/**
	 * @param string $slug
	 * @return IArticle
	 *
	 * @throws ArticleNotFound
	 */
	public function getArticleBySlug( string $slug ): IArticle
	{
		foreach( $this->_list as $article )
		{
			if( $article->getSlug() == $slug )
			{
					$article->loadBody( $this->_root );

				return $article;
			}
		}

		throw new ArticleNotFound();
	}

	/**
	 * @param string $tag
	 * @return array
	 */
	public function getArticlesByTag( string $tag ): array
	{
		$list = [];

		foreach( $this->_list as $article )
		{
			if( $article->hasTag( $tag ) )
			{
				$list[] = $article;
			}
		}

		return $list;
	}

	/**
	 * @param string $category
	 * @return array
	 */
	public function getArticlesByCategory( string $category ): array
	{
		$list = [];

		foreach( $this->_list as $article )
		{
			if( $article->getCategory() == $category )
			{
				$list[] = $article;
			}
		}

		return $list;
	}

	/**
	 * Gets all articles with an author field that contains any of the specified text.
	 *
	 * @param string $author
	 * @return array
	 */
	public function getArticlesByAuthor( string $author ): array
	{
		$list = [];

		foreach( $this->_list as $article )
		{
			if( strstr( $article->getAuthor(), $author ) )
			{
				$list[] = $article;
			}
		}

		return $list;
	}

	/**
	 * Returns a list of all authors in the repository.
	 *
	 * @return array
	 */
	public function getAuthors(): array
	{
		$authors = [];

		foreach( $this->_list as $article )
		{
			if( !in_array( $article->getAuthor(), $authors ) )
			{
				if( $article->getAuthor() === null || $article->getAuthor() === '' )
				{
					continue;
				}

				$authors[] = $article->getAuthor();
			}
		}

		sort( $authors );

		return $authors;
	}

	/**
	 * Returns a list of all categories in the repository.
	 *
	 * @return array
	 */
	public function getCategories(): array
	{
		$categories = [];

		foreach( $this->_list as $article )
		{
			if( !in_array( $article->getCategory(), $categories ) )
			{
				$categories[] = $article->getCategory();
			}
		}

		sort( $categories );

		return $categories;
	}

	/**
	 * Returns a list of all tags in the repository.
	 *
	 * @return array
	 */
	public function getTags(): array
	{
		$tags = [];

		foreach( $this->_list as $article )
		{
			foreach( $article->getTags() as $tag )
			{
				if( !in_array( $tag, $tags ) )
				{
					$tags[] = $tag;
				}
			}
		}

		sort( $tags );

		return $tags;
	}

	/**
	 * Generates an RSS feed for the repository.
	 *
	 * @param string $name
	 * @param string $description
	 * @param string $url
	 * @param string $feedUrl
	 * @param array $articles
	 * @return string
	 */
	public function getFeed( string $name, string $description, string $url, string $feedUrl, array $articles ): string
	{
		error_reporting(E_ALL & ~E_DEPRECATED );

		$feed = new Feed();

		$channel = new Channel();

		$channel->title( $name )
				  ->description( $description )
				  ->url( $url )
				  ->feedUrl( $feedUrl )
				  ->language( 'en-US' )
				  ->pubDate( time() )
				  ->ttl( 60 )
				  ->appendTo( $feed );

		foreach( $articles as $data )
		{
			try
			{
				$article = $this->getArticleBySlug( $data->getSlug() );
			}
			catch( ArticleNotFound | ArticleMissingBody $e )
			{
				continue;
			}

			$item = new Item();

			$link = $url . '/blahg/' . $article->getSlug();

			try
			{
				$item->title( $article->getTitle() )
					  ->description( $article->getBodyHtml() )
					  ->contentEncoded( $article->getBodyHtml() )
					  ->url( $link )
					  ->pubDate( strtotime( $article->getDatePublished() ) )
					  ->guid( $link, true )
					  ->preferCdata( true )
					  ->appendTo( $channel );
			}
			catch( CommonMarkException $e )
			{
				continue;
			}
		}

		return $feed->render();
	}

	/**
	 * @param array $files
	 * @param string $dir
	 * @return void
	 */
	protected function loadArticles(): void
	{
		$files = @scandir( $this->_root );

		if( !is_array( $files ) )
		{
			return;
		}

		foreach( $files as $file )
		{
			if( $file[ 0 ] == '.' )
			{
				continue;
			}

			if( !fnmatch( "*.yaml", $file ) )
			{
				continue;
			}

			$path = $this->_root . '/' . $file;

			try
			{
				$article = $this->loadArticle( $path );
			}
			catch( Exception\ArticleMissingData $e )
			{
				continue;
			}

			if( !$this->isDisplayable( $article ) )
			{
				continue;
			}

			$this->_list[] = $article;
		}

		usort( $this->_list, 'Blahg\ArticleCmp' );
	}
}
