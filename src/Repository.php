<?php

namespace Blahg;

use Blahg\Exception\ArticleMissingBody;
use Blahg\Exception\ArticleNotFound;
use Suin\RSSWriter\Channel;
use Suin\RSSWriter\Feed;
use Suin\RSSWriter\Item;
use Symfony\Component\Yaml\Yaml;

function ArticleCmp( $ArticleA, $ArticleB )
{
    $TimeA = strtotime( $ArticleA->getDatePublished() );
    $TimeB = strtotime( $ArticleB->getDatePublished() );

    if( $TimeA == $TimeB )
    {
        return 0;
    }
    return ( $TimeA < $TimeB ) ? -1 : 1;
}

class Repository
{
	private $_List = array();
	private $_Root;

	/**
	 * @param $Dir
	 */

	public function __construct( string $Dir )
	{
		$this->_Root = $Dir;

		$Files = scandir( $Dir );

		if( !is_array( $Files ) )
		{
			return;
		}

		foreach( $Files as $key => $File )
		{
			if( $File[ 0 ] == '.' )
			{
				continue;
			}

			if( !fnmatch( "*.yaml", $File ) )
			{
				continue;
			}

			$Path = $Dir.'/'.$File;

			$Article = $this->loadArticle( $Path );

			if( strtotime( $Article->getDatePublished() ) > time() )
			{
				continue;
			}

			$this->_List[] = $Article;
		}

		usort( $this->_List, 'Blahg\ArticleCmp' );
	}

    /**
     * @param int $Max
     * @return array
     */
	public function getAll( int $Max = 0 ) : array
	{
		if( $Max )
		{
			return array_slice( $this->_List, 0, $Max );
		}

		return $this->_List;
	}

	protected function loadArticle( string $FileName ) : Article
	{
		$File = Yaml::parseFile( $FileName );

		$Article = new Article();

		$Article->setTitle( $File[ 'title' ] );
		$Article->setSlug( $File[ 'slug' ] );
		$Article->setDatePublished( $File[ 'datePublished' ] );
		$Article->setBodyPath( $File[ 'path' ] );
		$Article->setTags( $File[ 'tags' ] );
		$Article->setCategory( $File[ 'category' ] );

		return $Article;
	}

	/**
	 * @param $Slug
	 * @return Article
	 *
	 * @throws ArticleNotFound
	 * @throws ArticleMissingBody
	 */

	public function getArticleBySlug( string $Slug ) : Article
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
	public function getAllByTag( string $Tag ) : array
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
	public function getAllByCategory( string $Category ) : array
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

	public function getFeed( string $Name, string $Description, string $Url, string $FeedUrl, array $Articles ) : string
    {
        $Feed = new Feed();

        $Channel = new Channel();

        $Channel
            ->title( $Name )
            ->description( $Description)
            ->url( $Url )
            ->feedUrl( $FeedUrl )
            ->language( 'en-US' )
            ->pubDate( time() )
            ->ttl( 60 )
            ->appendTo( $Feed );

        foreach( $Articles as $Data )
        {
            $Article = $this->getArticleBySlug( $Data->getSlug() );

            $Item = new Item();

            $Link = $Url.'/blahg/'.$Article->getSlug();
            $Item
                ->title( $Article->getTitle() )
                ->description( $Article->getBodyHtml() )
                ->contentEncoded( $Article->getBodyHtml() )
                ->url( $Link )
                ->pubDate( strtotime( $Article->getDatePublished() ) )
                ->guid( $Link, true )
                ->preferCdata( true )
                ->appendTo( $Channel );
        }

        return $Feed->render();
    }
}
