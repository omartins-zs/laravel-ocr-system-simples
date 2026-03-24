<?php

return [
    'tesseract_binary' => env('OCR_TESSERACT_BINARY', ''),
    'tessdata_path' => env('OCR_TESSDATA_PATH', ''),
    'language' => env('OCR_LANGUAGE', 'por'),
    'max_pdf_pages' => env('OCR_MAX_PDF_PAGES', 5),
    'pdf_resolution' => env('OCR_PDF_RESOLUTION', 200),
];
