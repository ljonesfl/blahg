<?php

namespace Blahg;

use Blahg\Exception\ArticleNotFound;

interface IRepository
{
	/**
	 * @return bool
	 */
	public function getShowDrafts(): bool;

	/**
	 * @param mixed $showDrafts
	 * @return IRepository
	 */
	public function setShowDrafts( bool $showDrafts ): IRepository;

	/**
	 * @param IArticle $article
	 * @return bool
	 *
	 * Test whether an article should be visible or not.
	 */
	public function isDisplayable( IArticle $article ): bool;

	/**
	 * @param int $limit Maximum number of articles to return (0 = all)
	 * @param int $offset Number of articles to skip from the beginning
	 * @return array
	 */
	public function getArticles( int $limit = 0, int $offset = 0 ): array;

	/**
	 * Returns the total count of articles in the repository
	 *
	 * @return int
	 */
	public function getArticleCount(): int;

	/**
	 * Returns the number of pages based on the current page size
	 *
	 * @return int Number of pages (0 if pageSize is invalid)
	 */
	public function getPageCount(): int;

	/**
	 * @return int
	 */
	public function getPageSize(): int;

	/**
	 * @param int $pageSize
	 * @return IRepository
	 */
	public function setPageSize( int $pageSize ): IRepository;

	/**
	 * Returns articles for a specific page number
	 *
	 * @param int $pageNumber Page number (1-based)
	 * @return array
	 */
	public function getArticlePage( int $pageNumber ): array;

	/**
	 * @param string $slug
	 * @return IArticle
	 *
	 * @throws ArticleNotFound
	 */
	public function getArticleBySlug( string $slug ): IArticle;

	/**
	 * @param string $tag
	 * @return array
	 */
	public function getArticlesByTag( string $tag ): array;

	/**
	 * @param string $category
	 * @return array
	 */
	public function getArticlesByCategory( string $category ): array;

	/**
	 * Gets all articles with an author field that contains any of the specified text.
	 *
	 * @param string $author
	 * @return array
	 */
	public function getArticlesByAuthor( string $author ): array;

	/**
	 * Returns a list of all authors in the repository.
	 *
	 * @return array
	 */
	public function getAuthors(): array;

	/**
	 * Returns a list of all categories in the repository.
	 *
	 * @return array
	 */
	public function getCategories(): array;

	/**
	 * Returns a list of all tags in the repository.
	 *
	 * @return array
	 */
	public function getTags(): array;

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
	public function getFeed( string $name, string $description, string $url, string $feedUrl, array $articles ): string;
}
