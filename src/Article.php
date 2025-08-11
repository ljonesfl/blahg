<?php

namespace Blahg;

use Blahg\Exception\ArticleMissingBody;
use League\CommonMark\Exception\CommonMarkException;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use League\CommonMark\MarkdownConverter;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\Footnote\FootnoteExtension;

class Article implements IArticle
{
	private string $_slug;
	private string $_title;
	private string $_description = "";
	private string $_datePublished;
	private string $_category = "";
	private array  $_tags = [];
	private string $_bodyPath;
	private string $_body;
	private bool   $_draft = false;
	private string $_canonicalUrl = "";
	private string $_author = "";
	private bool	$_githubFlavored = false;

	public function isGithubFlavored(): bool
	{
		return $this->_githubFlavored;
	}

	public function setGithubFlavored( bool $githubFlavored ): IArticle
	{
		$this->_githubFlavored = $githubFlavored;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getAuthor(): string
	{
		return $this->_author;
	}

	/**
	 * @param string $author
	 * @return IArticle
	 */
	public function setAuthor( string $author ): IArticle
	{
		$this->_author = $author;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getCanonicalUrl(): string
	{
		return $this->_canonicalUrl;
	}

	/**
	 * @param string $canonicalUrl
	 * @return IArticle
	 */
	public function setCanonicalUrl( string $canonicalUrl ): IArticle
	{
		$this->_canonicalUrl = $canonicalUrl;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getDescription(): string
	{
		return $this->_description;
	}

	/**
	 * @param string $description
	 * @return IArticle
	 */
	public function setDescription( string $description ): IArticle
	{
		$this->_description = $description;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isDraft() : bool
	{
		return $this->_draft;
	}

	/**
	 * @param bool $draft
	 * @return IArticle
	 */
	public function setDraft( bool $draft ) : IArticle
	{
		$this->_draft = $draft;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getSlug() : string
	{
		return $this->_slug;
	}

	/**
	 * @param string $slug
	 * @return IArticle
	 */
	public function setSlug( string $slug ) : IArticle
	{
		$this->_slug = $slug;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getBodyPath(): string
	{
		return $this->_bodyPath;
	}

	/**
	 * @param mixed $bodyPath
	 * @return IArticle
	 */
	public function setBodyPath( string $bodyPath ): IArticle
	{
		$this->_bodyPath = $bodyPath;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getBody(): string
	{
		return $this->_body;
	}

	/**
	 * @return string
	 * @throws CommonMarkException
	 */
	public function getBodyHtml(): string
	{
		if( $this->isGithubFlavored() )
		{
			$converter = $this->getGithubFlavoredMarkdownConverter();
		}
		else
		{
			$converter = $this->getCommonmarkConverter();
		}

		return $converter->convert( $this->_body );
	}

	/**
	 * @param mixed $text
	 * @return IArticle
	 */
	public function setBody( string $text ): IArticle
	{
		$this->_body = $text;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getTitle(): string
	{
		return $this->_title;
	}

	/**
	 * @param mixed $title
	 * @return IArticle
	 */
	public function setTitle( string $title ): IArticle
	{
		$this->_title = $title;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getDatePublished(): string
	{
		return $this->_datePublished;
	}

	/**
	 * @param mixed $datePublished
	 * @return IArticle
	 */
	public function setDatePublished( string $datePublished ): IArticle
	{
		$this->_datePublished = $datePublished;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCategory(): string
	{
		return $this->_category;
	}

	/**
	 * @param mixed $category
	 * @return IArticle
	 */
	public function setCategory( string $category ): IArticle
	{
		$this->_category = $category;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getTags(): array
	{
		return $this->_tags;
	}

	/**
	 * @param mixed $tags
	 * @return IArticle
	 */
	public function setTags( array $tags ): IArticle
	{
		$this->_tags = $tags;
		return $this;
	}

	/**
	 * @param string $target
	 * @return bool
	 */
	public function hasTag( string $target ): bool
	{
		foreach( $this->_tags as $tag )
		{
			if( $tag == $target )
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * @param string $root
	 * @throws ArticleMissingBody
	 */
	public function loadBody( string $root ): void
	{
		$file = $root . '/' . $this->getBodyPath();

		if( !file_exists( $file ) )
		{
			throw new ArticleMissingBody();
		}

		$this->setBody( file_get_contents( $file ) );
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

		return new MarkdownConverter( $environment );
	}
}
