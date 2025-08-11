<?php

namespace Blahg;

use League\CommonMark\Exception\CommonMarkException;

interface IArticle
{
	/**
	 * @return string
	 */
	public function getAuthor(): string;

	/**
	 * @param string $author
	 * @return IArticle
	 */
	public function setAuthor( string $author ): IArticle;

	/**
	 * @return string
	 */
	public function getCanonicalUrl(): string;

	/**
	 * @param string $canonicalUrl
	 * @return IArticle
	 */
	public function setCanonicalUrl( string $canonicalUrl ): IArticle;

	/**
	 * @return string
	 */
	public function getDescription(): string;

	/**
	 * @param string $description
	 * @return IArticle
	 */
	public function setDescription( string $description ): IArticle;

	/**
	 * @return bool
	 */
	public function isDraft(): bool;

	/**
	 * @param bool $draft
	 * @return IArticle
	 */
	public function setDraft( bool $draft ): IArticle;

	/**
	 * @return string
	 */
	public function getSlug(): string;

	/**
	 * @param string $slug
	 * @return IArticle
	 */
	public function setSlug( string $slug ): IArticle;

	/**
	 * @return mixed
	 */
	public function getBodyPath(): string;

	/**
	 * @param mixed $bodyPath
	 * @return IArticle
	 */
	public function setBodyPath( string $bodyPath ): IArticle;

	/**
	 * @return mixed
	 */
	public function getBody(): string;

	/**
	 * @return string
	 * @throws CommonMarkException
	 */
	public function getBodyHtml(): string;

	/**
	 * @param mixed $text
	 * @return IArticle
	 */
	public function setBody( string $text ): IArticle;

	/**
	 * @return mixed
	 */
	public function getTitle(): string;

	/**
	 * @param mixed $title
	 * @return IArticle
	 */
	public function setTitle( string $title ): IArticle;

	/**
	 * @return mixed
	 */
	public function getDatePublished(): string;

	/**
	 * @param mixed $datePublished
	 * @return IArticle
	 */
	public function setDatePublished( string $datePublished ): IArticle;

	/**
	 * @return mixed
	 */
	public function getCategory(): string;

	/**
	 * @param mixed $category
	 * @return IArticle
	 */
	public function setCategory( string $category ): IArticle;

	/**
	 * @return mixed
	 */
	public function getTags(): array;

	/**
	 * @param mixed $tags
	 * @return IArticle
	 */
	public function setTags( array $tags ): IArticle;

	/**
	 * @param string $target
	 * @return bool
	 */
	public function hasTag( string $target ): bool;
}
