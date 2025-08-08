# Blahg

A lightweight PHP library for creating blog applications with Markdown content and YAML metadata.

## Features

- **Markdown-based content** - Write articles in Markdown with GitHub-flavored markdown and footnote support
- **YAML metadata** - Organize articles with YAML descriptors containing title, tags, categories, and more
- **Draft management** - Control article visibility with draft status
- **RSS feed generation** - Automatic RSS feed creation for your blog
- **Flexible querying** - Filter articles by tag, category, author, or slug
- **Future publishing** - Schedule articles with future publication dates

## Requirements

- PHP 8.4 or higher
- Composer

## Installation

Install via [Composer](http://getcomposer.org):

```bash
composer require ljonesfl/blahg
```

## Usage

### Basic Setup

```php
use Blahg\Repository;

// Initialize repository with your content directory
$repository = new Repository('/path/to/articles');

// Get all published articles
$articles = $repository->getArticles();
```

### Article Structure

Each article consists of two files:

1. **YAML descriptor** (e.g., `my-article.yaml`)
2. **Markdown content** (e.g., `my-article.md`)

#### YAML Descriptor Example

```yaml
title: "10 Reasons Why I Love Broccoli"
slug: "10-reasons-why-i-love-broccoli"
datePublished: "2018-12-27"
category: "Food"
author: "John Doe"
description: "A deep dive into the wonderful world of broccoli"
tags:
  - broccoli
  - vegetables
  - nutrition
path: "10-reasons-broccoli.md"
draft: false
githubFlavored: true
canonicalUrl: "https://example.com/blog/10-reasons-why-i-love-broccoli"
```

#### Required Fields
- `title` - Article title
- `slug` - URL-friendly identifier
- `datePublished` - Publication date (YYYY-MM-DD format)
- `path` - Path to the Markdown content file

#### Optional Fields
- `category` - Article category
- `tags` - Array of tags
- `author` - Article author
- `description` - Short description for meta tags
- `draft` - Set to `true` to hide from public view
- `githubFlavored` - Enable GitHub-flavored Markdown
- `canonicalUrl` - Canonical URL for SEO

### Retrieving Articles

```php
// Get all published articles (excludes drafts and future posts)
$articles = $repository->getArticles();

// Get a specific article by slug
try 
{
    $article = $repository->getArticle('10-reasons-why-i-love-broccoli');
} 
catch( ArticleNotFound $e ) 
{
    // Handle missing article
} 
catch( ArticleMissingBody $e ) 
{
    // Handle missing content file
}

// Include drafts in results
$repository->setShowDrafts( true );
$allArticles = $repository->getArticles();
```

### Filtering Articles

```php
// Get articles by category
$foodArticles = $repository->getArticlesByCategory( 'Food' );

// Get articles by tag
$broccoliArticles = $repository->getArticlesByTag( 'broccoli' );

// Get articles by author
$johnsArticles = $repository->getArticlesByAuthor( 'John Doe' );
```

### Getting Metadata

```php
// Get all unique categories
$categories = $repository->getCategories();

// Get all unique tags
$tags = $repository->getTags();

// Get all unique authors
$authors = $repository->getAuthors();
```

### Rendering Articles

#### Article List Example

```php
<h1>Blog Articles</h1>
<?php foreach( $articles as $article ): ?>
    <article>
        <h2>
            <a href="/blog/<?= htmlspecialchars($article->getSlug()) ?>">
                <?= htmlspecialchars($article->getTitle()) ?>
            </a>
        </h2>
        
        <?php if ($article->getDescription()): ?>
            <p><?= htmlspecialchars($article->getDescription()) ?></p>
        <?php endif; ?>
        
        <div class="meta">
            <?php if ($article->getCategory()): ?>
                <span>Category: <?= htmlspecialchars($article->getCategory()) ?></span>
            <?php endif; ?>
            
            <?php if ($article->getAuthor()): ?>
                <span>By: <?= htmlspecialchars($article->getAuthor()) ?></span>
            <?php endif; ?>
            
            <time><?= $article->getDatePublished() ?></time>
        </div>
        
        <?php if( $article->getTags() ): ?>
            <div class="tags">
                <?php foreach( $article->getTags() as $tag ): ?>
                    <span class="tag">#<?= htmlspecialchars( $tag ) ?></span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </article>
<?php endforeach; ?>
```

#### Single Article Example

```php
<?php
try {
    $article = $repository->getArticleBySlug( $slug );
    ?>
    <article>
        <h1><?= htmlspecialchars( $article->getTitle() ) ?></h1>
        
        <div class="meta">
            <?php if ($article->getAuthor()): ?>
                <span>By <?= htmlspecialchars( $article->getAuthor() ) ?></span>
            <?php endif; ?>
            <time><?= $article->getDatePublished() ?></time>
        </div>
        
        <div class="content">
            <?= $article->getBody() ?>
        </div>
        
        <?php if( $article->getTags()) : ?>
            <div class="tags">
                <?php foreach( $article->getTags() as $tag ): ?>
                    <a href="/blog/tag/<?= urlencode( $tag ) ?>">
                        #<?= htmlspecialchars( $tag ) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </article>
    <?php
} catch( ArticleNotFound $e ) {
    echo "<p>Article not found.</p>";
} catch( ArticleMissingBody $e ) {
    echo "<p>Article content is missing.</p>";
}
?>
```

### RSS Feed Generation

```php
// Generate RSS feed
$rssFeed = $repository->getRss(
    title: 'My Blog',
    link: 'https://example.com/blog',
    description: 'A blog about various topics'
);

// Output RSS feed
header('Content-Type: application/rss+xml; charset=utf-8');
echo $rssFeed;
```

## Exception Handling

The library throws specific exceptions for different error conditions:

- `ArticleNotFound` - Thrown when requesting a non-existent article
- `ArticleMissingBody` - Thrown when the Markdown file referenced in the YAML descriptor is missing
- `ArticleMissingData` - Thrown when required YAML fields are missing

## Testing

Run the test suite:

```bash
composer test
```

Run tests with coverage:

```bash
composer test-coverage
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.
