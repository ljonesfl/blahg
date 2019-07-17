# Blahg

Created so that I can write blog articles in vi using md.
Each article consists of 2 files, a descriptor written in yaml and
the actual article written in markdown.

## Descriptor

Example: 10-reasons-broccoli.yaml

title: 10 Reasons Why I Love Broccoli<br>
slug: 10-reasons-why-i-love-broccoli<br>
datePublished: "2018-12-27"<br>
category: Food<br>
tags:<br>
- broccoli<br>
- food<br>
path: 10-reasons-broccoli.md<br>

## Usage

### Setup
```
$Blog = new Blagh/Repository( '/blog' );
$Articles = $Blog->getList();
```        
### Render List
```
<h1>Blahgs</h1>
<?php
foreach( $Articles as $Article)
{
    ?>
    <h3><a href="/blahg/<?= $Article->getSlug() ?>"><?= $Article->getTitle() ?></a></h3>
    Category: <?= $Article->getCategory() ?><br>
    Tags:
    <?php
    $Tags = $Article->getTags();
    foreach( $Tags as $Tag )
    {
        ?>
        <small>#<?= $Tag ?></small>
    <?php
    }
    ?><br>
    <small>Date published: <?= $Article->getDatePublished() ?></small><br>
    <?php
}
?>
```

### Render Article
```
try
{
    $Article = $Blahg->getArticle( '10-reasons-why-i-love-broccoli' );
}
catch( ArticleNotFound $Exception )
{}
catch( ArticleMissingBody $Exception )
{}
```
### Roadmap

Repository::getAllByDateRange( string $Start, string $End ) : array

Repository::getAllByPage( int $PageNum, int $PageCount ) : array

