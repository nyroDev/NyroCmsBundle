<?php

namespace NyroDev\NyroCmsBundle\Event;

use NyroDev\NyroCmsBundle\Model\Content;
use Symfony\Contracts\EventDispatcher\Event;

class SitemapEvent extends Event
{
    public const SITEMAP_EVENT = 'nyrocms.events.sitemap';

    public function __construct(
        public readonly Content $rootContent,
        public array $urls,
        public readonly bool $forSitemapXml = false,
    ) {
    }
}
