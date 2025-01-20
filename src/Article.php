<?php

namespace Blahg;

use Blahg\Exception\ArticleMissingBody;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Exception\CommonMarkException;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use League\CommonMark\MarkdownConverter;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\Footnote\FootnoteExtension;

class Article
{
	private string $_Slug;
	private string $_Title;
	private string $_Description = "";
	private string $_DatePublished;
	private string $_Category = "";
	private array  $_Tags;
	private string $_BodyPath;
	private string $_Body;
	private bool   $_Draft = false;
	private string $_CanonicalUrl = "";
	private string $_Author = "";
	private bool	$_GithubFlavored = false;

	public function isGithubFlavored(): bool
	{
		return $this->_GithubFlavored;
	}

	public function setGithubFlavored( bool $GithubFlavored ): Article
	{
		$this->_GithubFlavored = $GithubFlavored;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getAuthor(): string
	{
		return $this->_Author;
	}

	/**
	 * @param string $Author
	 * @return Article
	 */
	public function setAuthor( string $Author ): Article
	{
		$this->_Author = $Author;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getCanonicalUrl(): string
	{
		return $this->_CanonicalUrl;
	}

	/**
	 * @param string $CanonicalUrl
	 * @return Article
	 */
	public function setCanonicalUrl( string $CanonicalUrl ): Article
	{
		$this->_CanonicalUrl = $CanonicalUrl;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getDescription(): string
	{
		return $this->_Description;
	}

	/**
	 * @param string $Description
	 * @return Article
	 */
	public function setDescription( string $Description ): Article
	{
		$this->_Description = $Description;
		return $this;
	}

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
	public function getBodyPath(): string
	{
		return $this->_BodyPath;
	}

	/**
	 * @param mixed $BodyPath
	 * @return Article
	 */
	public function setBodyPath( string $BodyPath ): Article
	{
		$this->_BodyPath = $BodyPath;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getBody(): string
	{
		return $this->_Body;
	}

	/**
	 * @return string
	 * @throws CommonMarkException
	 */
	public function getBodyHtml(): string
	{
		if( $this->isGithubFlavored() )
		{
			$Converter = $this->getGithubFlavoredMarkdownConverter();
		}
		else
		{
			$Converter = $this->getCommonmarkConverter();
		}

		return $Converter->convert( $this->_Body );
	}

	/**
	 * @param mixed $Text
	 * @return Article
	 */
	public function setBody( string $Text ): Article
	{
		$this->_Body = $Text;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getTitle(): string
	{
		return $this->_Title;
	}

	/**
	 * @param mixed $Title
	 * @return Article
	 */
	public function setTitle( string $Title ): Article
	{
		$this->_Title = $Title;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getDatePublished(): string
	{
		return $this->_DatePublished;
	}

	/**
	 * @param mixed $DatePublished
	 * @return Article
	 */
	public function setDatePublished( string $DatePublished ): Article
	{
		$this->_DatePublished = $DatePublished;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCategory(): string
	{
		return $this->_Category;
	}

	/**
	 * @param mixed $Category
	 * @return Article
	 */
	public function setCategory( string $Category ): Article
	{
		$this->_Category = $Category;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getTags(): array
	{
		return $this->_Tags;
	}

	/**
	 * @param mixed $Tags
	 * @return Article
	 */
	public function setTags( array $Tags ): Article
	{
		$this->_Tags = $Tags;
		return $this;
	}

	/**
	 * @param string $Target
	 * @return bool
	 */
	public function hasTag( string $Target ): bool
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
	public function loadBody( string $Root ): void
	{
		$File = $Root . '/' . $this->getBodyPath();

		if( !file_exists( $File ) )
		{
			throw new ArticleMissingBody();
		}

		$this->setBody( file_get_contents( $File ) );
	}

	/**
	 * @return GithubFlavoredMarkdownConverter
	 */
	protected function getGithubFlavoredMarkdownConverter(): GithubFlavoredMarkdownConverter
	{
		return new GithubFlavoredMarkdownConverter(
			[
				'allow_unsafe_links' => false,
			]
		);
	}

	/**
	 * @return MarkdownConverter
	 */
	protected function getCommonmarkConverter(): MarkdownConverter
	{
		$config = [
			'footnote' => [
				'backref_class'      => 'footnote-backref',
				'backref_symbol'     => '↩',
				'container_add_hr'   => true,
				'container_class'    => 'footnotes',
				'ref_class'          => 'footnote-ref',
				'ref_id_prefix'      => 'fnref:',
				'footnote_class'     => 'footnote',
				'footnote_id_prefix' => 'fn:',
			],
		];

		// Configure the Environment with all the CommonMark parsers/renderers
		$environment = new Environment( $config );
		$environment->addExtension( new CommonMarkCoreExtension() );

		// Add the extension
		$environment->addExtension( new FootnoteExtension() );

		$Converter = new MarkdownConverter( $environment );
		return $Converter;
	}
}
