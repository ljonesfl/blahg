<?php

namespace Blahg;

use Blahg\Exception\ArticleMissingBody;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use Michelf\Markdown;

class Article
{
	private $_Slug;
	private $_Title;
	private $_DatePublished;
	private $_Category;
	private $_Tags;
	private $_BodyPath;
	private $_Body;
	private $_Draft = false;

    /**
     * @return mixed
     */
    public function getDraft()
    {
        return $this->_Draft;
    }

    /**
     * @param mixed $Draft
     * @return Article
     */
    public function setDraft( $Draft )
    {
        $this->_Draft = $Draft;
        return $this;
    }

	/**
	 * @return mixed
	 */
	public function getSlug()
	{
		return $this->_Slug;
	}

	/**
	 * @param mixed $Slug
	 * @return Article
	 */
	public function setSlug( $Slug )
	{
		$this->_Slug = $Slug;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getBodyPath() : string
	{
		return $this->_BodyPath;
	}

	/**
	 * @param mixed $BodyPath
	 * @return Article
	 */
	public function setBodyPath( string $BodyPath ) : Article
	{
		$this->_BodyPath = $BodyPath;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getBody() : string
	{
		return $this->_Body;
	}

    /**
     * @return string
     */
	public function getBodyHtml() : string
    {
		 $Converter = new GithubFlavoredMarkdownConverter(
		 	[
		 		'html_input' => 'strip',
				'allow_unsafe_links' => false,
			]
		 );

		 return $Converter->convertToHtml( $this->_Body );
    }

	/**
	 * @param mixed $Text
	 * @return Article
	 */
	public function setBody( string $Text ) : Article
	{
		$this->_Body = $Text;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getTitle() : string
	{
		return $this->_Title;
	}

	/**
	 * @param mixed $Title
	 * @return Article
	 */
	public function setTitle( string $Title ) : Article
	{
		$this->_Title = $Title;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getDatePublished() : string
	{
		return $this->_DatePublished;
	}

	/**
	 * @param mixed $DatePublished
	 * @return Article
	 */
	public function setDatePublished( string $DatePublished ) : Article
	{
		$this->_DatePublished = $DatePublished;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCategory() : string
	{
		return $this->_Category;
	}

	/**
	 * @param mixed $Category
	 * @return Article
	 */
	public function setCategory( string $Category ) : Article
	{
		$this->_Category = $Category;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getTags() : array
	{
		return $this->_Tags;
	}

	/**
	 * @param mixed $Tags
	 * @return Article
	 */
	public function setTags( array $Tags ) : Article
	{
		$this->_Tags = $Tags;
		return $this;
	}

	/**
	 * @param string $Target
	 * @return bool
	 */
	public function hasTag( string $Target ) : bool
	{
		foreach( $this->_Tags as $Tag )
		{
			if( $Tag == $Target )
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * @param string $Root
	 * @throws ArticleMissingBody
	 */
	public function loadBody( string $Root ) : void
	{
		$File = $Root . '/' . $this->getBodyPath();

		if( !file_exists( $File ) )
		{
			throw new ArticleMissingBody();
		}

		$this->setBody( file_get_contents( $File ) );
	}
}
