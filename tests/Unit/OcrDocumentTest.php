<?php

namespace Tests\Unit;

use App\Models\OcrDocument;
use Tests\TestCase;

class OcrDocumentTest extends TestCase
{
    public function test_excerpt_returns_dash_when_text_is_empty(): void
    {
        $document = new OcrDocument([
            'extracted_text' => null,
        ]);

        $this->assertSame('-', $document->excerpt());
    }

    public function test_excerpt_compacts_whitespace_and_limits_text(): void
    {
        $document = new OcrDocument([
            'extracted_text' => "  linha 1\n\nlinha   2\tlinha 3 ",
        ]);

        $excerpt = $document->excerpt(12);

        $this->assertStringContainsString('...', $excerpt);
        $this->assertStringStartsWith('linha 1', $excerpt);
        $this->assertLessThanOrEqual(15, mb_strlen($excerpt));
    }
}
