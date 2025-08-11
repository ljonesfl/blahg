<?php

namespace Blahg\Exception;

class ArticleMissingData extends \Exception
{
	 public function __construct( string $item, int $code = 0, ?\Throwable $previous = null )
	 {
		  parent::__construct( "Article missing data for: $item", $code, $previous );
	 }
}
