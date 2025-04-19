<?php

namespace Aurabx\DicomData;

interface TagNameResolverInterface
{
    public function resolve(string $tag): ?string;

    public function getTagIdByName(string $name): ?string;

}
