<?php

namespace Blahg;

use Blahg\Exception\ArticleMissingBody;
use Blahg\Exception\ArticleNotFound;
use Symfony\Component\Yaml\Yaml;

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

			$this->_List[ strtotime( $Article->getDatePublished() ) ] = $Article;
		}

		krsort( $this->_List );
	}

	/**
	 * @return array
	 */

	public function getAll()
	{
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
				$File = $this->_Root.'/'.$Article->getBodyPath();

				if( !file_exists( $File ) )
				{
					throw new ArticleMissingBody();
				}

				$Article->setBody( file_get_contents( $File ) );
				return $Article;
			}
		}

		throw new ArticleNotFound();
	}
}